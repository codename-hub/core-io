<?php

namespace codename\core\io\transform\get;

use codename\core\io\transform\get;

class randomstring extends get
{
    /**
     * {@inheritDoc}
     */
    public function internalTransform(mixed $value): mixed
    {
        $chars = $this->config['chars'] ?? '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $length = $this->config['length'];
        // @see https://stackoverflow.com/questions/4356289/php-random-string-generator
        return substr(str_shuffle(str_repeat($x = $chars, ceil($length / strlen($x)))), 0, $length);
    }

    /**
     * {@inheritDoc}
     */
    public function getSpecification(): array
    {
        return [
        ];
    }
}
