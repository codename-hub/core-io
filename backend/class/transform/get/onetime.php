<?php
namespace codename\core\io\transform\get;

/**
 * performs a one-time transform
 * and keeps its value all the time
 */
class onetime extends \codename\core\io\transform\get
{
  /**
   * override resetCache
   * to prevent cache reset
   * and keep the cached value
   * until destroyed
   *
   * @inheritDoc
   */
  public function resetCache()
  {
  }

  /**
   * @inheritDoc
   */
  public function resetErrors()
  {
  }

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
  public function getSpecification() : array
  {
    return [
      'type' => 'transform',
      'source' => [ "{$this->config['source']}.{$this->config['field']}" ]
    ];
  }
}
