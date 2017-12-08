<?php
namespace codename\core\io\transform\calculate;

/**
 * [divide description]
 */
class divide extends \codename\core\io\transform\calculate {
  /**
   * calculation precision
   * @var int
   */
  protected $precision;

  /**
   * @inheritDoc
   */
  public function __construct(array $config)
  {
    parent::__construct($config);

    $this->precision = $this->config['precision'] ?? 15;
  }

  /**
   * @inheritDoc
   */
  public function internalTransform($value)
  {
    $v = null;

    foreach($this->config['factors'] as $factor) {
      if ($v === null) {
        $v = is_array($factor) ? $this->getValue($factor['source'], $factor['field'], $value) : $factor;
        continue;
      }
      if(is_array($factor)) {
        $v = bcdiv($v, $this->getValue($factor['source'] ?? 'source', $factor['field'], $value), $this->precision);
      } else {
        $v = bcdiv($v, $factor, $this->precision);
      }
    }

    return $v;
  }

  /**
   * @inheritDoc
   */
  public function getSpecification(): array
  {
    $sources = [];
    foreach($this->config['factors'] as $factor) {
      if(!is_array($factor)) {
        // bare value
        continue;
      }
      $sources[] = "{$factor['source']}.{$factor['field']}";
    }

    return [
      'type' => 'transform',
      'source' => $sources // [ "{$this->config['source']}.{$this->config['field']}" ]
    ];
  }
}
