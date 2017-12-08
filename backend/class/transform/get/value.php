<?php namespace codename\core\io\transform\get;

/**
 * getter for values (source & field)
 */
class value extends \codename\core\io\transform\get {

  /**
   * @inheritDoc
   */
  public function internalTransform($value)
  {
    return $this->getValue($this->config['source'], $this->config['field'], $value);
  }

  /**
   * @inheritDoc
   */
  public function getSpecification(): array
  {
    return [
      'type' => 'transform',
      'source' => ["{$this->config['source']}.{$this->config['field']}"]
    ];
  }

}
