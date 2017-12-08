<?php
namespace codename\core\io\target;

/**
 * defines the interface for targets
 * that internally produce string (text) result (single entry!)
 */
interface textResultInterface {

  /**
   * [getTextResult description]
   * @return \codename\core\value\text
   */
  function getTextResult() : \codename\core\value\text;

}
