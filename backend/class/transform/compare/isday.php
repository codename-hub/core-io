<?php
namespace codename\core\io\transform\compare;

/**
 * [isequal description]
 */
class isday extends \codename\core\io\transform\compare {

  /**
   * @inheritDoc
   */
  public function internalTransform($value)
  {
    $v = $this->getValue($this->config['source'] ?? 'source', $this->config['field'], $value);
    $datetime = new \DateTime($v);
    $day = $datetime->format('l');
    if (is_array($this->value)) {
      return in_array($day,$this->value);
    } else {
      return ($day === $this->value ? true : false);
    }
  }

  /**
   * @inheritDoc
   */
  public function getSpecification(): array
  {
    return [
      'type' => 'transform',
      // TODO: implement transform as a source!
      'source' => [ "source.{$this->config['field']}" ]
    ];
  }

}
