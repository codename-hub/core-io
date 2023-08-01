<?php

namespace codename\core\io\transform;

use codename\core\io\transform;

/**
 * [value description]
 * simple value storage
 */
class value extends transform
{
    /**
     * {@inheritDoc}
     */
    public function internalTransform(mixed $value): mixed
    {
        return $this->config['value'];
    }

    /**
     * {@inheritDoc}
     */
    public function getSpecification(): array
    {
        return [
          'type' => 'transform',
          'source' => [],
        ];
    }
}
