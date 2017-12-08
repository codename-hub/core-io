<?php namespace codename\core\io\transform\get;

/**
 * getter for fallback values
 */
class fallback extends \codename\core\io\transform\get {

  /**
   * @inheritDoc
   */
  public function internalTransform($value)
  {
    foreach($this->config['fallback'] as $fallback) {
      $v = $this->getValue($fallback['source'], $fallback['field'], $value);
      if($v !== null) {
        return $v;
      }
      // if($fallback['source'] == 'source') {
      //   if(isset($value[$fallback['field']]) && $value[$fallback['field']] != null) {
      //     return $value[$fallback['field']];
      //   }
      // } else if($fallback['source'] == 'transform') {
      //   $v = $this->getTransformValue($fallback['field'], $value);
      //   if($v != null) {
      //     return $v;
      //   }
      // } else {
      //   die("unsupported source type");
      // }
    }
    if(isset($this->config['required']) && $this->config['required']) {
      $this->errorstack->addError('VALUE_NULL', 0, [
        'config' => $this->config,
        'value' => $value
      ]);
    }
    return null;
  }

  /**
   * @inheritDoc
   */
  public function getSpecification(): array
  {
    $sources = [];

    foreach($this->config['fallback'] as $k => $v) {
      $field = is_array($v['field']) ? implode('.', $v['field']) : $v['field'];
      $sources[] = "{$v['source']}.{$field}";
    }

    return [
      'type' => 'transform',
      'source' => $sources
    ];
  }

}
