<?php
namespace codename\core\io\transform\trim;

class right extends \codename\core\io\transform\trim
{
  /**
   * @inheritDoc
   */
  public function internalTransform($value)
  {
    $v = $this->getValue($this->config['source'], $this->config['field'], $value);
    // TODO: handle errors / required state

    if($this->characterMask === null) {
      return rtrim($v);
    } else {
      return rtrim($v, $this->characterMask);
    }
  }
}
