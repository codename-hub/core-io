<?php namespace codename\core\io\transform\get;

/**
 * getter for a filtered value
 */
class filtered extends \codename\core\io\transform\get {

  /**
   * @inheritDoc
   */
  public function internalTransform($value)
  {
    if(!isset($this->config['source'])) {
      echo("<pre>");
      print_r($this->config);
      echo("</pre>");
    }

    $v = $this->getValue($this->config['source'], $this->config['field'], $value);

    // apply filter
    foreach($this->config['filter'] as $filter) {
      switch ($filter['operator']) {
        case '=':
          if($filter['value'] == $v) {
            return $v;
          }
          break;
        case '!=':
          if($filter['value'] != $v) {
            return $v;
          }
          break;
        default:
          break;
      }
    }

    // no filter match
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
