<?php

namespace codename\core\io\target\buffered\file;

use codename\core\exception;
use codename\core\io\target\buffered\file;
use codename\core\value\text\fileabsolute;
use codename\parquet\CompressionMethod;
use codename\parquet\data\DataColumn;
use codename\parquet\data\DataField;
use codename\parquet\data\DateTimeDataField;
use codename\parquet\data\DecimalDataField;
use codename\parquet\data\Schema;
use codename\parquet\exception\ArgumentNullException;
use codename\parquet\exception\NotSupportedException;
use codename\parquet\ParquetException;
use codename\parquet\ParquetWriter;
use DateTimeImmutable;
use ReflectionException;

use function count;

class parquet extends file
{
    /**
     * [protected description]
     * @var null|int
     */
    protected ?int $compression = null;
    /**
     * [protected description]
     * @var bool
     */
    protected bool $bufferEnabled = true;
    /**
     * [protected description]
     * @var int
     */
    protected $bufferSize = null;
    /**
     * [protected description]
     * @var null|string
     */
    protected ?string $currentFilePath = null;
    /**
     * [protected description]
     * @var resource
     */
    protected $currentFileHandle = null;
    /**
     * current ParquetWriter instance
     * @var null|ParquetWriter
     */
    protected ?ParquetWriter $parquetWriter = null;

    /**
     * {@inheritDoc}
     * @param string $name
     * @param array $config
     * @throws \Exception
     */
    public function __construct(string $name, array $config)
    {
        parent::__construct($name, $config);

        $this->bufferEnabled = $config['buffer'] ?? false;
        $this->bufferSize = $config['buffer_size'] ?? null;

        if ($c = $config['compression'] ?? null) {
            $this->compression = match ($c) {
                'none' => CompressionMethod::None,
                'gzip' => CompressionMethod::Gzip,
                'snappy' => CompressionMethod::Snappy,
                default => throw new \Exception('Invalid compression method'),
            };
        }
    }

    /**
     * {@inheritDoc}
     * @param array $data
     * @param array|null $tags
     * @return bool
     * @throws ArgumentNullException
     * @throws NotSupportedException
     * @throws ParquetException
     * @throws exception
     */
    public function store(array $data, ?array $tags = null): bool
    {
        $success = parent::store($data, $tags);

        if ($this->bufferEnabled && $this->bufferSize && count($this->bufferArray) >= $this->bufferSize) {
            // immediate write-out and buffer clear
            $this->writeData($this->bufferArray);
            $this->bufferArray = [];
        }

        return $success;
    }

    /**
     * write a row group/data chunk
     * @param array $data [description]
     * @return void [type]       [description]
     * @throws ArgumentNullException
     * @throws NotSupportedException
     * @throws ParquetException
     * @throws exception
     */
    protected function writeData(array $data): void
    {
        $writer = $this->getParquetWriter();
        $rgr = $writer->CreateRowGroup();
        // column-based
        foreach ($writer->schema->getDataFields() as $field) {
            $column = new DataColumn($field, array_column($data, $field->name));
            $rgr->WriteColumn($column);
        }
        $rgr->finish();
    }

    /**
     * [getParquetWriter description]
     * @return ParquetWriter [description]
     * @throws ParquetException
     * @throws ArgumentNullException
     * @throws NotSupportedException
     * @throws exception
     */
    protected function getParquetWriter(): ParquetWriter
    {
        if ($this->parquetWriter === null) {
            // create a new writer instance
            $handle = $this->getCurrentFileHandle();


            $mapping = $this->getMapping();

            $fields = [];

            foreach ($mapping as $key => $config) {
                $dataField = null;
                $isNullable = $config['is_nullable'] ?? false;

                if ($config['php_class'] ?? false) {
                    if ($config['php_class'] === DateTimeImmutable::class) {
                        $dataField = DateTimeDataField::create($key, $config['datetime_format'], $config['is_nullable']);
                    } else {
                        $dataField = DataField::createFromType($key, $config['php_class']);
                    }
                } elseif ($config['php_type'] ?? false) {
                    if ($config['php_type'] === 'decimal') {
                        $dataField = DecimalDataField::create($key, $config['precision'], $config['scale'], false, $config['is_nullable']);
                    } else {
                        $dataField = DataField::createFromType($key, $config['php_type']);
                    }
                } else {
                    // guess by first X values, as long as we have some?
                    $values = array_column($this->bufferArray, $key);
                    $typesFound = [];
                    $classesFound = [];

                    foreach ($values as $index => $value) {
                        // only break out > 100 items, if we have already found ANYTHING
                        if (count($typesFound) > 0) {
                            if ($index > 100) {
                                break;
                            }
                        }

                        if ($value === null) {
                            $isNullable = true;
                            continue;
                        }

                        $type = gettype($value);
                        $typesFound[] = $type;
                        if ($type === 'object') {
                            $classesFound[] = get_class($value);
                        }
                    }

                    $uniqueTypes = array_values(array_unique($typesFound));

                    if (count($uniqueTypes) > 1) {
                        // invalid type spec by guessing
                        throw new exception('NOT UNIQUE TYPES', exception::$ERRORLEVEL_ERROR, $uniqueTypes);
                    } elseif ($uniqueTypes[0] === 'object') {
                        // we might have to guess class?

                        $uniqueClasses = array_values(array_unique($classesFound));
                        if ($uniqueClasses[0] === DateTimeImmutable::class) {
                            $dataField = DataField::createFromType($key, DateTimeImmutable::class);
                        } else {
                            // TODO: check count/other entries
                            throw new exception('UNSUPPORTED TYPE/CLASS', exception::$ERRORLEVEL_ERROR);
                        }
                    } else {
                        // print_r($uniqueTypes);

                        $dataField = DataField::createFromType($key, $uniqueTypes[0]);
                    }
                }
                $dataField->hasNulls = $isNullable;
                $fields[] = $dataField;
            }

            // create schema
            $schema = new Schema($fields);
            // TODO: formatOptions + append mode?
            $this->parquetWriter = new ParquetWriter($schema, $handle);

            // Apply compression method, if defined (!== null, NOTE: there might be (int)0 as value)
            if ($this->compression !== null) {
                $this->parquetWriter->compressionMethod = $this->compression;
            }
        }
        return $this->parquetWriter;
    }

    /**
     * Gets the current (temporary?) file HANDLE to work on
     * or creates a new one
     * @return resource
     * @throws exception
     */
    protected function getCurrentFileHandle()
    {
        if ($this->currentFileHandle === null) {
            $path = $this->getCurrentFilePath();
            $this->currentFileHandle = $this->getNewFileHandle($path);
        }
        return $this->currentFileHandle;
    }

    /**
     * Gets the current (temporary?) file path to work on
     * or creates/reserves a new one
     * @return string [description]
     */
    protected function getCurrentFilePath(): string
    {
        if ($this->currentFilePath === null) {
            $this->currentFilePath = $this->getNewFilePath();
        }
        return $this->currentFilePath;
    }

    /**
     * {@inheritDoc}
     * @throws ArgumentNullException
     * @throws NotSupportedException
     * @throws ParquetException
     * @throws ReflectionException
     * @throws exception
     */
    protected function storeBufferedData(): void
    {
        // No splitting allowed for this format. At the moment.

        // $dataChunks = [];
        // $tagsChunks = [];
        //
        // if($this->splitCount && (count($this->bufferArray) > $this->splitCount)) {
        //   // we have to split at least one time
        //   $dataChunks = array_chunk($this->bufferArray, $this->splitCount);
        //   $tagsChunks = array_chunk($this->tagsArray, $this->splitCount);
        //
        // } else {
        //   $dataChunks = [ $this->bufferArray ];
        //   $tagsChunks = [ $this->tagsArray ];
        // }

        $resultObjects = [];

        $path = $this->getCurrentFilePath();
        $handle = $this->getCurrentFileHandle();

        // check for pending writes
        if (count($this->bufferArray) > 0) {
            $this->writeData($this->bufferArray);
        }

        // finish main writer
        $writer = $this->getParquetWriter();
        $writer->finish();

        fclose($handle);

        $resultObjects[] = new fileabsolute($path);

        $this->fileResults = $resultObjects;
    }
}
