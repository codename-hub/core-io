<?php

namespace codename\core\io\datasource;

use codename\core\io\datasource;
use PhpOffice\PhpSpreadsheet\Calculation\Functions;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\Exception;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Reader\IReader;
use PhpOffice\PhpSpreadsheet\Worksheet\RowIterator;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

/**
 * [spreadsheet description]
 */
class spreadsheet extends datasource
{
    /**
     * [protected description]
     * @var IReader
     */
    protected IReader $reader;

    /**
     * [protected description]
     * @var null|string
     */
    protected ?string $filename = null;

    /**
     * true if the given sheet(s) are headed
     * @var bool
     */
    protected bool $headed = true;

    /**
     * [protected description]
     * @var bool
     */
    protected bool $includeSpreadsheetColumns = false;

    /**
     * [protected description]
     * @var null|\PhpOffice\PhpSpreadsheet\Spreadsheet
     */
    protected ?\PhpOffice\PhpSpreadsheet\Spreadsheet $sheet = null;

    /**
     * [protected description]
     * @var null|Worksheet|\PhpOffice\PhpSpreadsheet\Spreadsheet
     */
    protected null|Worksheet|\PhpOffice\PhpSpreadsheet\Spreadsheet $activeSheet = null;

    /**
     * [protected description]
     * @var null|RowIterator
     */
    protected ?RowIterator $activeSheetRowIterator = null;

    /**
     * [protected description]
     * @var bool
     */
    protected bool $includeEmptyRows = false;

    /**
     * [__construct description]
     * @param string $file [description]
     * @param bool $headed [description]
     * @param bool $includeSpreadsheetColumns [description]
     */
    /**
     * specific sheet index to use
     * @var int|string|null
     */
    protected string|int|null $customSheetIndex = null;
    /**
     * Let the reader read across multiple sheets
     * as one continuous dataset
     * @var bool
     */
    protected bool $multisheet = false;
    /**
     * skip until this row number
     * @var int
     */
    protected int $skipRows = 0;
    /**
     * number of header row
     * @var int
     */
    protected int $headerRow = 1;
    /**
     * [protected description]
     * @var mixed
     */
    protected mixed $currentValue = null;
    /**
     * [protected description]
     * @var array
     */
    protected array $currentColumnMapping = [];
    /**
     * [protected description]
     * @var int
     */
    protected int $sheetIndex = 0;
    /**
     * [protected description]
     * @var int
     */
    protected int $globalRowIndex = 0;

    /**
     * [__construct description]
     * @param string $file [description]
     * @param array $config [description]
     * @throws Exception
     * @throws \PhpOffice\PhpSpreadsheet\Reader\Exception
     */
    public function __construct(string $file = '', array $config = [] /* bool $headed = true, bool $includeSpreadsheetColumns = false*/)
    {
        $this->setConfig($config);

        $this->filename = $file;

        set_time_limit(0);

        // Increase memory limit to heavenly heights.
        ini_set('memory_limit', '2048M');

        // use \DateTime Objects by default
        Functions::setReturnDateType(Functions::RETURNDATE_PHP_OBJECT);

        // this automatically creates the matching reader for the file.
        $this->reader = IOFactory::createReaderForFile($this->filename);

        // skip empty cells
        $this->reader->setReadEmptyCells(false);

        $this->sheet = $this->reader->load($this->filename);

        if ($this->sheet->getSheetCount() > 0) {
            if ($this->customSheetIndex) {
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
     * {@inheritDoc}
     */
    public function setConfig(array $config): void
    {
        $this->headed = $config['headed'] ?? true;
        $this->includeSpreadsheetColumns = $config['include_spreadsheet_columns'] ?? false;
        $this->skipRows = $config['skip_rows'] ?? 0;
        $this->headerRow = $config['header_row'] ?? 1;
        $this->customSheetIndex = $config['custom_sheet_index'] ?? null;
        $this->multisheet = $config['multisheet'] ?? false;
    }

    /**
     * {@inheritDoc}
     * @throws Exception
     */
    public function rewind(): void
    {
        $this->currentValue = null;
        $this->sheetIndex = $this->customSheetIndex ?? 0;
        $this->activeSheet = $this->sheet->getSheet($this->sheetIndex);
        $this->activeSheetRowIterator = $this->activeSheet->getRowIterator();
        $this->globalRowIndex = 0;
        $this->next();
    }

    /**
     * {@inheritDoc}
     * @throws Exception
     * @throws \PhpOffice\PhpSpreadsheet\Calculation\Exception
     */
    public function next(): void
    {
        // reset current value
        $this->currentValue = null;

        if ($this->skipRows) {
            while ($this->activeSheetRowIterator->key() < ($this->skipRows)) {
                $this->activeSheetRowIterator->next();
            }
        }

        if ($this->headed) {
            while ($this->activeSheetRowIterator->key() < ($this->headerRow)) {
                $this->activeSheetRowIterator->next();
            }
        }

        // NOTE: RowIterator starts its index at 1
        // Therefore, we are getting the headed data columns at index 2
        if ($this->headed && $this->activeSheetRowIterator->valid() && $this->activeSheetRowIterator->key() === ($this->headerRow)) {
            // use first row in each sheet for mapping the values
            // get current row.
            $row = $this->activeSheetRowIterator->current();

            // iterate over cells.
            $cellIterator = $row->getCellIterator();

            // reset old column mapping
            $this->currentColumnMapping = [];

            // create a new column mapping
            foreach ($cellIterator as $cell) {
                $cellValue = $cell->getValue();
                if (!empty($cellValue)) {
                    $this->currentColumnMapping[$cell->getColumn()] = $cellValue;
                }
            }
        }

        $this->activeSheetRowIterator->next();

        if ($this->activeSheetRowIterator->valid()) {
            // get current row.
            $row = $this->activeSheetRowIterator->current();

            // iterate over cells.
            $cellIterator = $row->getCellIterator();

            $newCurrentValue = [];
            $valueCount = 0;

            foreach ($cellIterator as $cell) {
                // we may distinguish formulas from formatted datetime values or others.
                // $cellValue = $cell->getFormattedValue(); // $cell->isFormula() || $cell->getDataType == \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE ? $cell->getCalculatedValue() : $cell->getFormattedValue();

                $cellValue = null;
                if ($cell instanceof Cell) {
                    if ($cell->isFormula()) {
                        $cellValue = $cell->getCalculatedValue();
                    } else {
                        // $cellValue = $cell->getValue();
                        $cellValue = $cell->getFormattedValue();
                    }
                }

                if (!empty($cellValue)) {
                    $valueCount++;
                }

                $cellColumn = $cell->getColumn();

                // check for a mapping match:
                if ($this->headed && isset($this->currentColumnMapping[$cellColumn])) {
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

            if (!$this->includeEmptyRows && $valueCount === 0) {
                // if we have no values in this row (valueCount === 0)
                // and we want to skip empty rows, simply move on to next row.
                $this->next();
            } else {
                $this->globalRowIndex++;
                $this->currentValue = $newCurrentValue;
            }
        } elseif ($this->multisheet) {
            // we may go to the next sheet
            // if there's at least one more
            if ($this->sheetIndex < ($this->sheet->getSheetCount() - 1)) {
                $this->sheetIndex++;
                $this->activeSheet = $this->sheet->getSheet($this->sheetIndex);
                $this->activeSheetRowIterator = $this->activeSheet->getRowIterator();
                $this->next();
            }
        }
    }

    /**
     * {@inheritDoc}
     */
    public function key(): mixed
    {
        return $this->globalRowIndex;
    }

    /**
     * {@inheritDoc}
     */
    public function valid(): bool
    {
        // is this check enough?
        return $this->currentValue != null && $this->activeSheetRowIterator->valid();
    }

    /**
     * {@inheritDoc}
     */
    public function current(): mixed
    {
        return $this->currentValue;
    }

    /**
     * {@inheritDoc}
     */
    public function currentProgressPosition(): int
    {
        return $this->globalRowIndex;
    }

    /**
     * {@inheritDoc}
     */
    public function currentProgressLimit(): int
    {
        return 0;
    }
}
