<?php

namespace codename\core\io\tests\datasource;

use codename\core\io\datasource\spreadsheet;
use PhpOffice\PhpSpreadsheet\Exception;
use PHPUnit\Framework\TestCase;

/**
 * [testSpreadsheet description]
 */
class spreadsheetTest extends TestCase
{
    /**
     * tests general function of the spreadsheet datasource
     * @return void [type] [description]
     * @throws Exception
     * @throws \PhpOffice\PhpSpreadsheet\Reader\Exception
     */
    public function testSpreadsheetGeneral(): void
    {
        $datasource = new spreadsheet(__DIR__ . "/" . 'testSpreadsheet2.xlsx');

        static::assertEquals('0', $datasource->key());

        static::assertEquals('0', $datasource->currentProgressPosition());

        static::assertEquals('0', $datasource->currentProgressLimit());

        try {
            $datasource->setConfig([]);
        } catch (\Exception) {
            static::fail();
        }

        static::assertTrue(true);
    }

    /**
     * test a simple spreadsheet file
     * @return void [type] [description]
     * @throws Exception
     * @throws \PhpOffice\PhpSpreadsheet\Reader\Exception
     */
    public function testDataSourceIsValid(): void
    {
        $datasource = new spreadsheet(
            __DIR__ . "/" . 'testSpreadsheet1.xlsx',
            [
              'custom_sheet_index' => 0,
              'multisheet' => 0,
              'skip_rows' => 3,
              'header_row' => 2,
            ]
        );

        $datasource->next();

        $data = $datasource->current();
        static::assertEquals('Value2-1', $data['A']);
        static::assertEquals('Value2-2', $data['B']);
        static::assertEquals('Value2-3', $data['C']);
    }

    /**
     * test a simple spreadsheet file
     * @return void [type] [description]
     * @throws Exception
     * @throws \PhpOffice\PhpSpreadsheet\Reader\Exception
     */
    public function testDataSourceIsValidWithXls(): void
    {
        $datasource = new spreadsheet(
            __DIR__ . "/" . 'testSpreadsheet1.xls',
            [
              'custom_sheet_index' => 0,
              'multisheet' => 0,
              'skip_rows' => 3,
              'header_row' => 2,
            ]
        );

        $datasource->next();

        $data = $datasource->current();
        static::assertEquals('Value2-1', $data['A']);
        static::assertEquals('Value2-2', $data['B']);
        static::assertEquals('Value2-3', $data['C']);
    }

    /**
     * test a simple spreadsheet file
     * @return void [type] [description]
     * @throws Exception
     * @throws \PhpOffice\PhpSpreadsheet\Reader\Exception
     */
    public function testDataSourceNext(): void
    {
        $datasource = new spreadsheet(
            __DIR__ . "/" . 'testSpreadsheet1.xlsx',
            [
              'skip_rows' => 1,
              'header_row' => 2,
            ]
        );

        $i = 0;
        foreach ($datasource as $dataset) {
            $i++;
            static::assertEquals("Value$i-1", $dataset['Head1']);
            static::assertEquals("Value$i-2", $dataset['Head2']);
            static::assertEquals("Value$i-3", $dataset['Head3']);
        }

        //
        // make sure we have iterated two times
        //
        static::assertEquals(2, $i);
    }

    /**
     * test a simple spreadsheet file
     * @return void [type] [description]
     * @throws Exception
     * @throws \PhpOffice\PhpSpreadsheet\Reader\Exception
     */
    public function testDataSourceMulti(): void
    {
        $datasource = new spreadsheet(
            __DIR__ . "/" . 'testSpreadsheet3.xlsx',
            [
              'multisheet' => true,
              'custom_sheet_index' => 1,
              'skip_rows' => 3,
              'header_row' => 2,
            ]
        );

        $i = 0;
        foreach ($datasource as $dataset) {
            $i++;
            if ($i === 1) {
                static::assertEquals('12', $dataset['A']);
                static::assertEquals('345', $dataset['B']);
                static::assertEquals('678', $dataset['C']);
            } elseif ($i === 2) {
                static::assertEquals('901', $dataset['A']);
                static::assertEquals('234', $dataset['B']);
                static::assertEquals('567', $dataset['C']);
            } elseif ($i === 3) {
                static::assertEquals('jkl', $dataset['A']);
                static::assertEquals('mno', $dataset['B']);
                static::assertEquals('pqr', $dataset['C']);
            } elseif ($i === 4) {
                static::assertEquals('stu', $dataset['A']);
                static::assertEquals('vwx', $dataset['B']);
                static::assertEquals('yz', $dataset['C']);
            }
        }
    }
}
