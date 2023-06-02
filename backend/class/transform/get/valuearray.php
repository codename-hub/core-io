<?php

namespace codename\core\io\transform\get;

use codename\core\exception;
use codename\core\io\transform\get;

use function is_array;

/**
 * getter for a new array of values
 */
class valuearray extends get
{
    /**
     * {@inheritDoc}
     * @param mixed $value
     * @return mixed
     * @throws exception
     */
    public function internalTransform(mixed $value): mixed
    {
        $arr = [];
        foreach ($this->config['elements'] as $k => $v) {
            if (!is_array($v)) {
                // bare value
                $arr[$k] = $v;
            } else {
                $val = $this->getValue($v['source'], $v['field'], $value);
                if (($v['required'] ?? false) && $val === null) {
                    $this->errorstack->addError('GET_VALUEARRAY_MISSING_KEY', 0, [
                      'config' => $this->config,
                      'key' => $k,
                      'value' => $value,
                    ]);
                    continue; // ??
                } elseif ($val === null) {
                    if (!($v['allow_null'] ?? false)) {
                        continue;
                    }
                }
                $arr[$k] = $val;
            }
        }
        return $arr;
    }

    /**
     * {@inheritDoc}
     */
    public function getSpecification(): array
    {
        $sources = [];

        foreach ($this->config['elements'] as $k => $v) {
            if (!is_array($v)) {
                // BARE VALUE!
                continue;
            }

            if (is_array($v['field'])) {
                $field = implode('.', $v['field']);
            } else {
                $field = $v['field'];
            }

            $sources[$k] = "{$v['source']}.$field";
        }

        return [
          'type' => 'transform',
          'source' => $sources,
        ];
    }
}
