<?php
namespace codename\core\io\transform\convert;

class json extends \codename\core\io\transform\convert
{
  /**
   * @inheritDoc
   */
  public function internalTransform($value)
  {
    $v = $this->getValue($this->config['source'], $this->config['field'], $value);
    if($v !== null) {
      if($this->config['mode'] === 'encode') {
        return json_encode($v);
      } else if($this->config['mode'] === 'decode') {
        return json_decode($v, true);
      } else {
        // error
      }
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
