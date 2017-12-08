<?php
namespace codename\core\io\transform\get;

/**
 * convert a string (date) to another date format
 */
class easterdatetime extends \codename\core\io\transform\get {

  /**
   * @inheritDoc
   */
  public function internalTransform($value)
  {
    $v = $this->getValue($this->config['source'], $this->config['field'], $value);
    $datetime = new \DateTime($v);
    $days = \easter_days($datetime->format('Y'));
    $datetime->setDate($datetime->format('Y'), 3, 21);
    $datetime->add(new \DateInterval("P{$days}D"));
    return $datetime->format($this->config['format'] ?? 'Y-m-d');
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
