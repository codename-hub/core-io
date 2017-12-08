<?php
namespace codename\core\io\datasource;

use codename\core\exception;

/**
 * encapsulates a buffered value
 */
class bufferedValue {

  /**
   * @param mixed  $value
   * @param [type] $progressPosition
   */
  public function __construct(
    $value,
    $progressPosition
  ) {
    $this->value = $value;
    $this->progressPosition = $progressPosition;
  }

  /**
   * [public description]
   * @var mixed
   */
  public $value;

  /**
   * [public description]
   * @var [type]
   */
  public $progressPosition;

}

/**
 * buffered datasource
 * that encapsulates another datasource
 */
class buffered extends \codename\core\io\datasource {

  /**
   * [__construct description]
   * @param \codename\core\io\datasource $datasource [description]
   * @param int                          $bufferSize [description]
   */
  public function __construct(\codename\core\io\datasource $datasource, int $bufferSize)
  {
    if($bufferSize < 1) {
      throw new exception('EXCEPTION_DATASOURCE_BUFFERED_BUFFERSIZE_TOO_LOW', exception::$ERRORLEVEL_ERROR, $bufferSize);
    }
    $this->bufferSize = $bufferSize;
    $this->datasource = $datasource;
    $this->progressLimit = $this->datasource->currentProgressLimit();
    $this->bufferQueue = new \SplQueue();
  }

  /**
   * [getBuffer description]
   * @return \SplQueue [description]
   */
  public function getBuffer() : \SplQueue {
    return $this->bufferQueue;
  }

  /**
   * size of the buffer
   * @var int
   */
  protected $bufferSize = 1;

  /**
   * [protected description]
   * @var \codename\core\io\datasource
   */
  protected $datasource = null;

  /**
   * [protected description]
   * @var [type]
   */
  protected $progressLimit = null;

  /**
   * index
   * @var int
   */
  protected $index = -1;

  /**
   * [protected description]
   * @var \SplQueue
   */
  protected $bufferQueue = null;

  /**
   * [setBufferSize description]
   * @param int $size [description]
   */
  public function setBufferSize(int $size) {
    $this->bufferSize = $size;
  }

  /**
   * @inheritDoc
   */
  public function setConfig(array $config)
  {
    //
    // passthrough config
    //
    $this->datasource->setConfig($config);
  }

  /**
   * [fillBuffer description]
   * @return void
   */
  protected function fillBuffer() {
    for ($i=0; $i < $this->bufferSize; $i++) {
      if($this->datasource->valid()) {
        $this->bufferQueue->enqueue(
          new bufferedValue(
            $this->datasource->current(),
            $this->datasource->currentProgressPosition()
          )
        );
        $this->datasource->next();
      } else {
        break;
      }
    }
  }

  /**
   * [protected description]
   * @var bufferedValue
   */
  protected $currentValue = null;

  /**
   * @inheritDoc
   */
  public function current()
  {
    return $this->currentValue ? $this->currentValue->value : $this->currentValue;
  }

  /**
   * @inheritDoc
   */
  public function next()
  {
    if($this->bufferQueue->isEmpty()) {
      $this->fillBuffer();
    }
    if(!$this->bufferQueue->isEmpty()) {
      $this->currentValue = $this->bufferQueue->dequeue();
      $this->index++;
    } else {
      // end reached
      $this->currentValue = false;
    }
  }

  /**
   * @inheritDoc
   */
  public function key()
  {
    return $this->index;
  }

  /**
   * @inheritDoc
   */
  public function valid()
  {
    return $this->currentValue !== false; //  && $this->currentValue !== false && ($this->currentValue->value !== false) || $this->currentValue === null;
  }

  /**
   * @inheritDoc
   */
  public function currentProgressPosition() : int
  {
    return $this->currentValue ? $this->currentValue->progressPosition : 0;
  }

  /**
   * @inheritDoc
   */
  public function rewind()
  {
    $this->index = -1;
    $this->datasource->rewind();

    // empty the buffer
    while(!$this->bufferQueue->isEmpty()) {
      $this->bufferQueue->dequeue();
    }

    $this->currentValue = null;
  }

  /**
   * @inheritDoc
   */
  public function currentProgressLimit() : int
  {
    return $this->progressLimit ?? 1;
  }

}
