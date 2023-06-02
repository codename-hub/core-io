<?php

namespace codename\core\io\transform\get;

use codename\core\io\transform\get;
use DateTime;

/**
 * convert a string (date) to another date format
 */
class currentdatetime extends get
{
    /**
     * {@inheritDoc}
     */
    public function internalTransform(mixed $value): mixed
    {
        $datetime = new DateTime('now');
        if ($this->config['modify'] ?? false) {
            $datetime->modify($this->config['modify']);
        }
        return $datetime->format($this->config['format']);
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
