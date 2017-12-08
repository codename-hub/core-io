<?php namespace codename\core\io\transform\get;

/**
 * getter for array_column (PHP) values (via index/key)
 */
class arraycolumn extends \codename\core\io\transform\get {

  /**
   * @inheritDoc
   */
  public function internalTransform($value)
  {
    if($this->config['source'] == 'source') {
      // special case where we need to fetch a complete array
      // and access only an index later on
      $v = isset($this->config['field']) ? $value[$this->config['field']] : $value;
    } else {
      $v = $this->getValue($this->config['source'], $this->config['field'], $value);
    }

    if(is_array($this->config['index'])) {
      // dynamic index
      $index = $this->getValue($this->config['index']['source'], $this->config['index']['field'], $value);
      return array_column($v, $index); // TODO: we might use array_values additionally
    } else {
      return array_column($v, $this->config['index']); // TODO: we might use array_values additionally
    }
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
