<?php
namespace codename\core\io\transform\get\filtered;

use codename\core\exception;

class validated extends \codename\core\io\transform\get\filtered
{
  /**
   * array of validators to use
   * @var \codename\core\validator[]
   */
  protected $validators = null;

  /**
   * @inheritDoc
   */
  public function __construct(array $config)
  {
    parent::__construct($config);

    if($validator = $config['validator'] ?? false) {
      if(is_array($validator)) {
        $this->validators = [];
        foreach($validator as $validatorName) {
          $this->validators[] = \codename\core\app::getValidator($validatorName);
        }
      } else {
        $this->validators = [
          \codename\core\app::getValidator($validator)
        ];
      }
    } else {
      throw new exception('NO_VALIDATOR_SPECIFIED', exception::$ERRORLEVEL_ERROR);
    }
  }

  /**
   * @inheritDoc
   */
  public function internalTransform($value)
  {
    // reset validators before execution
    foreach($this->validators as $validatorInstance) {
      $validatorInstance->reset();
    }

    // get value
    $v = $this->getValue($this->config['source'], $this->config['field'], $value);

    $overallErrorCount = 0;

    // iterate over every validator
    foreach($this->validators as $validatorInstance) {

      // validate!
      $validatorInstance->validate($v);

      if($errorCount = count($errors = $validatorInstance->getErrors()) > 0) {
        $overallErrorCount += $errorCount;
        $this->errorstack->addErrors($errors);
      }
    }

    if($overallErrorCount === 0) {
      return $v;
    } else {
      // NOTE: we already added errors before...
      return null;
    }
  }
}
