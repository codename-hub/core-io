<?php
namespace codename\core\io\transform;

use codename\core\exception;

/**
 * dummy transform to access internal pipeline data
 */
class dummy extends \codename\core\io\transform {

  /**
   * returns a value from inside the pipeline
   * @param  string $sourceType [description]
   * @param  [type] $field      [description]
   * @param  [type] $value      [description]
   * @return [type]             [description]
   */
  public function getInternalPipelineValue(string $sourceType, $field, $value) {
    return $this->getValue($sourceType, $field, $value);
  }

  /**
   * @inheritDoc
   */
  public function internalTransform($value)
  {
    throw new \LogicException('Not implemented and shouln\'t be');
  }

  /**
   * @inheritDoc
   */
  public function getSpecification() : array
  {
    throw new \LogicException('Not implemented and shouln\'t be');
  }

}
