<?php

namespace codename\core\io\transform\pad;

use codename\core\exception;
use codename\core\io\transform\pad;

use function str_pad;

/**
 * pad_right
 */
class right extends pad
{
    /**
     * {@inheritDoc}
     * @param mixed $value
     * @return mixed
     * @throws exception
     */
    public function internalTransform(mixed $value): mixed
    {
        $v = $this->getValue($this->config['source'] ?? 'source', $this->config['field'], $value);
        return str_pad($v, $this->config['length'], $this->config['string']);
    }
}
