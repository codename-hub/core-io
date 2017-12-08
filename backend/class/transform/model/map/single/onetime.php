<?php
namespace codename\core\io\transform\model\map\single;

class onetime extends \codename\core\io\transform\model\map\single
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
