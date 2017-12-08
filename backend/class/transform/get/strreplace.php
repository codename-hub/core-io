<?php
namespace codename\core\io\transform\get;

/**
 * [substr description]
 */
class strreplace extends \codename\core\io\transform\get {

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
   * @var bool
   */
  protected $caseInsensitive = false;

  /**
   * @inheritDoc
   */
  public function __construct(array $config)
  {
    parent::__construct($config);

    $this->caseInsensitive = $config['case_insensitive'];
    $this->source = $config['source'];
    $this->field = $config['field'];

    if(is_array($search = $config['search'])) {
      if($search['source'] && $search['field']) {
        $this->searchDynamic = $search;
      } else {
        $this->searchStatic = $search; // array-based
      }
    } else {
      $this->searchStatic = $config['search'];
    }

    if(is_array($replace = $config['replace'])) {
      if($replace['source'] && $replace['field']) {
        $this->replaceDynamic = $replace;
      } else {
        $this->replaceStatic = $replace; // array-based
      }
    } else {
      $this->replaceStatic = $config['replace'];
    }
  }

  /**
   * static value to search for - single string or array of strings
   * @var string|array|null
   */
  protected $searchStatic = null;

  /**
   * dynamic value to search for (other source/transform)
   * @var array|null
   */
  protected $searchDynamic = null;

  /**
   * static value to replace with - single string or array of strings
   * @var string|array|null
   */
  protected $replaceStatic = null;

  /**
   * dynamic value to replace with (other source/transform)
   * @var array|null
   */
  protected $replaceDynamic = null;

  /**
   * @inheritDoc
   */
  public function internalTransform($value)
  {
    $subject = $this->getValue($this->config['source'], $this->config['field'], $value);

    $search = $this->searchStatic;
    if($this->searchDynamic) {
      $search = $this->getValue($this->searchDynamic['source'], $this->searchDynamic['field'], $value);
    }

    $replace = $this->replaceStatic;
    if($this->replaceDynamic) {
      $replace = $this->getValue($this->replaceDynamic['source'], $this->replaceDynamic['field'], $value);
    }

    if($this->caseInsensitive) {
      return str_ireplace($search, $replace, $subject);
    } else {
      return str_replace($search, $replace, $subject);
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
