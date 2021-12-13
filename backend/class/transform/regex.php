<?php
namespace codename\core\io\transform;

use codename\core\exception;

/**
 * [regex description]
 */
class regex extends \codename\core\io\transform
{
  /**
   * @inheritDoc
   */
  public function __construct(array $config)
  {
    parent::__construct($config);
    $this->regexValue = $config['regex_value'];
    $this->mode = $config['mode'];

    if(!in_array($this->mode, [
      'match_success', 'match', 'replace'
    ])) {
      throw new exception('TRANSFORM_REGEX_INVALID_CONFIG', exception::$ERRORLEVEL_ERROR, $config);
    }
  }

  /**
   * [protected description]
   * @var string
   */
  protected $regexValue = null;

  /**
   * [protected description]
   * @var string
   */
  protected $mode = null;

  /**
   * @inheritDoc
   */
  public function internalTransform($value)
  {
    $v = $this->getValue($this->config['source'], $this->config['field'], $value);

    if($this->mode === 'match' || $this->mode === 'match_success') {
      $matches = [];
      $res = preg_match($this->regexValue, $v, $matches);

      if($res === 1) {
        // match!
        if($this->mode === 'match_success') {
          return true;
        } else {
          return $matches;
        }
      } else if($res === 0) {
        // no match
        if($this->mode === 'match_success') {
          return false;
        } else {
          return null;
        }
      } else {
        // error
        $this->errorstack->addError('REGEX_ERROR', 0, [
          'config'  => $this->config,
          'value'   => $value,
        ]);
        return null;
      }

    } else if($this->mode === 'replace') {
      // TODO
    }
  }

  /**
   * @inheritDoc
   */
  public function getSpecification() : array
  {
    return [
      'type' => 'transform',
      'source' => [ "{$this->config['source']}.{$this->config['field']}" ]
    ];
  }
}
