<?php
namespace codename\core\io\target;

use \codename\core\app;
use codename\core\exception;

/**
 * dummy target (doesnt save anything)
 */
class dummy extends \codename\core\io\target
  implements \codename\core\io\target\virtualTargetInterface {

  /**
   * [protected description]
   * @var array
   */
  protected $virtualStore = [];

  /**
   * returns data stored virtually in this instance
   * @return array [description]
   */
  public function getVirtualStoreData() : array {
    return $this->virtualStore;
  }

  /**
   * @param string  $name
   * @param array   $config
   */
  public function __construct(string $name, array $config)
  {
    parent::__construct($name, $config);
  }

  /**
   * @inheritDoc
   */
  public function setVirtualStoreEnabled(bool $state)
  {
    return;
  }

  /**
   * @inheritDoc
   */
  public function getVirtualStoreEnabled() : bool
  {
    return true;
  }

  /**
   * @inheritDoc
   */
  public function store(array $data) : bool
  {
    if($this->finished) {
      throw new exception('EXCEPTION_CORE_IO_TARGET_VIRTUAL_ALREADY_FINISHED', exception::$ERRORLEVEL_ERROR);
    }
    $this->virtualStore[] = $data;
    return true;
  }

  /**
   * determines the finished status of this target
   * @var bool
   */
  protected $finished = false;

  /**
   * @inheritDoc
   */
  public function finish()
  {
    $this->finished = true;
  }

}
