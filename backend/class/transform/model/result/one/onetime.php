<?php
namespace codename\core\io\transform\model\result\one;

/**
 * performs a model_result_one for one time
 * for a whole import loop
 */
class onetime extends \codename\core\io\transform\model\result\one {

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
