<?php

namespace codename\core\io\transform\compare;

use codename\core\exception;
use codename\core\io\transform\compare;

/**
 * [isequal description]
 */
class isday extends compare
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
        $datetime = new \DateTime($v);
        $day = $datetime->format('l');
        if (is_array($this->value)) {
            return in_array($day, $this->value);
        } else {
            return $day === $this->value;
        }
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
