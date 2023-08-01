<?php

namespace codename\core\io\target\buffered\file;

use codename\core\app;
use codename\core\exception;
use codename\core\io\target\buffered\file;
use codename\core\io\value\text\fileabsolute\tagged;
use codename\core\value\text\fileabsolute;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\StringHelper;
use ReflectionException;

/**
 * csv file as a target
 */
class spreadsheet extends file
{
    /**
     * The current environment type configuration does not contain the desired key for the type.
     * May occur when you use multiple mail configurators.
     * @var string
     */
    public const EXCEPTION_STOREBUFFERDDATA_COLUMNOTFOUND = 'EXCEPTION_STOREBUFFERDDATA_COLUMNOTFOUND';
    /**
     * [protected description]
     * @var string|null [type]
     */
    protected ?string $use_template_file = null;
    /**
     * [protected description]
     * @var null|string
     */
    protected ?string $filepath = null;
    /**
     * [protected description]
     * @var string|null [type]
     */
    protected ?string $use_writer = null;
    /**
     * [protected description]
     * @var [type]
     */
    protected mixed $freeze = null;
    /**
     * [protected description]
     * @var [type]
     */
    protected mixed $key_row = null;
    /**
     * [protected description]
     * @var int [type]
     */
    protected int $start_row = 1;
    /**
     * [protected description]
     * @var int [type]
     */
    protected int $sheet = 0;
    /**
     * use serial columns/numeric indexes
     * instead of key => value based mappings
     * this needs "column" defined per mapping entry
     * @var bool
     */
    protected bool $numericIndexes = false;

    /**
     * {@inheritDoc}
     * @param string $name
     * @param array $config
     * @throws ReflectionException
     * @throws exception
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
     * {@inheritDoc}
     * @throws ReflectionException
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @throws \PhpOffice\PhpSpreadsheet\Reader\Exception
     * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
     * @throws exception
     */
    protected function storeBufferedData(): void
    {
        // split the array
        if ($this->splitCount && (count($this->bufferArray) > $this->splitCount)) {
            // we have to split at least one time
            $dataChunks = array_chunk($this->bufferArray, $this->splitCount);
            $tagsChunks = array_chunk($this->tagsArray, $this->splitCount);
        } else {
            $dataChunks = [$this->bufferArray];
            $tagsChunks = [$this->tagsArray];
        }

        $resultObjects = [];

        foreach ($dataChunks as $index => $dataChunk) {
            // skip empty chunks
            if (count($dataChunk) === 0) {
                continue;
            }

            $tagsChunk = $tagsChunks[$index];

            $path = $this->getNewFilePath();

            $this->internalStoreBufferedData($path, $dataChunk, $tagsChunk);

            if (!$tagsChunk) {
                // fill with empty array, if not set
                $tagsChunk = array_fill(0, count($dataChunk), []);
            }
            foreach ($tagsChunk as &$tagsElement) {
                // force csv extension in tag

                if ($this->use_writer == 'Xlsx') {
                    $extension = 'xlsx';
                } elseif ($this->use_writer == 'Xls') {
                    $extension = 'xls';
                } elseif ($this->use_writer == 'Csv') {
                    $extension = 'csv';
                } else {
                    throw new exception('INVALID_SPREADSHEET_FILE_FORMAT_SPECIFIED', exception::$ERRORLEVEL_ERROR, $this->use_writer);
                }

                $tagsElement['file_extension'] = $extension;
            }

            if ($tagsChunk) {
                $resultObjects[] = new tagged($path, $tagsChunk);
            } else {
                $resultObjects[] = new fileabsolute($path);
            }
        }

        $this->fileResults = $resultObjects;
    }

    /**
     * @param $path
     * @param $bufferArray
     * @param $tagsChunk
     * @return void
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @throws \PhpOffice\PhpSpreadsheet\Reader\Exception
     * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
     */
    protected function internalStoreBufferedData($path, $bufferArray, $tagsChunk = null): void
    {
        // this automatically creates the matching reader for the file.
        if ($this->filepath) {
            $reader = IOFactory::createReaderForFile($this->filepath);
            $spreadsheet = $reader->load($this->filepath);
        } else {
            $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        }
        $worksheet = $spreadsheet->setActiveSheetIndex($this->sheet);

        if ($this->freeze ?? false) {
            $worksheet->freezePane($this->freeze);
        }

        $mapping = $this->getMapping();
        $columnIndexMap = [];

        // Excel Column Index starts at 1, not 0
        $columnIndex = 1;

        foreach ($mapping as $k => $v) {
            if (isset($v['row'])) {
                continue;
            }

            if (!empty($v['column'])) {
                // explicitly specified column
                $columnIndexMap[$k] = Coordinate::columnIndexFromString($v['column']);
            } else {
                // fallback to linear column index
                $columnIndexMap[$k] = $columnIndex;
            }

            // produce a heading, if key_row is set to a value
            if ($this->key_row) {
                $worksheet->setCellValue(
                    [
                      $columnIndexMap[$k],
                      $this->key_row,
                    ],
                    ($this->numericIndexes ? ($v['columnName'] ?? $k) : $k)
                );
            }

            $columnIndex++;
        }

        // if start_row is set, increase this index by 1
        // and start to fill data
        $currentRow = $this->start_row + 1;

        foreach ($bufferArray as $line) {
            foreach ($line as $k => $v) {
                if ($mapping[$k]['setExplicitString'] ?? false) {
                    $worksheet->setCellValueExplicit(
                        [
                          $columnIndexMap[$k] ?? null,
                          ($mapping[$k]['row'] ?? $currentRow),
                        ],
                        $v,
                        DataType::TYPE_STRING
                    );
                } else {
                    $worksheet->setCellValue(
                        [
                          $columnIndexMap[$k] ?? null,
                          ($mapping[$k]['row'] ?? $currentRow),
                        ],
                        $v
                    );
                }

                if ($mapping[$k]['formatCode'] ?? false) {
                    $worksheet
                      ->getStyle([
                        $columnIndexMap[$k] ?? null,
                        ($mapping[$k]['row'] ?? $currentRow),
                      ])
                      ->getNumberFormat()
                      ->setFormatCode($mapping[$k]['formatCode']);
                }
            }
            $currentRow++;
        }

        if ($tagsChunk) {
            $filePassword = $tagsChunk[0]['file_password'] ?? null;
            if ($filePassword) {
                $spreadsheet->getSecurity()
                  ->setLockWindows(true)
                  ->setLockStructure(true)
                  ->setWorkbookPassword($filePassword);

                // Protect sheet
                $sheetNames = $spreadsheet->getSheetNames();
                foreach ($sheetNames as $k => $sheetName) {
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
        $writer = IOFactory::createWriter($spreadsheet, $this->use_writer);

        // save original state of those settings
        $prevDecimalSeparator = null;
        $prevThousandsSeparator = null;

        if ($writer instanceof \PhpOffice\PhpSpreadsheet\Writer\Csv) {
            $writer->setUseBOM($this->config['encoding_utf8bom'] ?? false);
            if ($val = $this->config['config']['decimal_separator'] ?? '.') {
                // NOTE: this may cause trouble, as it is called globally, so we have to save state and re-set later
                $prevDecimalSeparator = StringHelper::getDecimalSeparator();
                StringHelper::setDecimalSeparator($val);
            }
            if ($val = $this->config['config']['thousands_separator'] ?? '') {
                // NOTE: this may cause trouble, as it is called globally, so we have to save state and re-set later
                $prevThousandsSeparator = StringHelper::getThousandsSeparator();
                StringHelper::setThousandsSeparator($val);
            }
            if ($val = $this->config['config']['delimiter'] ?? ';') {
                $writer->setDelimiter($val);
            }
            if (array_key_exists('enclosure', $this->config['config'])) {
                $writer->setEnclosure($this->config['config']['enclosure'] ?? '');
            }
            if ($val = $this->config['config']['line_ending'] ?? "\r\n") {
                $writer->setLineEnding($val);
            }
            if ($val = $this->config['config']['sheet_index'] ?? 0) {
                $writer->setSheetIndex($val); // pure optional... sheet index from reader?
            }
        }

        $writer->save($path);

        // restore previous states of those settings, if changed in-between
        if ($prevDecimalSeparator) {
            StringHelper::setDecimalSeparator($prevDecimalSeparator);
        }
        if ($prevThousandsSeparator) {
            StringHelper::setThousandsSeparator($prevThousandsSeparator);
        }
    }
}
