<?php
namespace codename\core\io\transform\model\result\all;

class onetime extends \codename\core\io\transform\model\result\all
{
  /**
   * override resetCache
   * to prevent cache reset
   * and keep the cached value
   * until destroyed
   *
   * @inheritDoc
   */
  public function resetCache()
  {
  }

  /**
   * @inheritDoc
   */
  public function resetErrors()
  {
  }
}
