<?php
namespace codename\core\io\target;

use \codename\core\app;
use codename\core\exception;

/**
 * buffer as a target
 */
abstract class buffered extends \codename\core\io\target
  implements \codename\core\io\targetStoreTagInterface {

  /**
   * buffered entries
   * @var array
   */
  protected $bufferArray = [];

  /**
   * [protected description]
   * @var array
   */
  protected $tagsArray = [];

  /**
   * @inheritDoc
   */
  public function store(array $data, ?array $tags = null) : bool
  {
    if($this->finished) {
      throw new exception('EXCEPTION_CORE_IO_TARGET_BUFFERED_ALREADY_FINISHED', exception::$ERRORLEVEL_ERROR);
    }
    $this->bufferArray[] = $data;
    $this->tagsArray[] = $tags;
    return true;
  }

  /**
   * perform the real store routine
   * -> store the buffered data
   *
   * @return void
   */
  protected abstract function storeBufferedData();

  /**
   * [protected description]
   * @var bool
   */
  protected $finished = false;

  /**
   * @inheritDoc
   */
  public function finish()
  {
    $this->finished = true;
    $this->storeBufferedData();

    \codename\core\app::getResponse()->setData('buffered_tags_debug', $this->tagsArray);
  }

}
