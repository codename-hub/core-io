<?php
namespace codename\core\io\transform\get;

/**
 * convert a string (date) to another date format
 */
class currentdatetime extends \codename\core\io\transform\get {

  /**
   * @inheritDoc
   */
  public function internalTransform($value)
  {
    $datetime = new \DateTime('now');
    if($this->config['modify'] ?? false) {
      $datetime->modify($this->config['modify']);
    }
    return $datetime->format($this->config['format']);
  }

  /**
   * @inheritDoc
   */
  public function getSpecification() : array
  {
    return [
      'type' => 'transform',
      'source' => []
    ];
  }
}
