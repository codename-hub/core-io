<?php
namespace codename\core\io\transform;

use codename\core\exception;

/**
 * transform for hashing values
 */
class hash extends \codename\core\io\transform {

  /**
   * @inheritDoc
   */
  public function __construct(array $config)
  {
    parent::__construct($config);
    if(!isset($this->config['algorithm'])) {
      throw new exception('EXCEPTION_TRANSFORM_HASH_NO_ALGORITHM_SPECIFIED', exception::$ERRORLEVEL_ERROR);
    }
  }
  /**
   * @inheritDoc
   */
  public function internalTransform($value)
  {
    $v = $this->getValue($this->config['source'], $this->config['field'], $value);
    // TODO: handle errors / required state
    return hash($this->config['algorithm'], $v);
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
