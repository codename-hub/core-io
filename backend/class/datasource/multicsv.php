<?php

namespace codename\core\io\datasource;

use codename\core\config;
use codename\core\exception;
use codename\core\io\datasource;

/**
 * wraps multiple CSV files seamlessly
 */
class multicsv extends datasource
{
    /**
     * underlying csv datasources
     * @var array
     */
    protected array $datasources = [];

    /**
     * current datasource/file index
     * @var int
     */
    protected int $fileindex = 0;

    /**
     * headed setting for all csv's
     * @var bool
     */
    protected bool $headed = true;

    /**
     * delimiter for all csv's
     * @var string
     */
    protected string $delimiter = ';';
    /**
     * [protected description]
     * @var null|config
     */
    protected ?config $config = null;
    /**
     * [protected description]
     * @var int
     */
    protected int $overallKey = 0;
    /**
     * [protected description]
     * @var array
     */
    protected array $positions = [];
    /**
     * just save the progress limits of the datasources
     * to make sure we don't cause IO-intensive operations somehow
     * @var int|null
     */
    protected ?int $cachedProgressLimit = null;

    /**
     * [__construct description]
     * @param string|string[] $files [filepath array]
     * @param array $config [config]
     * @throws exception
     */
    public function __construct(array|string $files, array $config = [])
    {
        // make an array of files, if it's ONE file.
        if (!is_array($files)) {
            $files = [$files];
        }

        $this->setConfig($config);

        $i = 0;
        foreach ($files as $file) {
            // CHANGED 2021-04-30: we now pass through the full config to nested datasources
            $subconfig = $this->config->get();
            $this->datasources[$i] = new csv($file, $subconfig);
            $i++;
        }

        $this->fileindex = 0;
    }

    /**
     * {@inheritDoc}
     */
    public function setConfig(array $config): void
    {
        // CHANGED 2021-04-30: we now pass through the full config to nested datasources
        // and fallback configs to default + store them in a member variable
        $this->delimiter = $config['delimiter'] = $config['delimiter'] ?? ';';
        $this->headed = $config['headed'] = $config['headed'] ?? true;
        $this->config = new config($config);

        foreach ($this->datasources as $datasource) {
            // CHANGED 2021-04-30: we now pass through the full config to nested datasources
            $subconfig = $this->config->get();
            $datasource->setConfig($subconfig);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function next(): void
    {
        $this->datasources[$this->fileindex]->next();
        if (!$this->datasources[$this->fileindex]->valid()) {
            if ($this->fileindex < (count($this->datasources) - 1)) {
                // move on to next datasource
                $this->fileindex++;
            }
        }
        $this->overallKey++;
    }

    /**
     * {@inheritDoc}
     */
    public function valid(): bool
    {
        return ($this->datasources[$this->fileindex]->current() !== false);
    }

    /**
     * {@inheritDoc}
     */
    public function current(): mixed
    {
        return $this->datasources[$this->fileindex]->current();
    }

    /**
     * {@inheritDoc}
     */
    public function key(): mixed
    {
        return $this->overallKey;
    }

    /**
     * {@inheritDoc}
     */
    public function rewind(): void
    {
        $this->fileindex = 0;
        foreach ($this->datasources as $ds) {
            $ds->rewind();
        }
        $this->overallKey = 0;
        $this->positions = [];
    }

    /**
     * {@inheritDoc}
     */
    public function currentProgressPosition(): int
    {
        $this->positions[$this->fileindex] = $this->datasources[$this->fileindex]->currentProgressPosition();
        return array_sum($this->positions);
    }

    /**
     * {@inheritDoc}
     */
    public function currentProgressLimit(): int
    {
        if (!$this->cachedProgressLimit) {
            $count = 0;
            foreach ($this->datasources as $datasource) {
                $count += $datasource->currentProgressLimit();
            }
            $this->cachedProgressLimit = $count;
        }
        return $this->cachedProgressLimit;
    }
}
