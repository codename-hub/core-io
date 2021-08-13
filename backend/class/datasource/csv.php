<?php
namespace codename\core\io\datasource;
use \codename\core\exception;

/**
 * Datasource encapsulating CSV files
 */
class csv extends \codename\core\io\datasource {

  /**
   * [__construct description]
   * @param string $filepath  path to file
   * @param array  $config   [description]
   */
  public function __construct(string $filepath, array $config = array())
  {
    $this->setConfig($config);

    if (($this->handle = @fopen($filepath, "r")) !== false)
    {
      $this->fstatSize = fstat($this->handle)['size'];

      if($this->autodetectUtf8Bom) {
        $this->handleUtf8Bom();
      }
    }
    else
    {
      error_clear_last();
      throw new exception('FILE_COULD_NOT_BE_OPEN', exception::$ERRORLEVEL_ERROR,array($filepath));
    }
  }

  /**
   * this function detects UTF8-BOM, if we're at the beginning of the file
   * and causes the datasource to skip these bytes
   * @return [type] [description]
   */
  protected function handleUtf8Bom() {
    if(ftell($this->handle) !== 0) {
      return; // skip, as we are not on pos 0 (beginning of file)
    }

    if(($header = fread($this->handle, 3)) !== false) {
      if(strcmp($header, self::UTF8_BOM) === 0) {
        // enable BOM handling
        // TODO: kill existing encoding transformations
        //
      } else {
        // rewind to start, no UTF8-BOM
        fseek($this->handle, 0);
      }
    } else {
      // nothing? error?
    }
  }

  /**
   * @inheritDoc
   */
  public function setConfig(array $config)
  {
    $this->delimiter = $config['delimiter'] ?? ',';
    $this->headed = $config['headed'] ?? true;
    $this->encoding = $config['encoding'] ?? null;
    $this->skipRows = $config['skip_rows'] ?? 0;
    $this->skipEmptyRows = $config['skip_empty_rows'] ?? false;
    $this->autodetectUtf8Bom = $config['autodetect_utf8_bom'] ?? false;
  }

  /**
   * detect and skip empty rows
   * @var bool
   */
  protected $skipEmptyRows = false;

  protected $headings = null;

  /**
   * [protected description]
   * @var bool
   */
  protected $autodetectUtf8Bom = false;

  /**
   * [UTF8_BOM description]
   * see https://stackoverflow.com/questions/5601904/encoding-a-string-as-utf-8-with-bom-in-php
   * @var string
   */
  const UTF8_BOM = "\xEF\xBB\xBF"; // chr(239) . chr(187) . chr(191);

  /**
   * returns the headings retrieved for the current file
   * @return string[] [description]
   */
  public function getHeadings() : array {
    return $this->headings;
  }

  /**
   * is true if first line of csv contains heading
   * @var bool
   */
  protected $headed = true;

  /**
   * the actuall postition
   * @var int
   */
  protected $index;

  /**
   * [protected description]
   * @var mixed
   */
  protected $handle;

  /**
   * current array item itself
   * @var array
   */
  protected $current;

  /**
   * [protected description]
   * @var [type]
   */
  public $delimiter = ';';

  /**
   * [public description]
   * @var array
   */
  public $encoding = null;

  /**
   * @inheritDoc
   */
  public function current()
  {
    return $this->current;
  }

  /**
   * @inheritDoc
   */
  public function next()
  {
      $current = fgetcsv($this->handle, 0, $this->delimiter);

      if($this->skipRows > 0 && $this->key() === 0)  {
        for ($i=0; $i < $this->skipRows; $i++) {
          $current = fgetcsv($this->handle, 0, $this->delimiter);
        }
      }

      // update ftell position
      // $this->ftellPosition = ftell($this->handle);

      // we have to stop next from running
      // if fgetcsv returns false.
      if($current === FALSE) {
        $this->current = false;
        return;
      }

      if (!is_null($this->headings))
      {
        if($this->skipEmptyRows) {
          // check for "all-empty"-cells
          // and loop forward until we reach another vital entry or the real end
          while(count(array_filter($current)) === 0) {
            $current = fgetcsv($this->handle, 0, $this->delimiter);
            if($current === FALSE) {
              $this->current = false;
              return;
            }
          }
        }

        $i = 0;
        $this->current = array();
        foreach($this->headings as $head)
        {
          if($this->encoding) {
            $this->current[$head] = mb_convert_encoding($current[$i], $this->encoding['to'], $this->encoding['from']);
          } else {
            $this->current[$head] = $current[$i];
          }
          $i++;
        }
      } else {
        if($this->encoding) {
          $this->current = array_map(function($item) {
            return mb_convert_encoding($item, $this->encoding['to'], $this->encoding['from']);
          }, $current);
        } else {
          $this->current = $current;
        }
      }

      // echo "current: ".chr(10);
      // print_r($this->current);
      //$this->current = fgetcsv($this->handle, 0, ",");

      if ($this->valid())
      {
          if ($this->key() == 0 && $this->headed)
          {
              //read in heading
              $this->headings =  $this->current();

              //
              // NOTE: decoding is performed implicitly, above.
              // No need for double-decoding, this corrupts data.
              //
              // if($this->encoding) {
              //   $this->headings = array_map(function($item) {
              //     return mb_convert_encoding($item, $this->encoding['to'], $this->encoding['from']);
              //   }, $this->headings);
              // }

              $this->index++;
              return $this->next();
          }
          $this->index++;
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
    return ($this->current !== FALSE);
  }

  /**
   * @inheritDoc
   */
  public function rewind()
  {
    fseek($this->handle, 0);
    if($this->autodetectUtf8Bom) {
      $this->handleUtf8Bom();
    }
    $this->index = 0;
    $this->next();
  }

  protected $ftellPosition = 0;

  /**
   * @inheritDoc
   */
  public function currentProgressPosition(): int
  {
    return ftell($this->handle);
    // return $this->ftellPosition;
  }

  /**
   * [protected description]
   * @var int
   */
  protected $fstatSize = 0;

  /**
   * @inheritDoc
   */
  public function currentProgressLimit(): int
  {
    return $this->fstatSize;
  }

}
