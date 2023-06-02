<?php

namespace codename\core\io\transform\trim;

use codename\core\exception;
use codename\core\io\transform\trim;

class left extends trim
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
        // TODO: handle errors / required state

        if ($this->characterMask === null) {
            return ltrim($v);
        } else {
            return ltrim($v, $this->characterMask);
        }
    }
}
