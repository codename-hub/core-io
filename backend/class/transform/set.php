<?php namespace codename\core\io\transform;

class set extends \codename\core\io\transform
{
  /**
   * @inheritDoc
   */
  public function internalTransform($value)
  {
    throw new \LogicException('Not implemented'); // TODO
  }

  /**
   * @inheritDoc
   */
  public function getSpecification() : array
  {
    return [];
  }


}
