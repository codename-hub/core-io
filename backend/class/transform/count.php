<?php

namespace codename\core\io\transform;

use codename\core\exception;
use codename\core\io\transform;

/**
 * [count description]
 */
class count extends transform
{
    /**
     * {@inheritDoc}
     * @param mixed $value
     * @return mixed
     * @throws exception
     */
    public function internalTransform(mixed $value): mixed
    {
        $v = $this->getValue($this->config['source'], $this->config['field'], $value);

        if (is_array($v)) {
            return \count($v);
        } else {
            return null;
        }
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
