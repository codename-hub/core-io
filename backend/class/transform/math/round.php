<?php
namespace codename\core\io\transform\math;

use codename\core\exception;

class round extends \codename\core\io\transform
{
  /**
   * calculation precision
   * @var int
   */
  protected $precision;

  /**
   * round mode (PHP const)
   * @var int
   */
  protected $mode;

  /**
   * @inheritDoc
   */
  public function __construct(array $config)
  {
    parent::__construct($config);

    //
    // By default, we round to the next integer.
    // in general, this is pow(10, -precision)
    // e.g.
    // precision 1  => 10^(-1)    => 0.1
    // precision 2  => 10^(-2)    => 0.01
    // precision 0  => 10^0       => 1 (stripping off fraction - but will stay a float!)
    // precision -1 => 10^(-(-1)) => 10
    //
    $this->precision = $this->config['precision'] ?? 0;
    $modeString = $this->config['mode'] ?? null;
    if(!$modeString) {
      $this->mode = PHP_ROUND_HALF_UP;
    } else {
      switch($modeString) {
        case 'half_up':
        case 'financial': // Alias
          $this->mode = PHP_ROUND_HALF_UP;
          break;
        case 'half_down':
          $this->mode = PHP_ROUND_HALF_DOWN;
          break;
        case 'half_even':
        case 'symmetric': // Alias
          $this->mode = PHP_ROUND_HALF_EVEN;
          break;
        case 'half_odd':
          $this->mode = PHP_ROUND_HALF_ODD;
          break;
        default:
          throw new exception('INVALID_ROUND_MODE', exception::$ERRORLEVEL_ERROR, $modeString);
      }
    }
  }

  /**
   * @inheritDoc
   */
  public function internalTransform($value)
  {
    $v = $this->getValue($this->config['source'] ?? 'source', $this->config['field'], $value);
    return round($v, $this->precision, $this->mode);
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
