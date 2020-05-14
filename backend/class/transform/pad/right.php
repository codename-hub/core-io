<?php
namespace codename\core\io\transform\pad;

/**
 * pad_right
 */
class right extends \codename\core\io\transform\pad {
  /**
   * @inheritDoc
   */
  public function internalTransform($value)
  {
    $v = $this->getValue($this->config['source'] ?? 'source', $this->config['field'], $value);
    return \str_pad($v, $this->config['length'], $this->config['string'], STR_PAD_RIGHT);
  }
}
