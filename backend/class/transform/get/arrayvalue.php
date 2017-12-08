<?php namespace codename\core\io\transform\get;

/**
 * getter for array values (via index/key)
 */
class arrayvalue extends \codename\core\io\transform\get {

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

      if(!isset($v[$index])) {
        if(isset($this->config['required']) && $this->config['required']) {
          $this->errorstack->addError('GET_ARRAYVALUE_MISSING', 0, [
            'config' => $this->config,
            'value' => $value
          ]);
        }
        return null;
      }

      return $v[$index];
    } else {

      if(!isset($v[$this->config['index']])) {
        if(isset($this->config['required']) && $this->config['required']) {
          $this->errorstack->addError('GET_ARRAYVALUE_MISSING', 0, [
            'config' => $this->config,
            'value' => $value
          ]);
        }
        return null;
      }

      return $v[$this->config['index']];
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
