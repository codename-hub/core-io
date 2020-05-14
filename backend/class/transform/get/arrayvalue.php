<?php namespace codename\core\io\transform\get;

/**
 * getter for array values (via index/key)
 */
class arrayvalue extends \codename\core\io\transform\get {

  /**
   * @inheritDoc
   */
  public function __construct(array $config)
  {
    parent::__construct($config);

    $this->source = $this->config['source'];
    $this->isSource = $this->config['source'] == 'source';
    $this->field = $this->config['field'];
    $this->indexIsArray = is_array($this->config['index']);
    $this->indexValue  = $this->config['index'];
    $this->indexSource = $this->config['index']['source'] ?? null;
    $this->indexField = $this->config['index']['field'] ?? null;
    $this->required = isset($this->config['required']) && $this->config['required'];
  }

  /**
   * [protected description]
   * @var bool
   */
  protected $required = null;

  /**
   * [protected description]
   * @var string
   */
  protected $source = null;

  /**
   * [protected description]
   * @var bool
   */
  protected $isSource = null;

  /**
   * [protected description]
   * @var string
   */
  protected $field = null;

  /**
   * [protected description]
   * @var bool
   */
  protected $indexIsArray = null;

  /**
   * [protected description]
   * @var array|string
   */
  protected $indexValue = null;

  /**
   * [protected description]
   * @var string
   */
  protected $indexSource = null;

  /**
   * [protected description]
   * @var string
   */
  protected $indexField = null;

  /**
   * @inheritDoc
   */
  public function internalTransform($value)
  {
    // if(!isset($this->config['source'])) {
    //   echo("<pre>");
    //   print_r($this->config);
    //   echo("</pre>");
    // }

    if($this->isSource) {
      // special case where we need to fetch a complete array
      // and access only an index later on
      if($this->field) {
        $v = $value[$this->field];
      } else {
        $v = $value;
      }
      // $v = isset($this->config['field']) ? $value[$this->config['field']] : $value;
    } else {
      $v = $this->getValue($this->source, $this->field, $value);
    }

    if($this->indexIsArray) {
      // dynamic index
      $index = $this->getValue($this->indexSource, $this->indexField, $value);

      if($v[$index] ?? false) {
        if($this->required) {
          $this->errorstack->addError('GET_ARRAYVALUE_MISSING', 0, [
            'config' => $this->config,
            'value' => $value
          ]);
        }
        return null;
      }

      return $v[$index];
    } else {

      if($v[$this->indexValue] ?? false) {
        if($this->required) {
          $this->errorstack->addError('GET_ARRAYVALUE_MISSING', 0, [
            'config' => $this->config,
            'value' => $value
          ]);
        }
        return null;
      }

      return $v[$this->indexValue];
    }


    // if($this->config['source'] == 'transform') {
    //   $v = $this->getTransformValue($this->config['field'], $value);
    //   if(isset($this->config['required']) && $this->config['required'] && !isset($v[$this->config['index']])) {
    //     $this->errorstack->addError('GET_ARRAYVALUE_MISSING', 0, [
    //       'config' => $this->config,
    //       'value' => $value
    //     ]);
    //   }
    //   return $v[$this->config['index']] ?? null;
    // } else if($this->config['source'] == 'source') {
    //   // if(isset($this->config['required']) && !$this->config['required'] && !isset($value[$this->config['field']])) {
    //   //   return null;
    //   // } else {
    //     $v = isset($this->config['field']) ? $value[$this->config['field']] : $value;
    //     if(!isset($v[$this->config['index']])) {
    //       if(isset($this->config['required']) && $this->config['required']) {
    //         $this->errorstack->addError('GET_ARRAYVALUE_MISSING', 0, [
    //           'config' => $this->config,
    //           'value' => $value
    //         ]);
    //       }
    //       return null;
    //     }
    //     return $v[$this->config['index']];
    //   // }
    // } else {
    //   die("invalid transform source");
    // }
  }

  /**
   * @inheritDoc
   */
  public function getSpecification(): array
  {
    return [
      'type' => 'transform',
      'source' => $this->config['source'] == 'source' ? [ "{$this->config['source']}.{$this->config['index']}" ] : [ "{$this->config['source']}.{$this->config['field']}" ]
    ];
  }

}
