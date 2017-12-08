<?php
namespace codename\core\io\transform\compare;

use codename\core\exception;

/**
 * [isequal description]
 */
class number extends \codename\core\io\transform\compare {

  /**
   * @inheritDoc
   */
  public function __construct(array $config)
  {
    parent::__construct($config);
    $this->operator = $config['operator'];
    $this->precision = $config['precision'];
  }

  /**
   * operator to use
   * @var string
   */
  protected $operator = null;

  /**
   * precision for BCMath to use
   * @var int
   */
  protected $precision = null;

  /**
   * @inheritDoc
   */
  public function internalTransform($value)
  {
    // NOTE: fallback to source, if none defined
    $v = $this->getValue($this->config['source'], $this->config['field'], $value);
    $v2 = is_array($this->value) ? $this->getValue($this->value['source'], $this->value['field'], $value) : $this->value;

    if($this->operator === '=') {
      return bccomp($v, $v2, $this->precision) === 0;
    } else if($this->operator === '!=') {
      return bccomp($v, $v2, $this->precision) !== 0;
    } else if($this->operator === '>') {
      return bccomp($v, $v2, $this->precision) === 1;
    } else if($this->operator === '<') {
      return bccomp($v, $v2, $this->precision) === -1;
    } else if($this->operator === '>=') {
      return bccomp($v, $v2, $this->precision) >= 0;
    } else if($this->operator === '<=') {
      return bccomp($v, $v2, $this->precision) <= 0;
    } else {
      throw new exception('INVALID_OPERATOR', exception::$ERRORLEVEL_ERROR, $this->operator);
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
      'source' => [ "{$this->config['source']}.{$this->config['field']}" ]
    ];
  }

}
