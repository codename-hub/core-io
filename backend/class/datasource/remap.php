<?php

namespace codename\core\io\datasource;

use codename\core\config;
use codename\core\io\datasource;

/**
 * remap datasource
 * encapsulates another datasource
 * allows free/dynamic reconfiguration of mapping
 * before data is handed to the pipeline
 */
class remap extends datasource
{
    /**
     * the underlying datasource
     * @var datasource
     */
    protected datasource $datasource;

    /**
     * config object
     * @var config
     */
    protected config $config;
    /**
     * output value "current()" is based on the original source
     * remapped keys may replace old ones
     * @var bool
     */
    protected bool $sourceDataReplace = false;
    /**
     * key to store original/source data in (null => do not store)
     * @var string|null
     */
    protected ?string $sourceDataKey = null;
    /**
     * remapping config
     * @var array
     */
    protected array $remap = [];
    /**
     * inverted remapping config, for multiple new-keys for ONE old key
     * @var array
     */
    protected array $invertedRemap = [];
    /**
     * index
     * @var int
     */
    protected int $index = -1;
    /**
     * current value
     * @var mixed
     */
    protected mixed $currentValue = null;

    /**
     * [__construct description]
     * @param datasource $datasource [description]
     * @param array $config [description]
     */
    public function __construct(datasource $datasource, array $config)
    {
        $this->datasource = $datasource;
        $this->setConfig($config);
    }

    /**
     * {@inheritDoc}
     */
    public function setConfig(array $config): void
    {
        $this->config = new config($config);
        $this->remap = $this->config->get('remap');

        $this->invertedRemap = [];
        foreach ($this->remap as $oldKey => $newKey) {
            if (is_array($newKey)) {
                foreach ($newKey as $key) {
                    $this->invertedRemap[$key] = $oldKey;
                }
            } else {
                $this->invertedRemap[$newKey] = $oldKey;
            }
        }

        $this->sourceDataReplace = $this->config->get('replace') ?? false;
        $this->sourceDataKey = $this->config->get('source_data_key') ?? null;
    }

    /**
     * {@inheritDoc}
     */
    public function next(): void
    {
        $this->datasource->next();

        if ($this->datasource->valid()) {
            $this->currentValue = $this->remapData($this->datasource->current());
            $this->index++;
        } else {
            $this->currentValue = false;
        }
    }

    /**
     * {@inheritDoc}
     */
    public function valid(): bool
    {
        return $this->datasource->valid();
    }

    /**
     * the internal remapping function
     * @param array $input [description]
     * @return array        [description]
     */
    protected function remapData(array $input): array
    {
        if ($this->sourceDataReplace) {
            $output = $input;
        } else {
            $output = [];
        }

        // TODO: allow old values to be present?
        if ($this->sourceDataKey) {
            $output[$this->sourceDataKey] = $input;
        }

        // TODO: insert complete old dataset?
        // foreach($this->remap as $oldKey => $newKey) {
        //   // TODO: explicitly allow fallback - or check for array_key_exists?
        //   // throw on error?
        //   $output[$newKey] = $input[$oldKey] ?? null;
        // }
        foreach ($this->invertedRemap as $newKey => $oldKey) {
            // TODO: explicitly allow fallback - or check for array_key_exists?
            // throw on error?
            $output[$newKey] = $input[$oldKey] ?? null;
        }
        return $output;
    }

    /**
     * {@inheritDoc}
     */
    public function current(): mixed
    {
        return $this->currentValue;
    }

    /**
     * {@inheritDoc}
     */
    public function key(): mixed
    {
        return $this->datasource->key();
    }

    /**
     * {@inheritDoc}
     */
    public function rewind(): void
    {
        $this->index = -1;
        $this->datasource->rewind();

        if ($this->datasource->valid()) {
            $this->currentValue = $this->remapData($this->datasource->current());
        } else {
            $this->currentValue = false;
        }
    }

    /**
     * {@inheritDoc}
     */
    public function currentProgressPosition(): int
    {
        return $this->datasource->currentProgressPosition();
    }

    /**
     * {@inheritDoc}
     */
    public function currentProgressLimit(): int
    {
        return $this->datasource->currentProgressLimit();
    }
}
