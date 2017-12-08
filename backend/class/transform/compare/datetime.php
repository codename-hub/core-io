<?php
namespace codename\core\io\transform\compare;

/**
 * datetime comparison
 * which outputs 1, 0, -1 or NULL
 */
class datetime extends \codename\core\io\transform {

  /**
   * @inheritDoc
   */
  public function internalTransform($value)
  {
    $leftConfig = $this->config['left'];
    $rightConfig = $this->config['right'];

    $leftValue = $this->getValue($leftConfig['source'], $leftConfig['field'], $value);
    $rightValue = $this->getValue($rightConfig['source'], $rightConfig['field'], $value);

    $left = \DateTime::createFromFormat($leftConfig['source_format'] ?? $this->config['source_format'], $leftValue);
    $right = \DateTime::createFromFormat($rightConfig['source_format'] ?? $this->config['source_format'], $rightValue);

    if($leftConfig['modify'] ?? false) {
      $left->modify($leftConfig['modify']);
    }
    if($rightConfig['modify'] ?? false) {
      $right->modify($rightConfig['modify']);
    }

    if ($this->config['set_time_to_null'] ?? false) {
      $left->setTime(0,0);
      $right->setTime(0,0);
    }

    /**
     * results like bccomp
     * @link http://php.net/manual/de/function.bccomp.php
     */
    if($left == $right) {
      return 0;
    } else if($left < $right) {
      return -1;
    } else {
      return 1;
    }
  }

  /**
   * @inheritDoc
   */
  public function getSpecification(): array
  {
    return [
      'type' => 'transform',
      // TODO: implement transform as a source!
      'source' => [ "TODO_SPEC" ]
    ];
  }

}
