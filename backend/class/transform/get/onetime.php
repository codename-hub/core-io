<?php

namespace codename\core\io\transform\get;

use codename\core\exception;
use codename\core\io\transform\get;

/**
 * performs a one-time transform
 * and keeps its value all the time
 */
class onetime extends get
{
    /**
     * override resetCache
     * to prevent cache reset
     * and keep the cached value
     * until destroyed
     *
     * {@inheritDoc}
     */
    public function resetCache(): void
    {
    }

    /**
     * {@inheritDoc}
     */
    public function resetErrors(): void
    {
    }

    /**
     * {@inheritDoc}
     * @param mixed $value
     * @return mixed
     * @throws exception
     */
    public function internalTransform(mixed $value): mixed
    {
        return $this->getValue($this->config['source'], $this->config['field'], $value);
    }

    /**
     * {@inheritDoc}
     */
    public function getSpecification(): array
    {
        return [
          'type' => 'transform',
          'source' => ["{$this->config['source']}.{$this->config['field']}"],
        ];
    }
}
