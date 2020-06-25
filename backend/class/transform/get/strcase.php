<?php
namespace codename\core\io\transform\get;

use codename\core\exception;

/**
 * [strcase description]
 */
class strcase extends \codename\core\io\transform\get {

  /**
   * source
   * @var string
   */
  protected $source = null;

  /**
   * field from source
   * @var string
   */
  protected $field = null;

  /**
   * whether to work in case insensitive mode
   * @var string
   */
  protected $mode = null;

  /**
   * @inheritDoc
   */
  public function __construct(array $config)
  {
    parent::__construct($config);

    $this->mode = $config['mode'];

    if(!in_array($this->mode, ['upper', 'lower'])) {
      throw new exception('INVALID_STRCASE_MODE', exception::$ERRORLEVEL_ERROR, $this->mode);
    }

    $this->source = $config['source'];
    $this->field = $config['field'];
  }

  /**
   * @inheritDoc
   */
  public function internalTransform($value)
  {
    $v = $this->getValue($this->config['source'], $this->config['field'], $value);

    if($v === null) {
      return null;
    }

    if($this->mode === 'upper') {
      return strtoupper($v);
    } else if($this->mode === 'lower') {
      return strtolower($v);
    }
    return null;
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
