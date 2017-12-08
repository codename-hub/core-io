<?php
namespace codename\core\io\transform\convert;

use codename\core\exception;

/**
 * convert a value to a boolean
 */
class boolean extends \codename\core\io\transform\convert {

  /**
   * [$positiveValues description]
   * @var array
   */
  static $positiveValues = [ 1, '1', true, 'true' ];

  /**
   * [$negativeValues description]
   * @var array
   */
  static $negativeValues = [ 0, '0', false, 'false' ];

  /**
   * @inheritDoc
   */
  public function internalTransform($value)
  {
    $v = $this->getValue($this->config['source'], $this->config['field'], $value);
    if(in_array($v, self::$positiveValues, true)) {
      return true;
    } else if (in_array($v, self::$negativeValues, true)) {
      return false;
    } else {
      if($v === null && ($this->config['required'] ?? false)) {
        //
        // Required, but not set - case
        //
        $this->errorstack->addError('convert_boolean', 'MISSING_VALUE', [
          'config' => $this->config,
          'value' => $value
        ]);
      } else if($v !== null) {
        //
        // Error case
        //
        $this->errorstack->addError('convert_boolean', 'INVALID_VALUE', [
          'config' => $this->config,
          'value' => $value
        ]);
      }
      return null;
    }
  }

  /**
   * @inheritDoc
   */
  public function getSpecification(): array
  {
    return [
      'type' => 'transform',
      'source' => [ "{$this->config['source']}.{$this->config['field']}" ]
    ];
  }
}
