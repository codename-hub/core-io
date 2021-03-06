<?php
namespace codename\core\io\transform;

/**
 * [explode description]
 */
class explode extends \codename\core\io\transform {

  /**
   * delimiter
   * @var string
   */
  protected $delimiter = ',';

  /**
   * limit (explode limit)
   * 0 for no limit
   * @var int
   */
  protected $limit = null;

  /**
   * field to explode
   * @var string
   */
  protected $field = [];

  /**
   * @inheritDoc
   */
  public function __construct(array $config)
  {
    parent::__construct($config);
    $this->delimiter = $config['delimiter'] ?? ',';
    $this->limit = $config['limit'] ?? null;
    $this->field = $config['field'];
  }

  /**
   * @inheritDoc
   */
  public function internalTransform($value)
  {
    // NOTE: PHP's explode() with NO limit needs limit param to be omitted
    // do not set it to 'null', as it equals to 0, which leads to 1
    $v = $this->getValue($this->config['source'], $this->config['field'], $value);

    $delimiter = null;
    if(is_array($this->delimiter)) {
      if(($this->delimiter['source'] ?? false) && ($this->delimiter['field'] ?? false)) {
        $delimiter = $this->getValue($this->delimiter['source'], $this->delimiter['field'], $value);
      } else {
        throw new \LogicException('Not supported: transform explode using array delimiter (non-source) or incorrect config');
      }
    } else {
      $delimiter = $this->delimiter;
    }

    if($this->limit !== null) {
      return explode($delimiter, $v, $this->limit);
    } else {
      return explode($delimiter, $v);
    }

    return $transformResult;
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
