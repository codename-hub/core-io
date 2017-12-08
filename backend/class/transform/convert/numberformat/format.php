<?php
namespace codename\core\io\transform\convert\numberformat;

use codename\core\exception;

/**
 * convert a number to a specific format
 */
class format extends \codename\core\io\transform\convert\numberformat {

  /**
   * @inheritDoc
   */
  public function internalTransform($value)
  {
    // doesn't work:
    // return floatval($value);
    $v = $this->getValue($this->config['source'], $this->config['field'], $value);
    if($v === null && ($this->config['required'] ?? false)) {
      $this->errorstack->addError('convert_numberformat_format', 'MISSING_VALUE', [
        'config' => $this->config,
        'value' => $value
      ]);
      return null;
    }

    $formatted = $this->numberFormatter->format($v);
    if($formatted === false) {
      $this->errorstack->addError('convert_numberformat_format', 'INVALID_VALUE', [
        'config' => $this->config,
        'value' => $value
      ]);
    }
    return $formatted;
  }

}
