<?php
namespace codename\core\io\transform;

/**
 * [trim description]
 */
class trim extends \codename\core\io\transform {

  /**
   * character mask to be used for trimming
   * null falls back to PHP's standard/default
   * @var string|null
   */
  protected $characterMask = null;

  /**
   * @inheritDoc
   */
  public function __construct(array $config)
  {
    parent::__construct($config);

    // NOTE: data that may evaluate to false, somehow
    $this->characterMask = $config['character_mask'] ?? null;
  }

  /**
   * @inheritDoc
   */
  public function internalTransform($value)
  {
    $v = $this->getValue($this->config['source'], $this->config['field'], $value);
    // TODO: handle errors / required state

    if($this->characterMask === null) {
      return trim($v);
    } else {
      return trim($v, $this->characterMask);
    }
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
