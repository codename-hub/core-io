<?php

namespace codename\core\io\transform\convert\numberformat;

use codename\core\exception;
use codename\core\io\transform\convert\numberformat;

/**
 * convert a number to a specific format
 */
class format extends numberformat
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
        if ($v === null && ($this->config['required'] ?? false)) {
            $this->errorstack->addError('convert_numberformat_format', 'MISSING_VALUE', [
              'config' => $this->config,
              'value' => $value,
            ]);
            return null;
        }

        $formatted = $this->numberFormatter->format($v);
        if ($formatted === false) {
            $this->errorstack->addError('convert_numberformat_format', 'INVALID_VALUE', [
              'config' => $this->config,
              'value' => $value,
            ]);
        }
        return $formatted;
    }
}
