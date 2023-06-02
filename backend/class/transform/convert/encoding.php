<?php

namespace codename\core\io\transform\convert;

use codename\core\exception;
use codename\core\io\transform\convert;

/**
 * convert a string to another encoding
 */
class encoding extends convert
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
        if ($v !== null) {
            return mb_convert_encoding($v, $this->config['to'], $this->config['from']);
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
