<?php
namespace codename\core\io\value\text;

class tagged extends \codename\core\value\text
{
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
