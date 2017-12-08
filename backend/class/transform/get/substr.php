<?php
namespace codename\core\io\transform\get;

/**
 * [substr description]
 */
class substr extends \codename\core\io\transform\get {

  /**
   * start
   * @var string
   */
  protected $start = 0;

  /**
   * length
   * @var int
   */
  protected $length = null;

  /**
   * field to substr
   * @var string
   */
  protected $field = [];

  /**
   * @inheritDoc
   */
  public function __construct(array $config)
  {
    parent::__construct($config);
    $this->start = $config['start'] ?? 0;
    $this->length = $config['length'] ?? null;
    $this->field = $config['field'];
  }

  /**
   * @inheritDoc
   */
  public function internalTransform($value)
  {
    $v = $this->getValue($this->config['source'], $this->config['field'], $value);

    if($this->length !== null) {
      return substr($v, $this->start, $this->length);
    } else {
      return substr($v, $this->start);
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
