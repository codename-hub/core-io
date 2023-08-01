<?php

namespace codename\core\io\transform\get;

use codename\core\exception;
use codename\core\io\transform\get;

/**
 * getter for fallback values
 */
class fallback extends get
{
    /**
     * {@inheritDoc}
     * @param mixed $value
     * @return mixed
     * @throws exception
     */
    public function internalTransform(mixed $value): mixed
    {
        foreach ($this->config['fallback'] as $fallback) {
            $v = $this->getValue($fallback['source'], $fallback['field'], $value);
            if ($v !== null) {
                return $v;
            }
        }
        if (isset($this->config['required']) && $this->config['required']) {
            $this->errorstack->addError('VALUE_NULL', 0, [
              'config' => $this->config,
              'value' => $value,
            ]);
        }
        return null;
    }

    /**
     * {@inheritDoc}
     */
    public function getSpecification(): array
    {
        $sources = [];

        foreach ($this->config['fallback'] as $v) {
            $field = is_array($v['field']) ? implode('.', $v['field']) : $v['field'];
            $sources[] = "{$v['source']}.$field";
        }

        return [
          'type' => 'transform',
          'source' => $sources,
        ];
    }
}
