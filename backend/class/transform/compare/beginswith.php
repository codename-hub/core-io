<?php
namespace codename\core\io\transform\compare;

/**
 * [beginswith description]
 */
class beginswith extends \codename\core\io\transform\compare {

  /**
   * @inheritDoc
   */
  public function internalTransform($value)
  {
    // NOTE: fallback to source, if none defined
    $baseValue = $this->getValue($this->config['source'] ?? 'source', $this->config['field'], $value);
    return substr($baseValue, 0, strlen($this->value)) === $this->value;
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
