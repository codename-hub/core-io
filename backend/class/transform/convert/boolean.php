<?php

namespace codename\core\io\transform\convert;

use codename\core\exception;
use codename\core\io\transform\convert;

/**
 * convert a value to a boolean
 */
class boolean extends convert
{
    /**
     * [$positiveValues description]
     * @var array
     */
    public static array $positiveValues = [1, '1', true, 'true'];

    /**
     * [$negativeValues description]
     * @var array
     */
    public static array $negativeValues = [0, '0', false, 'false'];

    /**
     * {@inheritDoc}
     * @param mixed $value
     * @return mixed
     * @throws exception
     */
    public function internalTransform(mixed $value): mixed
    {
        $v = $this->getValue($this->config['source'], $this->config['field'], $value);
        if (in_array($v, self::$positiveValues, true)) {
            return true;
        } elseif (in_array($v, self::$negativeValues, true)) {
            return false;
        } else {
            if ($v === null && ($this->config['required'] ?? false)) {
                //
                // Required, but not set - case
                //
                $this->errorstack->addError('convert_boolean', 'MISSING_VALUE', [
                  'config' => $this->config,
                  'value' => $value,
                ]);
            } elseif ($v !== null) {
                //
                // Error case
                //
                $this->errorstack->addError('convert_boolean', 'INVALID_VALUE', [
                  'config' => $this->config,
                  'value' => $value,
                ]);
            }
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
