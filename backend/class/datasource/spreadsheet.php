<?php
namespace codename\core\io\datasource;

use codename\core\exception;

/**
 * [spreadsheet description]
 */
class spreadsheet extends \codename\core\io\datasource {

  /**
   * [protected description]
   * @var \PhpOffice\PhpSpreadsheet\Reader\IReader
   */
  protected $reader = null;

  /**
   * [protected description]
   * @var string
   */
  protected $filename = null;

  /**
   * true if the given sheet(s) are headed
   * @var bool
   */
  protected $headed = true;

  /**
   * [protected description]
   * @var bool
   */
  protected $includeSpreadsheetColumns = false;

  /**
   * [protected description]
   * @var \PhpOffice\PhpSpreadsheet\Spreadsheet
   */
  protected $sheet = null;

  /**
   * [protected description]
   * @var \PhpOffice\PhpSpreadsheet\Spreadsheet
   */
  protected $activeSheet = null;

  /**
   * [protected description]
   * @var \PhpOffice\PhpSpreadsheet\Worksheet\RowIterator
   */
  protected $activeSheetRowIterator = null;

  /**
   * [protected description]
   * @var [type]
   */
  protected $includeEmptyRows = false;

  /**
   * [__construct description]
   * @param string  $file                      [description]
   * @param bool $headed                    [description]
   * @param bool $includeSpreadsheetColumns [description]
   */

  /**
   * [__construct description]
   * @param string $file   [description]
   * @param array  $config [description]
   */
  public function __construct(string $file = '', array $config = array() /* bool $headed = true, bool $includeSpreadsheetColumns = false*/ )
  {
    $this->setConfig($config);

    $this->filename = $file;

    set_time_limit(0);

    // Increase memory limit to heavenly heights.
    ini_set('memory_limit', '2048M');

    // use \DateTime Objects by default
    \PhpOffice\PhpSpreadsheet\Calculation\Functions::setReturnDateType(\PhpOffice\PhpSpreadsheet\Calculation\Functions::RETURNDATE_PHP_OBJECT);

    // this automatically creates the matching reader for the file.
    $this->reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReaderForFile($this->filename);

    // skip empty cells
    $this->reader->setReadEmptyCells(false);

    $this->sheet = $this->reader->load($this->filename);

    if($this->sheet->getSheetCount() > 0) {
      if($this->customSheetIndex) {
        // custom sheet index
        $this->customSheetIndex = intval($this->customSheetIndex);
      } else {
        // fallback to first sheet
        $this->customSheetIndex = 0;
      }
      $this->activeSheet = $this->sheet->getSheet($this->customSheetIndex);
      $this->activeSheetRowIterator = $this->activeSheet->getRowIterator();
    }



    // count of sheets: $this->reader->getSheetCount()
    // get/activate a specific sheet: $this->reader->getSheet($sheetIndex);
    // alternative: $this->reader->getSheetByName('some-string');

    // $this->activeSheetRowIterator = $this->reader->getActiveSheet()->getRowIterator();

    // if ... $this->reader->canRead($this->filename)
  }

  /**
   * specific sheet index to use
   * @var int|string|null
   */
  protected $customSheetIndex = null;

  /**
   * Let the reader read across multiple sheets
   * as one continuous dataset
   * @var bool
   */
  protected $multisheet = false;

  /**
   * skip until this row number
   * @var int
   */
  protected $skipRows = 0;

  /**
   * number of header row
   * @var int
   */
  protected $headerRow = 1;

  /**
   * @inheritDoc
   */
  public function setConfig(array $config)
  {
    $this->headed = $config['headed'] ?? true;
    $this->includeSpreadsheetColumns = $config['include_spreadsheet_columns'] ?? false;
    $this->skipRows = $config['skip_rows'] ?? 0;
    $this->headerRow = $config['header_row'] ?? 1;
    $this->customSheetIndex = $config['custom_sheet_index'] ?? null;
    $this->multisheet = $config['multisheet'] ?? false;
  }


  /**
   * @inheritDoc
   */
  public function current()
  {
    return $this->currentValue;
  }

  /**
   * [protected description]
   * @var mixed
   */
  protected $currentValue = null;

  /**
   * [protected description]
   * @var array
   */
  protected $currentColumnMapping = [];

  /**
   * @inheritDoc
   */
  public function next()
  {
    // reset current value
    $this->currentValue = null;

    if($this->skipRows) {
      while($this->activeSheetRowIterator->key() < ($this->skipRows)) {
        $this->activeSheetRowIterator->next();
      }
    }

    if($this->headed) {
      while($this->activeSheetRowIterator->key() < ($this->headerRow)) {
        $this->activeSheetRowIterator->next();
      }
    }

    // NOTE: RowIterator starts its index at 1
    // Therefore, we are getting the headed data columns at index 2
    if($this->headed && $this->activeSheetRowIterator->valid() && $this->activeSheetRowIterator->key() === ($this->headerRow)) {

      // use first row in each sheet for mapping the values
      // get current row.
      $row = $this->activeSheetRowIterator->current();

      // iterate over cells.
      $cellIterator = $row->getCellIterator();

      // reset old column mapping
      $this->currentColumnMapping = [];

      // create a new column mapping
      foreach($cellIterator as $cell) {
        $cellValue = $cell->getValue();
        if(!empty($cellValue)) {
          $this->currentColumnMapping[$cell->getColumn()] = $cellValue;
        }
      }
    }

    $this->activeSheetRowIterator->next();

    if($this->activeSheetRowIterator->valid()) {

      // get current row.
      $row = $this->activeSheetRowIterator->current();

      // iterate over cells.
      $cellIterator = $row->getCellIterator();

      $newCurrentValue = [];
      $valueCount = 0;

      foreach($cellIterator as $cell) {
        // we may distinguish formulas from formatted datetime values or others.
        // $cellValue = $cell->getFormattedValue(); // $cell->isFormula() || $cell->getDataType == \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE ? $cell->getCalculatedValue() : $cell->getFormattedValue();

        $cellValue = null;
        if($cell instanceof \PhpOffice\PhpSpreadsheet\Cell\Cell) {
          if($cell->isFormula()) {
            $cellValue = $cell->getCalculatedValue();
          } else {
            // $cellValue = $cell->getValue();
            $cellValue = $cell->getFormattedValue();
          }
        }

        if(!empty($cellValue)) {
          $valueCount++;
        }

        $cellColumn = $cell->getColumn();

        // check for a mapping match:
        if($this->headed && isset($this->currentColumnMapping[$cellColumn])) {
          $newCurrentValue[$this->currentColumnMapping[$cellColumn]] = $cellValue;
        } else {
          // Skip empty values that are not mapped in any way
          // if(!empty($cellValue)) {
          // CHANGED 2021-04-29: handling of empty-row-skipping done below
          // otherwise, we can't handle empty cells/null values
          $newCurrentValue[$cellColumn] = $cellValue;
          // }
        }
      }

      if(!$this->includeEmptyRows && $valueCount === 0) {
        // if we have no values in this row (valueCount === 0)
        // and we want to skip empty rows, simply move on to next row.
        $this->next();
      } else {
        $this->globalRowIndex++;
        $this->currentValue = $newCurrentValue;
      }

    } else {

      // we may go to the next sheet
      // if there's at least one more
      if($this->multisheet) {
        if($this->sheetIndex < ($this->sheet->getSheetCount()-1)) {
          $this->sheetIndex++;
          $this->activeSheet = $this->sheet->getSheet($this->sheetIndex);
          $this->activeSheetRowIterator = $this->activeSheet->getRowIterator();
          $this->next();
        }
      }
    }
  }

  /**
   * [protected description]
   * @var [type]
   */
  protected $sheetIndex = 0;

  /**
   * [protected description]
   * @var [type]
   */
  protected $globalRowIndex = 0;

  /**
   * @inheritDoc
   */
  public function key()
  {
    return $this->globalRowIndex;
  }

  /**
   * @inheritDoc
   */
  public function valid()
  {
    // is this check enough?
    return $this->currentValue != null && $this->activeSheetRowIterator->valid();
  }

  /**
   * @inheritDoc
   */
  public function rewind()
  {
    $this->currentValue = null;
    $this->sheetIndex = $this->customSheetIndex ?? 0;
    $this->activeSheet = $this->sheet->getSheet($this->sheetIndex);
    $this->activeSheetRowIterator = $this->activeSheet->getRowIterator();
    $this->globalRowIndex = 0;
    $this->next();
  }

  /**
   * @inheritDoc
   */
  public function currentProgressPosition() : int
  {
    return $this->globalRowIndex;
  }

  /**
   * @inheritDoc
   */
  public function currentProgressLimit() : int
  {
    return 0;
  }


}
