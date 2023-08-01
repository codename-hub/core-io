<?php

namespace codename\core\io\target\buffered\file;

use codename\core\exception;
use codename\core\io\target\buffered\file;
use codename\core\io\value\text\fileabsolute\tagged;
use codename\core\value\text\fileabsolute;
use ReflectionException;

/**
 * csv file as a target
 */
class csv extends file
{
    /**
     * [UTF8_BOM description]
     * see https://stackoverflow.com/questions/5601904/encoding-a-string-as-utf-8-with-bom-in-php
     * @var string
     */
    public const UTF8_BOM = "\xEF\xBB\xBF";
    /**
     * This target supports Partial Writeouts (buffer flushing)
     * @var bool
     */
    public const SupportsPartialWriteout = true;
    /**
     * the delimiter used in the csv
     * @var string
     */
    protected string $delimiter = ';';
    /**
     * the enclosure char used in the csv
     * @var null|string
     */
    protected ?string $enclosure = '"';
    /**
     * the escape char used in the CSV
     * @var string [type]
     */
    protected string $escapeChar = '\\';
    /**
     * headed data: use mapping keys as headings
     * @var bool
     */
    protected bool $headed = true;
    /**
     * use serial columns/numeric indexes
     * instead of key => value based mappings
     * this needs "column" defined per mapping entry
     * @var bool
     */
    protected bool $numericIndexes = false;
    /**
     * [protected description]
     * @var int
     */
    protected int $numericIndexStart = 0;
    /**
     * count after which we begin a new file
     * @var null|int
     */
    protected ?int $splitCount = null;
    /**
     * [protected description]
     * @var string [type]
     */
    protected string $lineBreak = "\n";
    /**
     * output encoding
     * @var string
     */
    protected string $encoding = 'UTF-8';
    /**
     * Use BOM, if using encoding == UTF-8
     * @var bool
     */
    protected bool $encodingUtf8BOM = true; // chr(239) . chr(187) . chr(191);
    /**
     * Array of currently used file handles
     * @var array
     */
    protected array $filesCreated = [];

    /**
     * Handle of currently opened file, if any.
     * @var resource
     */
    protected $currentFileHandle;

    /**
     * {@inheritDoc}
     */
    public function __construct(string $name, array $config)
    {
        parent::__construct($name, $config);

        $this->delimiter = $config['delimiter'] ?? $this->delimiter;
        $this->enclosure = array_key_exists('enclosure', $config) ? $config['enclosure'] : $this->enclosure;
        $this->escapeChar = $config['escape_char'] ?? $this->escapeChar;
        $this->headed = $config['headed'] ?? $this->headed;
        $this->numericIndexes = $config['numeric_indexes'] ?? false;
        $this->numericIndexStart = $config['numeric_index_start'] ?? 0;
        $this->encoding = $config['encoding'] ?? 'UTF-8';
        $this->encodingUtf8BOM = $config['encoding_utf8bom'] ?? true;
        $this->splitCount = $config['split_count'] ?? null;
        $this->lineBreak = $config['lineBreak'] ?? $this->lineBreak;
    }

    /**
     * {@inheritDoc}
     * @throws ReflectionException
     * @throws exception
     */
    protected function storeBufferedData(): void
    {
        $dataChunks = [];

        $dataChunkIndexOffset = 0;

        if (static::SupportsPartialWriteout && $this->bufferSize) {
            // By default, we target only one file (index 0)
            if ($this->splitCount) {
                // if splitting, determine current starting data chunk index
                // WARNING: (int) cast required, as floor outputs a float!
                $dataChunkIndexOffset = (int)floor(($this->currentStoredCount - count($this->bufferArray)) / $this->splitCount);#
            }

            // current count of elements in being-worked-on chunk
            // (if any; zero if not buffering)
            if ($this->splitCount) {
                $dataChunkCountOffset = ($this->currentStoredCount - count($this->bufferArray)) % $this->splitCount;
            } else {
                $dataChunkCountOffset = 0;
            }

            if ($dataChunkCountOffset > 0) {
                $partialChunkLeftoverSize = 0;
                // leftover space in current chunk
                if ($this->splitCount) {
                    $partialChunkLeftoverSize = $this->splitCount - $dataChunkCountOffset;
                }

                // first chunk, partial.
                if ($partialChunkLeftoverSize > 0) {
                    $dataChunks[] = array_slice($this->bufferArray, 0, $partialChunkLeftoverSize);
                }

                // more chunks, if applicable
                $moreChunkableData = array_slice($this->bufferArray, $partialChunkLeftoverSize);
                if (!empty($moreChunkableData)) {
                    $dataChunks = array_merge(
                        $dataChunks,
                        array_chunk($moreChunkableData, $this->splitCount)
                    );
                }
            } elseif ($this->splitCount && (count($this->bufferArray) > $this->splitCount)) {
                // we have to split at least one time
                $dataChunks = array_chunk($this->bufferArray, $this->splitCount);
            } else {
                $dataChunks = [$this->bufferArray];
            }
        } elseif ($this->splitCount && (count($this->bufferArray) > $this->splitCount)) {
            // we have to split at least one time
            $dataChunks = array_chunk($this->bufferArray, $this->splitCount);
        } else {
            $dataChunks = [$this->bufferArray];
        }

        foreach ($dataChunks as $index => $dataChunk) {
            // skip empty chunks
            if (count($dataChunk) === 0) {
                continue;
            }

            $append = false;

            if (count($this->filesCreated) === ($index + $dataChunkIndexOffset + 1)) {
                //
                // use currently open file handle
                // target chunk is current chunk
                //
                if ($this->currentFileHandle) {
                    // continue to use this file handle
                    $append = true;
                } else {
                    throw new exception('MAJOR_FAULT_TARGET_FILEHANDLES_INVALID_OFFSET', exception::$ERRORLEVEL_FATAL);
                }
            } else {
                // Close current file handle
                if ($this->currentFileHandle) {
                    // further checks...? Out-of-bounds?
                    fclose($this->currentFileHandle);
                    $this->currentFileHandle = null;
                }

                // create a new file handle.
                $path = $this->getNewFilePath();
                $this->currentFileHandle = $this->getNewFileHandle($path);
                $this->filesCreated[] = $path;
            }

            $this->internalStoreBufferedData($this->currentFileHandle, $dataChunk, $append);
        }


        //
        // Finalize, tag and create result file path array
        //
        if ($this->finished) {
            // Close the remaining open file handle.
            if ($this->currentFileHandle) {
                fclose($this->currentFileHandle);
                $this->currentFileHandle = null;
            }

            // aggregate tags chunks, if applicable
            if ($this->splitCount && ($this->currentStoredCount > $this->splitCount)) {
                $tagsChunks = array_chunk($this->tagsArray, $this->splitCount);
            } else {
                $tagsChunks = [$this->tagsArray];
            }

            $resultObjects = [];
            foreach ($this->filesCreated as $index => $path) {
                $tagsChunk = $tagsChunks[$index] ?? null;

                if ($tagsChunk) {
                    foreach ($tagsChunk as &$tagsElement) {
                        // force csv extension in tag
                        $tagsElement['file_extension'] = 'csv';

                        if (count($tagsChunks) > 1) {
                            // override filename with chunk number
                            if ($addendum = $tagsElement['file_name_add'] ?? ('_' . ($index + 1))) {
                                // CHANGED 2021-04-30: we now fall back to an empty string as base filename
                                // if nothing provided.
                                $tagsElement['file_name'] = ($tagsElement['file_name'] ?? '') . $addendum;
                            }
                        }
                    }
                    $resultObjects[] = new tagged($path, $tagsChunk);
                } else {
                    $resultObjects[] = new fileabsolute($path);
                }
            }

            $this->fileResults = $resultObjects;
        }
    }

    /**
     * @param resource $handle
     * @param array $bufferArray
     * @param bool $append
     * @return void
     * @throws exception
     */
    protected function internalStoreBufferedData($handle, array $bufferArray, bool $append = false): void
    {
        if ($this->numericIndexes) {
            $keys = [];
            $indexCheck = $this->numericIndexStart;

            $sortedMapping = $this->getMapping();
            ksort($sortedMapping);

            //
            // Check index integrity first
            // Indexes have to be serial
            // 0, 1, 2, 3, ...
            //
            foreach ($sortedMapping as $index => $map) {
                if ($index == $indexCheck) {
                    $indexCheck++;
                } else {
                    throw new exception('EXCEPTION_CORE_IO_TARGET_NUMERIC_INDEX_INVALID', exception::$ERRORLEVEL_ERROR, [
                      'index' => $indexCheck,
                    ]);
                }
            }

            //
            // Get the real mappings
            //
            foreach ($this->getMapping() as $map) {
                $keys[] = $map['column'];
            }
        } else {
            $keys = array_keys($this->getMapping());
        }

        //
        // If appending, do not write BOM or headers
        // otherwise, we writeout this stuff, if configured to do so.
        //
        if (!$append) {
            //
            // We're relying on the fact
            // we're using UTF-8 for almost everything
            // in our system.
            //
            if ($this->encoding === 'UTF-8') {
                if ($this->encodingUtf8BOM) {
                    //
                    // write a UTF-8 BOM
                    //
                    fwrite($handle, self::UTF8_BOM);
                }
            } elseif ($this->encoding) {
                if (!in_array($this->encoding, ['UTF-8', 'ISO-8859-1', 'ASCII', 'Windows-1252'])) {
                    // change encoding?
                    throw new exception('EXCEPTION_TARGET_BUFFERED_FILE_CSV_UNSUPPORTED_REENCODE', exception::$ERRORLEVEL_ERROR, $this->encoding);
                }
            }

            $headings = $keys;
            if ($this->encoding) {
                foreach ($headings as &$str) {
                    $str = mb_convert_encoding($str, $this->encoding, 'UTF-8');
                }
            }
            if ($this->enclosure === null) {
                fputs($handle, implode($this->delimiter, $headings) . $this->lineBreak);
            } else {
                fputcsv($handle, $headings, $this->delimiter, $this->enclosure, $this->escapeChar);
            }
        }


        if ($this->numericIndexes) {
            foreach ($bufferArray as $buffered) {
                $dataset = array_values($buffered);
                if ($this->encoding) {
                    foreach ($dataset as &$str) {
                        $str = mb_convert_encoding($str, $this->encoding, 'UTF-8');
                    }
                }
                if ($this->enclosure === null) {
                    fputs($handle, implode($this->delimiter, $dataset) . $this->lineBreak);
                } else {
                    fputcsv($handle, $dataset, $this->delimiter, $this->enclosure, $this->escapeChar);
                }
            }
        } else {
            foreach ($bufferArray as $buffered) {
                // linearize assoc array for CSV usage
                $dataset = [];
                foreach ($keys as $k) {
                    $dataset[] = $buffered[$k] ?? null;
                }

                if ($this->encoding) {
                    foreach ($dataset as &$str) {
                        $str = mb_convert_encoding($str, $this->encoding, 'UTF-8');
                    }
                }

                if ($this->enclosure === null) {
                    fputs($handle, implode($this->delimiter, $dataset) . $this->lineBreak);
                } else {
                    fputcsv($handle, $dataset, $this->delimiter, $this->enclosure, $this->escapeChar);
                }
            }
        }

        //
        // WARNING: do not close file handle here.
        // CHANGED 2021-08-18: moved to storeBufferedData, for appending data.
        // fclose($handle);
    }
}
