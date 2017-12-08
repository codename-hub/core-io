<?php
namespace codename\core\io\target;

/**
 * defines the interface for targets
 * that internally produce string (text) result arrays
 */
interface textResultArrayInterface {

  /**
   * [getTextResult description]
   * @return \codename\core\value\text[]
   */
  function getTextResultArray() : array;

}
