<?php
namespace codename\core\io\datasource;

use codename\core\exception;

use jocoon\parquet\ParquetReader;

class parquet extends \codename\core\io\datasource
{
  /**
   * [protected description]
   * @var resource
   */
  protected $handle;

  /**
   * [protected description]
   * @var ParquetReader
   */
  protected $reader;

  /**
   * [__construct description]
   * @param string $filepath  path to file
   * @param array  $config   [description]
   */
  public function __construct(string $filepath, array $config = array())
  {
    $this->setConfig($config);

    if (($this->handle = fopen($filepath, "r")) !== false)
    {
      $this->initParquetReader();
    }
    else
    {
      throw new exception('FILE_COULD_NOT_BE_OPENED', exception::$ERRORLEVEL_ERROR,array($filepath));
    }
  }

  /**
   * @inheritDoc
   */
  public function setConfig(array $config)
  {

  }

  /**
   * [initParquetReader description]
   */
  protected function initParquetReader(): void {
    // TODO: ParquetOptions
    $this->reader = new ParquetReader($this->handle);
  }

  /**
   * [protected description]
   * @var int
   */
  protected $currentRowGroupIndex = 0;

  /**
   * [protected description]
   * @var array|null
   */
  protected $currentRowGroupData = null;

  /**
   * [protected description]
   * @var int
   */
  protected $currentRowCount = null;

  /**
   * [protected description]
   * @var int
   */
  protected $currentIndex = null;

  /**
   * [read description]
   * @return bool
   */
  protected function read(): bool {

    if($this->currentRowGroupData === null) {
      // nothing to check, unread data
    } else {
      // check current read state
      // or simply increment by 1 ?
      $this->currentRowGroupIndex++;
    }

    if($this->reader->getRowGroupCount() <= $this->currentRowGroupIndex) {
      return false; // finished reading
    }

    $dataFields = $this->reader->schema->getDataFields();
    $rg = $this->reader->OpenRowGroupReader($this->currentRowGroupIndex);

    $this->currentRowCount = $rg->getRowCount();
    $this->currentIndex = 0;
    $this->currentRowGroupData = [];

    foreach($dataFields as $field) {
      $values = $rg->ReadColumn($field)->getData();
      foreach($values as $index => $value) {
        $this->currentRowGroupData[$index][$field->name] = $value;
      }
    }

    return true;
  }

  /**
   * @inheritDoc
   */
  public function current()
  {
    return $this->currentRowGroupData[$this->currentIndex];
  }

  /**
   * @inheritDoc
   */
  public function next()
  {
    if($this->currentIndex === null || $this->currentIndex > $this->currentRowCount) {
      // trigger read on first try or when required to advance to the next row group
      if(!$this->read()) {
        return;
      }

      $this->currentIndex = 0;
      $this->overallKey = 0;
      return;
    }
    $this->currentIndex++;
    $this->overallKey++;
  }

  /**
  * [protected description]
  * @var int
  */
  protected $overallKey = 0;

  /**
   * @inheritDoc
   */
  public function key()
  {
    return $this->overallKey;
  }

  /**
   * @inheritDoc
   */
  public function valid()
  {
    return $this->currentRowGroupIndex < $this->reader->getRowGroupCount() && $this->overallKey < $this->reader->getThriftMetadata()->num_rows;
  }

  /**
   * @inheritDoc
   */
  public function rewind()
  {
    $this->overallKey = 0;
    $this->currentIndex = null;
    $this->currentRowGroupIndex = 0;
    $this->currentRowGroupData = null;
    $this->currentRowCount = null;
    $this->next();
  }

  /**
   * @inheritDoc
   */
  public function currentProgressPosition(): int
  {
    return $this->overallKey;
  }

  /**
   * @inheritDoc
   */
  public function currentProgressLimit(): int
  {
    return $this->reader->getThriftMetadata()->num_rows;
  }

}
