<?php namespace codename\core\io\transform\get;

/**
 * getter for a new array of values
 */
class valuearray extends \codename\core\io\transform\get {

  /**
   * @inheritDoc
   */
  public function internalTransform($value)
  {
    $arr = [];
    foreach($this->config['elements'] as $k => $v) {
      if(!\is_array($v)) {
        // bare value
        $arr[$k] = $v;
      } else {
        $val = $this->getValue($v['source'], $v['field'], $value);
        if(($v['required'] ?? false) && $val === null) {
          $this->errorstack->addError('GET_VALUEARRAY_MISSING_KEY', 0, [
            'config' => $this->config,
            'key' => $k,
            'value' => $value
          ]);
          continue; // ??
        } else if($val === null) {
          continue;
        }
        $arr[$k] = $val;

        // if($v['source'] == 'transform') {
        //   $arr[$k] = $this->getTransformValue($v['field'], $value);
        //   // TODO: handle required state using errorstack
        // } else if($v['source'] == 'source') {
        //   // die("error!");
        //   if(isset($v['required']) && !$this->config['required'] && !isset($value[$v['field']])) {
        //     // return null;
        //     continue; // ??
        //   } else {
        //     if(!isset($value[$v['field']])) {
        //       echo("<pre>");
        //       print_r($value);
        //       print_r($this);
        //       echo("</pre>");
        //       continue; // ??
        //     }
        //
        //     $arr[$k] = $value[$v['field']];
        //   }
        // } else {
        //   die("invalid transform source");
        // }
      }
    }
    return $arr;
  }

  /**
   * @inheritDoc
   */
  public function getSpecification(): array
  {
    $sources = [];

    foreach($this->config['elements'] as $k => $v) {
      if(!is_array($v)) {
        // BARE VALUE!
        continue;
      }

      if(is_array($v['field'])) {
        $field = implode('.', $v['field']);
      } else {
        $field = $v['field'];
      }

      $sources[$k] = "{$v['source']}.{$field}";
    }

    return [
      'type' => 'transform',
      'source' => $sources
    ];
  }

}
