<?php

namespace codename\core\io\transform\compare;

use codename\core\exception;
use codename\core\io\transform\compare;

/**
 * [isequal description]
 */
class isequal extends compare
{
    /**
     * {@inheritDoc}
     * @param mixed $value
     * @return mixed
     * @throws exception
     */
    public function internalTransform(mixed $value): mixed
    {
        // NOTE: fallback to source, if none defined
        return $this->getValue($this->config['source'] ?? 'source', $this->config['field'], $value) == $this->value;
    }

    /**
     * {@inheritDoc}
     */
    public function getSpecification(): array
    {
        return [
          'type' => 'transform',
            // TODO: implement transform as a source!
          'source' => ["source.{$this->config['field']}"],
        ];
    }
}
