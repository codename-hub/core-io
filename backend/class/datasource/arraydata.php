<?php

namespace codename\core\io\datasource;

use codename\core\io\datasource;

/**
 * [arraydata description]
 */
class arraydata extends datasource
{
    /**
     * @var array
     */
    protected array $data;

    /**
     * [protected description]
     * @var int
     */
    protected int $elementCount;

    /**
     * [setData description]
     * @param array $data [description]
     */
    public function setData(array $data): void
    {
        $this->data = $data;
        $this->elementCount = count($this->data);
    }

    /**
     * {@inheritDoc}
     */
    public function setConfig(array $config): void
    {
    }

    /**
     * {@inheritDoc}
     */
    public function next(): void
    {
        next($this->data);
    }

    /**
     * {@inheritDoc}
     */
    public function valid(): bool
    {
        return $this->current() !== false;
    }

    /**
     * {@inheritDoc}
     */
    public function current(): mixed
    {
        return current($this->data);
    }

    /**
     * {@inheritDoc}
     */
    public function rewind(): void
    {
        reset($this->data);
    }

    /**
     * {@inheritDoc}
     */
    public function currentProgressPosition(): int
    {
        return $this->key();
    }

    /**
     * {@inheritDoc}
     */
    public function key(): mixed
    {
        return key($this->data);
    }

    /**
     * {@inheritDoc}
     */
    public function currentProgressLimit(): int
    {
        return $this->elementCount;
    }
}
