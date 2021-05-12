<?php
namespace codename\core\io\target\buffered;

use \codename\core\app;
use codename\core\exception;

use codename\core\io\target\fileResultInterface;
use codename\core\io\target\fileResultArrayInterface;

/**
 * file as a target
 */
abstract class file extends \codename\core\io\target\buffered
  implements fileResultArrayInterface {
  use \codename\core\io\target\createArchiveTrait;

  /**
   * output filename
   * @var string
   */
  protected $targetFilePath = null;

  /**
   * [protected description]
   * @var bool
   */
  protected $append = false;

  /**
   * count after which we begin a new file
   * @var int
   */
  protected $splitCount = null;

  /**
   * @inheritDoc
   */
  public function __construct(string $name, array $config)
  {
    parent::__construct($name, $config);
    $this->targetFilePath = $config['filepath'] ?? null;
    $this->append = $config['append'] ?? false;
    $this->splitCount = $config['split_count'] ?? null;
  }

  /**
   * legacy method returning a new file handle using the currently set targetFilePath
   * @deprecated
   * @return resource
   */
  protected function getFileHandle() {
    if(!$this->targetFilePath) {
      $this->targetFilePath = $this->getNewFilePath();
    }
    return $this->getNewFileHandle($this->targetFilePath);
  }

  /**
   * returns a new, temporary file path
   * @return [type]                 [description]
   */
  protected function getNewFilePath() {
    return tempnam(sys_get_temp_dir(), '_bf_');
  }

  /**
   * returns a new file handle for the given filepath
   * to be opened in write or append mode
   * @param  string $targetFilePath [description]
   * @return resource               [a file handle resource]
   */
  protected function getNewFileHandle(string $targetFilePath) {
    $handle = @fopen($targetFilePath, ($this->append ? 'a+' : 'w+'));
    if($handle === false) {
      throw new exception('CORE_IO_TARGET_BUFFERED_FILE_HANDLE_ERROR', exception::$ERRORLEVEL_ERROR, $targetFilePath);
    }
    return $handle;
  }

  /**
   * [protected description]
   * @var \codename\core\value\text\fileabsolute[]
   */
  protected $fileResults = null;

  /**
   * @inheritDoc
   */
  public function getFileResultArray() : array
  {
    if($this->fileResults) {
      if($this->config['config']['archive'] ?? false) {
        return $this->archiveResults ?? $this->archiveResults = $this->createArchive();
      } else {
        return $this->fileResults;
      }
    } else {
      // invalid, exception!
    }
    return [];
    // throw new \LogicException('Not implemented'); // TODO
  }

}
