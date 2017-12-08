<?php
namespace codename\core\io\target;

/**
 * interface for targets that don't really store data
 */
interface virtualTargetInterface
{
  /**
   * returns the data stored internally
   * @return array [description]
   */
  function getVirtualStoreData() : array;

  /**
   * enables or disables storing data virtually
   * @param bool $state [description]
   */
  function setVirtualStoreEnabled(bool $state);

  /**
   * returns the current virtual store state
   * @return bool [description]
   */
  function getVirtualStoreEnabled() : bool;
}
