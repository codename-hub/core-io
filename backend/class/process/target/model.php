<?php
namespace codename\core\io\process\target;

use codename\core\app;
use codename\core\exception;

/**
 * [model description]
 */
abstract class model extends \codename\core\io\process\target {

  /**
   * [getModel description]
   * @return \codename\core\model [description]
   */
  protected function getModel() : \codename\core\model {
    $target = $this->getTarget();
    if($target instanceof \codename\core\io\targetModelInterface) {
      return $target->getModel();
    } else {
      throw new exception('EXCEPTION_CORE_IO_PROCESS_TARGET_MODEL_UNSUPPORTED', exception::$ERRORLEVEL_FATAL, $this->config);
    }
  }
}
