<?php
namespace codename\core\io\target;

/**
 * defines the interface for targets
 * that internally produce string (text) result arrays
 */
interface fileResultArrayInterface {

  /**
   * returns paths to files
   * @return \codename\core\value\text\fileabsolute[]
   */
  function getFileResultArray() : array;

}
