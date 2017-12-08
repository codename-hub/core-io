<?php
namespace codename\core\io\target;

/**
 * defines the interface for targets
 * that internally produce string (text) result (single entry!)
 */
interface fileResultInterface {

  /**
   * returns paths to files
   * @return \codename\core\value\text\fileabsolute
   */
  function getFileResult() : \codename\core\value\text\fileabsolute;

}
