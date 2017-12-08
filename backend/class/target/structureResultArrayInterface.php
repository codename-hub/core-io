<?php
namespace codename\core\io\target;

/**
 * defines the interface for targets
 * that internally produce string (text) result arrays
 */
interface structureResultArrayInterface {

  /**
   * returns paths to files
   * @return \codename\core\value\structure[]
   */
  function getStructureResultArray() : array;

}
