<?php
namespace codename\core\io\transform;

/**
 * [count description]
 */
class count extends \codename\core\io\transform
{
  /**
   * @inheritDoc
   */
  public function internalTransform($value)
  {
    $v = $this->getValue($this->config['source'], $this->config['field'], $value);

    if(is_array($v)) {
      return \count($v);
    } else {
      return null;
    }
  }

  /**
   * @inheritDoc
   */
  public function getSpecification() : array
  {
    return [
      'type' => 'transform',
      'source' => [ "{$this->config['source']}.{$this->config['field']}" ]
    ];
  }
}
