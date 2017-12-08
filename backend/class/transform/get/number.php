<?php namespace codename\core\io\transform\get;

/**
 * base getter for number value components
 */
abstract class number extends \codename\core\io\transform\get {

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
