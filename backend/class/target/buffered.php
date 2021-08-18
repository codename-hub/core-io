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
  public function __construct(string $name, array $config)
  {
    parent::__construct($name, $config);
    $this->bufferSize = $this->config['buffer_size'] ?? null;
  }

  /**
   * buffer size until forced writeout, if SupportsPartialWriteout = true
   * and if bufferSize != null OR > 0
   * @var int|null
   */
  protected $bufferSize = null;

  /**
   * Current count of entries in buffer
   * @var int
   */
  protected $currentStoredCount = 0;

  /**
   * Whether this target supports partial write-outs
   * (buffer flushing)
   * This constant must be overridden in order to use it.
   * @var bool
   */
  const SupportsPartialWriteout = false;

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

    $this->currentStoredCount++;

    //
    // Buffer flush, depending on current size
    //
    if(static::SupportsPartialWriteout && $this->bufferSize && (($this->currentStoredCount % $this->bufferSize) === 0)) {
      // forced write-out
      $this->storeBufferedData();
      $this->bufferArray = []; // clear buffer array to release memory
    }
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
    if(!$this->finished) {
      $this->finished = true;
      $this->storeBufferedData();
    }

    // \codename\core\app::getResponse()->setData('buffered_tags_debug', $this->tagsArray);
  }

}
