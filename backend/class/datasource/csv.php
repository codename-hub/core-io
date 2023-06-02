<?php

namespace codename\core\io\datasource;

use codename\core\exception;
use codename\core\io\datasource;

/**
 * Datasource encapsulating CSV files
 */
class csv extends datasource
{
    /**
     * [UTF8_BOM description]
     * see https://stackoverflow.com/questions/5601904/encoding-a-string-as-utf-8-with-bom-in-php
     * @var string
     */
    public const UTF8_BOM = "\xEF\xBB\xBF";
    /**
     * [protected description]
     * @var string [type]
     */
    public string $delimiter = ';';
    /**
     * [public description]
     * @var null|array
     */
    public ?array $encoding = null;
    /**
     * detect and skip empty rows
     * @var bool
     */
    protected bool $skipEmptyRows = false;
    /**
     * @var array|null
     */
    protected ?array $headings = null;

    /**
     * [protected description]
     * @var bool
     */
    protected bool $autodetectUtf8Bom = false;
    /**
     * is true if first line of csv contains heading
     * @var bool
     */
    protected bool $headed = true; // chr(239) . chr(187) . chr(191);
    /**
     * the actual position
     * @var int
     */
    protected int $index;
    /**
     * [protected description]
     * @var mixed
     */
    protected $handle;
    /**
     * current array item itself
     * @var bool|array
     */
    protected bool|array $current;
    /**
     * [protected description]
     * @var int
     */
    protected int $fstatSize = 0;
    /**
     * @var int
     */
    protected int $skipRows;

    /**
     * [__construct description]
     * @param string $filepath path to file
     * @param array $config [description]
     * @throws exception
     */
    public function __construct(string $filepath, array $config = [])
    {
        $this->setConfig($config);

        if (($this->handle = @fopen($filepath, "r")) !== false) {
            $this->fstatSize = fstat($this->handle)['size'];

            if ($this->autodetectUtf8Bom) {
                $this->handleUtf8Bom();
            }
        } else {
            error_clear_last();
            throw new exception('FILE_COULD_NOT_BE_OPEN', exception::$ERRORLEVEL_ERROR, [$filepath]);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function setConfig(array $config): void
    {
        $this->delimiter = $config['delimiter'] ?? ',';
        $this->headed = $config['headed'] ?? true;
        $this->encoding = $config['encoding'] ?? null;
        $this->skipRows = $config['skip_rows'] ?? 0;
        $this->skipEmptyRows = $config['skip_empty_rows'] ?? false;
        $this->autodetectUtf8Bom = $config['autodetect_utf8_bom'] ?? false;
    }

    /**
     * this function detects UTF8-BOM, if we're at the beginning of the file
     * and causes the datasource to skip these bytes
     * @return void [type] [description]
     */
    protected function handleUtf8Bom(): void
    {
        if (ftell($this->handle) !== 0) {
            return; // skip, as we are not on pos 0 (beginning of file)
        }

        if (($header = fread($this->handle, 3)) !== false) {
            if (strcmp($header, self::UTF8_BOM) === 0) {
                // enable BOM handling
                // TODO: kill existing encoding transformations
                //
            } else {
                // rewind to start, no UTF8-BOM
                fseek($this->handle, 0);
            }
        } else {
            // nothing? error?
        }
    }

    /**
     * returns the headings retrieved for the current file
     * @return string[] [description]
     */
    public function getHeadings(): array
    {
        return $this->headings;
    }

    /**
     * {@inheritDoc}
     */
    public function rewind(): void
    {
        fseek($this->handle, 0);
        if ($this->autodetectUtf8Bom) {
            $this->handleUtf8Bom();
        }
        $this->index = 0;
        $this->next();
    }

    /**
     * {@inheritDoc}
     */
    public function next(): void
    {
        $current = fgetcsv($this->handle, 0, $this->delimiter);

        if ($this->skipRows > 0 && $this->key() === 0) {
            for ($i = 0; $i < $this->skipRows; $i++) {
                $current = fgetcsv($this->handle, 0, $this->delimiter);
            }
        }

        // we have to stop next from running
        // if fgetcsv returns false.
        if ($current === false) {
            $this->current = false;
            return;
        }

        if (!is_null($this->headings)) {
            if ($this->skipEmptyRows) {
                // check for "all-empty"-cells
                // and loop forward until we reach another vital entry or the real end
                while (count(array_filter($current)) === 0) {
                    $current = fgetcsv($this->handle, 0, $this->delimiter);
                    if ($current === false) {
                        $this->current = false;
                        return;
                    }
                }
            }

            $i = 0;
            $this->current = [];
            foreach ($this->headings as $head) {
                if ($this->encoding) {
                    $this->current[$head] = mb_convert_encoding($current[$i], $this->encoding['to'], $this->encoding['from']);
                } else {
                    $this->current[$head] = $current[$i];
                }
                $i++;
            }
        } elseif ($this->encoding) {
            $this->current = array_map(function ($item) {
                return mb_convert_encoding($item, $this->encoding['to'], $this->encoding['from']);
            }, $current);
        } else {
            $this->current = $current;
        }

        // echo "current: ".chr(10);
        // print_r($this->current);
        //$this->current = fgetcsv($this->handle, 0, ",");

        if ($this->valid()) {
            if ($this->key() == 0 && $this->headed) {
                //read in heading
                $this->headings = $this->current();

                //
                // NOTE: decoding is performed implicitly, above.
                // No need for double-decoding, this corrupts data.
                //
                // if($this->encoding) {
                //   $this->headings = array_map(function($item) {
                //     return mb_convert_encoding($item, $this->encoding['to'], $this->encoding['from']);
                //   }, $this->headings);
                // }

                $this->index++;
                $this->next();
                return;
            }
            $this->index++;
        }
    }

    /**
     * {@inheritDoc}
     */
    public function key(): mixed
    {
        return $this->index;
    }

    /**
     * {@inheritDoc}
     */
    public function valid(): bool
    {
        return ($this->current !== false);
    }

    /**
     * {@inheritDoc}
     */
    public function current(): mixed
    {
        return $this->current;
    }

    /**
     * {@inheritDoc}
     */
    public function currentProgressPosition(): int
    {
        return ftell($this->handle);
    }

    /**
     * {@inheritDoc}
     */
    public function currentProgressLimit(): int
    {
        return $this->fstatSize;
    }
}
