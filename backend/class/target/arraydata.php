<?php
namespace codename\core\io\target;

use codename\core\exception;

/**
 * pure array data as target
 */
class arraydata extends \codename\core\io\target {

  /**
   * [__construct description]
   * @param string $name   [description]
   * @param array  $config [description]
   */
  public function __construct(string $name, array $config)
  {
    parent::__construct($name, $config);
  }

  /**
   * data storage
   * @var array
   */
  protected $virtualStore = [];

  /**
   * @inheritDoc
   */
  public function store(array $data) : bool
  {
    $this->virtualStore[] = $data;
    return true;
  }

  /**
   * returns data stored virtually in this instance
   * @return array [description]
   */
  public function getVirtualStoreData() : array {
    return $this->virtualStore;
  }
  
  /**
   * @inheritDoc
   */
  public function finish()
  {
    // nothing. lock storing?
  }


}
