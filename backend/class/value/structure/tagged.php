<?php
namespace codename\core\io\value\structure;

class tagged extends \codename\core\value\structure
{
  /**
   * This validator is used to validate the value on generation.
   * @var string
   */
  protected $validator = 'structure';

  /**
   * [protected description]
   * @var array|null
   */
  protected $tags = null;

  /**
   * @inheritDoc
   */
  public function __construct($value, ?array $tags = null)
  {
    parent::__construct($value);
    $this->tags = $tags;
  }

  /**
   * [getTags description]
   * @return array|null
   */
  public function getTags() : ?array {
    return $this->tags;
  }
}
