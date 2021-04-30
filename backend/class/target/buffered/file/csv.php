<?php
namespace codename\core\io\target\buffered\file;

use codename\core\exception;

/**
 * csv file as a target
 */
class csv extends \codename\core\io\target\buffered\file {

  /**
   * the delimiter used in the csv
   * @var string
   */
  protected $delimiter = ';';

  /**
   * the enclosure char used in the csv
   * @var string
   */
  protected $enclosure = '"';

  /**
   * the escape char used in the CSV
   * @var [type]
   */
  protected $escapeChar = '\\';

  /**
   * headed data: use mapping keys as headings
   * @var bool
   */
  protected $headed = true;

  /**
   * use serial columns/numeric indexes
   * instead of key => value based mappings
   * this needs "column" defined per mapping entry
   * @var bool
   */
  protected $numericIndexes = false;

  /**
   * [protected description]
   * @var int
   */
  protected $numericIndexStart = 0;

  /**
   * count after which we begin a new file
   * @var int
   */
  protected $splitCount = null;

  /**
   * [protected description]
   * @var [type]
   */
  protected $lineBreak = "\n";

  /**
   * @inheritDoc
   */
  public function __construct(string $name, array $config)
  {
    parent::__construct($name, $config);

    $this->delimiter = $config['delimiter'] ?? $this->delimiter;
    $this->enclosure = array_key_exists('enclosure',$config) ? $config['enclosure'] : $this->enclosure;
    $this->escapeChar = $config['escape_char'] ?? $this->escapeChar;
    $this->headed = $config['headed'] ?? $this->headed;
    $this->numericIndexes = $config['numeric_indexes'] ?? false;
    $this->numericIndexStart = $config['numeric_index_start'] ?? 0;
    $this->encoding = $config['encoding'] ?? 'UTF-8';
    $this->encodingUtf8BOM = $config['encoding_utf8bom'] ?? true;
    $this->splitCount = $config['split_count'] ?? null;
    $this->lineBreak = $config['lineBreak'] ?? $this->lineBreak;
  }

  /**
   * output encoding
   * @var string
   */
  protected $encoding = 'UTF-8';

  /**
   * Use BOM, if using encoding == UTF-8
   * @var string
   */
  protected $encodingUtf8BOM = true;

  /**
   * [UTF8_BOM description]
   * see https://stackoverflow.com/questions/5601904/encoding-a-string-as-utf-8-with-bom-in-php
   * @var string
   */
  const UTF8_BOM = "\xEF\xBB\xBF"; // chr(239) . chr(187) . chr(191);


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

      if(!$tagsChunk) {
        // fill with empty array, if not set
        $tagsChunk = array_fill(0, count($dataChunk), []);
      }

      foreach($tagsChunk as &$tagsElement) {
        // force csv extension in tag
        $tagsElement['file_extension'] = 'csv';

        if(count($dataChunks) > 1) {
          // override filename with chunk number
          if($addendum = $tagsElement['file_name_add'] ?? ('_'.($index+1))) {
            // CHANGED 2021-04-30: we now fallback to an empty string as base filename
            // if nothing provided.
            $tagsElement['file_name'] = ($tagsElement['file_name'] ?? '') . $addendum;
          }
        }
      }

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

    $keys = null;
    if($this->numericIndexes) {
      $keys = [];
      $indexCheck = $this->numericIndexStart;

      $sortedMapping = $this->getMapping();
      ksort($sortedMapping);

      //
      // Check index integrity first
      // Indexes have to be serial
      // 0, 1, 2, 3, ...
      //
      foreach($sortedMapping as $index => $map) {
        if($index == $indexCheck) {
          $indexCheck++;
        } else {
          throw new exception('EXCEPTION_CORE_IO_TARGET_NUMERIC_INDEX_INVALID', exception::$ERRORLEVEL_ERROR, [
            'index' => $indexCheck
          ]);
        }
      }

      //
      // Get the real mappings
      //
      foreach($this->getMapping() as $index => $map) {
        $keys[] = $map['column'];
      }
    } else {
      $keys = array_keys($this->getMapping());
    }

    //
    // We're relying on the fact
    // we're using UTF-8 for almost everything
    // in our system.
    //
    if($this->encoding === 'UTF-8') {
      if($this->encodingUtf8BOM) {
        //
        // write a UTF-8 BOM
        //
        fwrite($handle, self::UTF8_BOM);
      }
    } else if($this->encoding) {
      if(!in_array($this->encoding, [ 'UTF-8', 'ISO-8859-1', 'ASCII', 'Windows-1252' ])) {
        // change encoding?
        throw new exception('EXCEPTION_TARGET_BUFFERED_FILE_CSV_UNSUPPORTED_REENCODE', exception::$ERRORLEVEL_ERROR, $this->encoding);
      }
    }

    $headings = $keys;
    if($this->encoding) {
      foreach($headings as &$str) {
        $str = mb_convert_encoding($str, $this->encoding, 'UTF-8');
      }
    }
    if($this->enclosure === null) {
      fputs($handle, implode($this->delimiter, $headings).$this->lineBreak);
    } else {
      fputcsv($handle, $headings, $this->delimiter, $this->enclosure, $this->escapeChar);
    }

    if($this->numericIndexes) {
      foreach($bufferArray as $buffered) {
        $dataset = array_values($buffered);
        if($this->encoding) {
          foreach($dataset as &$str) {
            $str = mb_convert_encoding($str, $this->encoding, 'UTF-8');
          }
        }
        if($this->enclosure === null) {
          fputs($handle, implode($this->delimiter, $dataset).$this->lineBreak);
        } else {
          fputcsv($handle, $dataset, $this->delimiter, $this->enclosure, $this->escapeChar);
        }
      }
    } else {
      foreach($bufferArray as $buffered) {
        // linearize assoc array for CSV usage
        $dataset = [];
        foreach($keys as $k) {
          $dataset[] = $buffered[$k];
        }

        if($this->encoding) {
          foreach($dataset as &$str) {
            $str = mb_convert_encoding($str, $this->encoding, 'UTF-8');
          }
        }

        if($this->enclosure === null) {
          fputs($handle, implode($this->delimiter, $dataset).$this->lineBreak);
        } else {
          fputcsv($handle, $dataset, $this->delimiter, $this->enclosure, $this->escapeChar);
        }
      }
    }

    fclose($handle);
  }
}
