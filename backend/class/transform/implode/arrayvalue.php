<?php
namespace codename\core\io\transform\implode;

use codename\core\exception;

/**
 * implodes a single source, which is an array for itself
 */
class arrayvalue extends \codename\core\io\transform {

  /**
   * @inheritDoc
   */
  public function __construct(array $config)
  {
    parent::__construct($config);
    $this->glue = $config['glue'] ?? '';
    $this->source = $config['source'] ?? null;
    $this->field = $config['field'] ?? null;
  }

  /**
   * @inheritDoc
   */
  public function internalTransform($value)
  {
    $arrayValue = $this->getValue($this->source, $this->field, $value);
    return implode($this->glue, $arrayValue);
  }

  /**
   * @inheritDoc
   */
  public function getSpecification(): array
  {
    $sources = [];
    $sources[] = "{$this->source}.{$this->field}";
    return [
      'type' => 'transform',
      'source' => $sources
    ];
  }
}
