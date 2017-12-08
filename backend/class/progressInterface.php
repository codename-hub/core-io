<?php
namespace codename\core\io;

/**
 * interface to get the current position (of whatever, e.g. an iterator or so)
 */
interface progressInterface {

  /**
   * the current position of a pointer or so
   * @return int [position relative to whatever this thing provides]
   */
  function currentProgressPosition() : int;

  /**
   * the current position of a pointer or so
   * @return int [position relative to whatever this thing provides]
   */
  function currentProgressLimit() : int;
}
