<?php

namespace codename\core\io\transform\convert;

use codename\core\exception;
use codename\core\io\transform\convert;
use LogicException;

class json extends convert
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
            if ($this->config['mode'] === 'encode') {
                return json_encode($v);
            } elseif ($this->config['mode'] === 'decode') {
                return json_decode($v, true);
            } else {
                throw new LogicException('Not implemented and shouldn\'t be');
            }
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
