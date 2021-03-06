<?php namespace codename\core\io\transform\get\number;

use codename\core\exception;

/**
 * getter for the whole component of a number value
 */
class whole extends \codename\core\io\transform\get\number {

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

    list($whole, $decimal) = sscanf($v, '%d.%d');

    return $whole; // floor($v);
  }

}
