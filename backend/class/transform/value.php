<?php
namespace codename\core\io\transform;

/**
 * [value description]
 * simple value storage
 */
class value extends \codename\core\io\transform {

  /**
   * @inheritDoc
   */
  public function internalTransform($value)
  {
    return $this->config['value'];
  }

  /**
   * @inheritDoc
   */
  public function getSpecification(): array
  {
    return [
      'type' => 'transform',
      'source' => []
    ];
  }

}
