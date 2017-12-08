<?php namespace codename\core\io;

/**
 * datasource base class
 */
abstract class datasource implements \Iterator, progressInterface {

  /**
   * (re-)configure the datasource
   * @param array $config [datasource config array]
   */
  public abstract function setConfig(array $config);

}
