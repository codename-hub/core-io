<?php
namespace codename\core\io\transform\convert;

/**
 * convert a string (date) to another date format
 */
class datetime extends \codename\core\io\transform\convert {

  /**
   * @inheritDoc
   */
  public function __construct(array $config)
  {
    parent::__construct($config);
    $this->source = $this->config['source'];
    $this->field = $this->config['field'];
    $this->required = $this->config['required'] ?? null;
    $this->sourceFormat = $this->config['source_format'];
    $this->sourceFormatIsArray = is_array($this->config['source_format']);
    $this->targetFormat = $this->config['target_format'];
    $this->modify = $this->config['modify'] ?? null;
    $this->set_time_to_null = $this->config['set_time_to_null'] ?? null;
  }

  /**
   * source type
   * @var string
   */
  protected $source = null;

  /**
   * source field to use
   * @var string
   */
  protected $field = null;

  /**
   * whether transform should output something non-falsy
   * @var bool
   */
  protected $required = null;

  /**
   * whether we're using arrays for source/input format specs
   * @var bool
   */
  protected $sourceFormatIsArray = null;

  /**
   * source format(s) allowed
   * @var string|string[]
   */
  protected $sourceFormat = null;

  /**
   * target format to convert to
   * @var string
   */
  protected $targetFormat = null;

  /**
   * modifier string
   * @var string|null
   */
  protected $modify = null;

  /**
   * [protected description]
   * @var bool|null
   */
  protected $set_time_to_null = null;

  /**
   * @inheritDoc
   */
  public function internalTransform($value)
  {
    $v = $this->getValue($this->source, $this->field, $value);

    if($v === null) {
      if($this->required) {
        $this->errorstack->addError('VALUE_NULL', 0, [
          'config' => $this->config,
          'value' => $value
        ]);
      }
      return null;
    } else {
      $dt = false;
      if($this->sourceFormatIsArray) {
        foreach($this->sourceFormat as $sourceFormat) {
          $dt = \DateTime::createFromFormat($sourceFormat, $v);
          if($dt !== false) {
            // first successful match
            break;
          }
        }
      } else {
        $dt = \DateTime::createFromFormat($this->sourceFormat, $v);
      }
      if($dt !== false) {
        if ($this->set_time_to_null ?? false) {
          $dt->setTime(0,0);
        }
        if($this->modify) {
          $dt->modify($this->modify);
        }
        return $dt->format($this->targetFormat);
      } else {
        // NOTE: we have to log this error to the errorstack either way
        // as we have a value (!= null) that leads to an internal conversion error
        $this->errorstack->addError('convert_datetime', 'INVALID_FORMAT', [
          'config' => $this->config,
          'value' => $value
        ]);
        return null;
      }
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
