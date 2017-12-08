<?php
namespace codename\core\io\datasource;

/**
 * [arraydata description]
 */
class arraydata extends \codename\core\io\datasource {

  /**
   * [setData description]
   * @param array $data [description]
   */
  public function setData(array $data) {
    $this->data = $data;
    $this->elementCount = count($this->data);
  }

  /**
   * [protected description]
   * @var int
   */
  protected $elementCount = null;

  /**
   * @inheritDoc
   */
  public function setConfig(array $config)
  {
    return;
  }

  /**
   * @inheritDoc
   */
  public function current()
  {
    return current($this->data);
  }

  /**
   * @inheritDoc
   */
  public function next()
  {
    return next($this->data);
  }

  /**
   * @inheritDoc
   */
  public function key()
  {
    return key($this->data);
  }

  /**
   * @inheritDoc
   */
  public function valid()
  {
    return $this->current() !== false;
  }

  /**
   * @inheritDoc
   */
  public function rewind()
  {
    reset($this->data);
  }

  /**
   * @inheritDoc
   */
  public function currentProgressPosition() : int
  {
    return $this->key();
  }

  /**
   * @inheritDoc
   */
  public function currentProgressLimit() : int
  {
    return $this->elementCount;
  }

}
