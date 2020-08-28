<?php
namespace codename\core\io\target\buffered\file;

use codename\core\app;
use codename\core\exception;

/**
 * csv file as a target
 */
class spreadsheet extends \codename\core\io\target\buffered\file {

  /**
   * [protected description]
   * @var [type]
   */
  protected $use_template_file = null;

  /**
   * [protected description]
   * @var [type]
   */
  protected $filepath = null;

  /**
   * [protected description]
   * @var [type]
   */
  protected $use_writer = null;

  /**
   * [protected description]
   * @var [type]
   */
  protected $freeze = null;

  /**
   * [protected description]
   * @var [type]
   */
  protected $key_row = null;

  /**
   * [protected description]
   * @var [type]
   */
  protected $start_row = 1;

  /**
   * [protected description]
   * @var [type]
   */
  protected $sheet = 0;

  /**
   * use serial columns/numeric indexes
   * instead of key => value based mappings
   * this needs "column" defined per mapping entry
   * @var bool
   */
  protected $numericIndexes = false;

  /**
   * The current environment type configuration does not contain the desired key for the type.
   * <br />May occur when you use multiple mail configurators.
   * @var string
   */
  CONST EXCEPTION_STOREBUFFERDDATA_COLUMNOTFOUND = 'EXCEPTION_STOREBUFFERDDATA_COLUMNOTFOUND';

  /**
   * @inheritDoc
   */
  public function __construct(string $name, array $config)
  {
    parent::__construct($name, $config);

    $this->use_template_file = $this->config['use_template_file'] ?? null;
    if (!is_null($this->use_template_file)) {
      $this->filepath = app::getInheritedPath($this->use_template_file);
    }

    $this->use_writer = $this->config['use_writer'] ?? null;
    $this->freeze = $this->config['freeze'] ?? null;
    $this->key_row = $this->config['key_row'] ?? null;
    $this->start_row = $this->config['start_row'] ?? 1;
    $this->sheet = $this->config['sheet'] ?? 0;
    $this->numericIndexes = $config['numeric_indexes'] ?? false;
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

      $this->internalStoreBufferedData($path, $dataChunk, $tagsChunk);

      if(!$tagsChunk) {
        // fill with empty array, if not set
        $tagsChunk = array_fill(0, count($dataChunk), []);
      }
      foreach($tagsChunk as &$tagsElement) {
        // force csv extension in tag

        $extension = null;
        if($this->use_writer == 'Xlsx') {
          $extension = 'xlsx';
        } else if($this->use_writer == 'Xls') {
          $extension = 'xls';
        } else if($this->use_writer == 'Csv') {
          $extension = 'csv';
        } else {
          throw new exception('INVALID_SPREADSHEET_FILE_FORMAT_SPECIFIED', exception::$ERRORLEVEL_ERROR, $this->use_writer);
        }

        $tagsElement['file_extension'] = $extension;
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
  protected function internalStoreBufferedData($path, $bufferArray, $tagsChunk = null)
  {
    // this automatically creates the matching reader for the file.
    if ($this->filepath) {
      $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReaderForFile($this->filepath);
      $spreadsheet = $reader->load($this->filepath);
    } else {
      $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
    }
    $worksheet = $spreadsheet->setActiveSheetIndex($this->sheet);

    if($this->freeze) {
      $worksheet->freezePane($this->freeze);
    }

    $mapping = $this->getMapping();
    $columnIndexMap = [];

    // Excel Column Index starts at 1, not 0
    $columnIndex = 1;

    foreach ($mapping as $k => $v) {
      if (isset($mapping[$k]['row'])) { continue; }
      // if (empty($mapping[$k]['column'])) {
      //   throw new \codename\core\exception(self::EXCEPTION_STOREBUFFERDDATA_COLUMNOTFOUND, \codename\core\exception::$ERRORLEVEL_FATAL);
      // }

      if(!empty($mapping[$k]['column'])) {
        // explicitly specified column
        $columnIndexMap[$k] = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($mapping[$k]['column']);
      } else {
        // fallback to linear column index
        $columnIndexMap[$k] = $columnIndex;
      }

      // produce a heading, if key_row is set to a value
      if ($this->key_row) {
        $worksheet->setCellValueByColumnAndRow(
          $columnIndexMap[$k],
          $this->key_row,
          ($this->numericIndexes ? ($mapping[$k]['columnName'] ?? $k) : $k)
        );
      }

      $columnIndex++;
    }

    // if start_row is set, increase this index by 1
    // and start to fill data
    $currentRow = $this->start_row + 1;

    foreach($bufferArray as $line) {
      foreach($line as $k => $v) {
        // if (empty($mapping[$k]['column'])) {
        //   throw new \codename\core\exception(self::EXCEPTION_STOREBUFFERDDATA_COLUMNOTFOUND, \codename\core\exception::$ERRORLEVEL_FATAL);
        // }
        if($mapping[$k]['setExplicitString'] ?? false) {
          $worksheet->setCellValueExplicitByColumnAndRow(
            $columnIndexMap[$k],
            ($mapping[$k]['row'] ?? $currentRow),
            $v,
            \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING
          );
        } else {
          $worksheet->setCellValueByColumnAndRow(
            $columnIndexMap[$k],
            ($mapping[$k]['row'] ?? $currentRow),
            $v
          );
        }

        if($mapping[$k]['formatCode'] ?? false) {
          $worksheet
            ->getStyleByColumnAndRow(
              $columnIndexMap[$k],
              ($mapping[$k]['row'] ?? $currentRow)
            )
            ->getNumberFormat()
            ->setFormatCode($mapping[$k]['formatCode']);
        }
      }
      $currentRow++;
    }

    if($tagsChunk) {
      $filePassword = $tagsChunk[0]['file_password'] ?? null;
      if($filePassword) {

        $spreadsheet->getSecurity()
          ->setLockWindows(true)
          ->setLockStructure(true)
          ->setWorkbookPassword($filePassword);

        // Protect sheet
        $sheetNames = $spreadsheet->getSheetNames();
        foreach($sheetNames as $k => $sheetName) {
            $sheet = $spreadsheet->getSheet($k);
            $protection = $sheet->getProtection();

            $protection->setPassword($filePassword);
            $protection->setSheet(true);
            $protection->setSort(true);
            $protection->setInsertRows(true);
            $protection->setFormatCells(true);
        }
      }

    }
    // $targetFilePath = $this->targetFilePath;
    // if(!$this->targetFilePath) {
    // }
    // $targetFilePath = tempnam(sys_get_temp_dir(), '_ma_');
    $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet,$this->use_writer);

    // save original state of those settings
    $prevDecimalSeparator = null;
    $prevThousandsSeparator = null;

    if($writer instanceof \PhpOffice\PhpSpreadsheet\Writer\Csv) {
      $writer->setUseBOM($this->config['encoding_utf8bom'] ?? false);
      if($val = $this->config['config']['decimal_separator'] ?? '.') {
        // NOTE: this may cause trouble, as it is called globally, so we have to save state and re-set later
        $prevDecimalSeparator = \PhpOffice\PhpSpreadsheet\Shared\StringHelper::getDecimalSeparator();
        \PhpOffice\PhpSpreadsheet\Shared\StringHelper::setDecimalSeparator($val);
      }
      if($val = $this->config['config']['thousands_separator'] ?? '') {
        // NOTE: this may cause trouble, as it is called globally, so we have to save state and re-set later
        $prevThousandsSeparator = \PhpOffice\PhpSpreadsheet\Shared\StringHelper::getThousandsSeparator();
        \PhpOffice\PhpSpreadsheet\Shared\StringHelper::setThousandsSeparator($val);
      }
      if($val = $this->config['config']['delimiter'] ?? ';') {
        $writer->setDelimiter($val);
      }
      if(array_key_exists('enclosure', $this->config['config'])) {
        $writer->setEnclosure($this->config['config']['enclosure']);
      }
      if($val = $this->config['config']['line_ending'] ?? "\r\n") {
        $writer->setLineEnding($val);
      }
      if($val = $this->config['config']['sheet_index'] ?? 0) {
        $writer->setSheetIndex($val); // pure optional... sheet index from reader?
      }
    }

    $writer->save($path);

    // restore previous states of those settings, if changed in-between
    if($prevDecimalSeparator) {
      \PhpOffice\PhpSpreadsheet\Shared\StringHelper::setDecimalSeparator($prevDecimalSeparator);
    }
    if($prevThousandsSeparator) {
      \PhpOffice\PhpSpreadsheet\Shared\StringHelper::setThousandsSeparator($prevThousandsSeparator);
    }

    // return $targetFilePath;

    // $exporte = new \codename\enbase\helper\document;
    // $exporte->createDocument(
    //   $this->targetFilePath,
    //   'test.xlsx',
    //   'test',
    //   null,
    //   1
    // );
  }
}
