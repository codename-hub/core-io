<?php namespace codename\core\io\transform\get\number;

use codename\core\exception;

/**
 * getter for the fraction component of a number value
 */
class fraction extends \codename\core\io\transform\get\number {

  /**
   * @inheritDoc
   */
  public function internalTransform($value)
  {
    $v = $this->getValue($this->config['source'], $this->config['field'], $value);

    if($v === null) {
      if(isset($this->config['required']) && $this->config['required']) {
        $this->errorstack->addError('GET_NUMBER_REQUIRED', 0, [
          'config' => $this->config,
          'value' => $value
        ]);
      }
      return null;
    }

    if(!is_numeric($v)) {
      throw new exception('EXCEPTION_CORE_IO_TRANSFORM_GET_NUMBER_FRACTION_NOT_NUMERIC', exception::$ERRORLEVEL_ERROR, $v);
    }

    // list($whole, $decimal) = sscanf($v, '%d.%d');
    // NOTE: sscanf swallowed the leading zeros
    $value = explode('.', $v, 2);

    if($value[1] ?? false) {
      if($this->config['fraction_digits'] ?? false) {
        $value[1] = substr($value[1], 0 , $this->config['fraction_digits']);
      }
    }

    return $value[1] ?? null; // $v - floor($v);
  }

}
