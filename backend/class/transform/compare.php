<?php
namespace codename\core\io\transform;

/**
 * base class for comparisons ( ==, !=, >=, >, <, <= )
 */
abstract class compare extends \codename\core\io\transform {

  /**
   * the field to compare
   * @var [type]
   */
  protected $field;

  /**
   * the value to compare to
   * @var [type]
   */
  protected $value;

  /**
   * @inheritDoc
   */
  public function __construct(array $config)
  {
    parent::__construct($config);
    $this->field = $config['field'];
    $this->value = $config['value'];
  }

}