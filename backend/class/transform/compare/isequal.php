<?php
namespace codename\core\io\transform\compare;

/**
 * [isequal description]
 */
class isequal extends \codename\core\io\transform\compare {

  /**
   * @inheritDoc
   */
  public function internalTransform($value)
  {
    // NOTE: fallback to source, if none defined
    return $this->getValue($this->config['source'] ?? 'source', $this->config['field'], $value) == $this->value;
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
