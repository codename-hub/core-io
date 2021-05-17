<?php
namespace codename\core\io\target\buffered\file;

use codename\core\exception;

/**
 * raw file as a target
 */
class raw extends \codename\core\io\target\buffered\file {

  /**
   * determines if the raw exporter uses a multiline per entry
   * @var bool
   */
  protected $multiline = false;

  /**
   * structure helper
   * @var array
   */
  protected $structure = [];

  /**
   * padding string to be used
   * @var string
   */
  protected $paddingString = '';

  /**
   * padding mode to be used (left or right)
   * @var string
   */
  protected $paddingMode = STR_PAD_RIGHT;

  /**
   * true: auto-truncate too long values
   * false: throw an error on overflow
   * @var bool
   */
  protected $truncate = false;

  /**
   * output encoding to use
   * @var string
   */
  protected $encoding = 'ASCII';

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

    $this->paddingString = $config['padding_string'];
    $this->truncate = $config['truncate'];
    $this->encoding = $config['encoding'] ?? null;

    if($config['padding_mode'] == 'left') {
      $this->paddingMode = STR_PAD_LEFT;
    } else if($config['padding_mode'] == 'right') {
      $this->paddingMode = STR_PAD_RIGHT;
    } else {
      // ERROR
      throw new exception('EXCEPTION_TARGET_BUFFERED_FILE_RAW_PADDING_MODE_NOT_SUPPORTED', exception::$ERRORLEVEL_ERROR);
    }

    $mapping = $this->getMapping();

    foreach($mapping as $index => $map) {
      $this->structure[$map['rowIndex']][$map['columnIndex']] = $index;
    }

    $this->splitCount = $config['split_count'] ?? null;
  }

  /**
   * @inheritDoc
   */
  protected function storeBufferedData()
  {
    // split the array

    $dataChunks = [];
    $tagsChunks = [];

    if($this->splitCount && (count($this->bufferArray) > $this->splitCount)) {
      // we have to split at least one time
      $dataChunks = array_chunk($this->bufferArray, $this->splitCount);
      $tagsChunks = array_chunk($this->tagsArray, $this->splitCount);

    } else {
      $dataChunks = [ $this->bufferArray ];
      $tagsChunks = [ $this->tagsArray ];
    }

    $resultObjects = [];

    foreach ($dataChunks as $index => $dataChunk) {

      // skip empty chunks
      if(count($dataChunk) === 0) {
        continue;
      }

      $tagsChunk = $tagsChunks[$index];

      // create a new file handle?
      $handle = null;

      $path = $this->getNewFilePath();
      $handle = $this->getNewFileHandle($path);

      $this->internalStoreBufferedData($handle, $dataChunk);

      if($tagsChunk) {
        $resultObjects[] = new \codename\core\io\value\text\fileabsolute\tagged($path, $tagsChunk);
      } else {
        $resultObjects[] = new \codename\core\value\text\fileabsolute($path, $tagsChunk);
      }
    }

    $this->fileResults = $resultObjects;
  }

  /**
   * @inheritDoc
   */
  protected function internalStoreBufferedData($handle, $bufferArray)
  {
    // $handle = $this->getFileHandle();

    $mapping = $this->getMapping();

    foreach($bufferArray as $buffered) {
      $data = [];
      foreach ($this->structure as $rowIndex => $rowConfig) {
        $data[$rowIndex] = [];
        foreach($rowConfig as $columnIndex => $mappingKey) {
          // $value = $value === null ? '' : $value; ??
          $value = $buffered[$mappingKey];
          $length = $mapping[$mappingKey]['length'];
          // TODO:
          // please change if there is an official version of php lib
          // https://bugs.php.net/bug.php?id=21317
          $diff = strlen($value) - mb_strlen($value, 'UTF-8');
          $value = str_pad($value, $length + $diff, $this->paddingString, $this->paddingMode);
          if($this->truncate) {
            $value = mb_substr($value, 0, $length, 'UTF-8');
          } else {
            if(mb_strlen($value, 'UTF-8') > $length) {
              throw new exception('EXCEPTION_TARGET_BUFFERED_FILE_RAW_VALUE_TOO_LONG', exception::$ERRORLEVEL_ERROR, $value);
            }
          }
          $data[$rowIndex][$columnIndex] = $value;
        }
        // Add line terminator?
      }

      $rows = [];
      foreach($data as $columns) {
        $rows[] = implode('', $columns);
      }

      $entry = implode(chr(10), $rows);

      // TODO: add line/final terminating CRLF?
      $entry .= chr(10);

      if($this->encoding) {
        $entry = mb_convert_encoding($entry, $this->encoding, 'UTF-8');
      }
      $success = fputs($handle, $entry);
    }

    fclose($handle);
  }

}
