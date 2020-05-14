<?php
namespace codename\core\io\transform\convert;

/**
 * convert a string to another encoding
 */
class encoding extends \codename\core\io\transform\convert {

  /**
   * @inheritDoc
   */
  public function internalTransform($value)
  {
    $v = $this->getValue($this->config['source'], $this->config['field'], $value);
    // TODO: Handle required field
    // if($this->config['source'] == 'transform') {
    //   $v = $this->getTransformValue($this->config['field'], $value);
    // } else if($this->config['source'] == 'source') {
    //   if(isset($this->config['required']) && !$this->config['required'] && !isset($value[$this->config['field']])) {
    //     return null;
    //   } else {
    //     $v = $value[$this->config['field']];
    //   }
    // }
    if($v !== null) {
      return mb_convert_encoding($v, $this->config['to'], $this->config['from']);
    } else {
      return null;
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
