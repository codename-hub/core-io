<?php

namespace codename\core\io\tests\datasource;

use codename\core\exception;
use codename\core\io\datasource\csv;
use PHPUnit\Framework\TestCase;

class csvTest extends TestCase
{
    /**
     * tests general function of the csv datasource
     * @return void [type] [description]
     * @throws exception
     */
    public function testCsvGeneral(): void
    {
        $datasource = new csv(__DIR__ . "/" . 'testcsv1.csv');
        static::assertEquals('0', $datasource->currentProgressPosition());
        static::assertEquals('31', $datasource->currentProgressLimit());
    }

    /**
     * test a simple csv file
     * @return void [type] [description]
     * @throws exception
     */
    public function testDataSourceFileNotExists(): void
    {
        $this->expectException(exception::class);
        $this->expectExceptionMessage('FILE_COULD_NOT_BE_OPEN');
        new csv(__DIR__ . "/" . 'testcsv1_filenotexists.csv');
    }

    /**
     * test a simple csv file
     * @return void [type] [description]
     * @throws exception
     */
    public function testDataSourceIsValid(): void
    {
        $datasource = new csv(__DIR__ . "/" . 'testcsv1.csv');
        $datasource->rewind();

        $head = $datasource->getHeadings();
        static::assertEquals('head1', $head[0]);
        static::assertEquals('head2', $head[1]);

        $data = $datasource->current();
        static::assertEquals('bla', $data['head1']);
        static::assertEquals('foo', $data['head2']);
    }

    /**
     * "head1","head2"
     * "l1_d1","l1_d2"
     * "l2_d1","l2_d2"
     * "l3_d1","l3_d2"
     *
     * @return void testing the next function
     * @throws exception
     */
    public function testDataSourceNext(): void
    {
        $datasource = new csv(
            __DIR__ . "/" . 'testcsv2.csv',
            [
              'autodetect_utf8_bom' => true,
              'skip_empty_rows' => true,
              'skip_rows' => 1,
              'encoding' => ['from' => 'UTF-8', 'to' => 'UTF-8'],
            ]
        );

        $i = 0;
        foreach ($datasource as $dataset) {
            $i++;
            static::assertEquals("l{$i}_d1", $dataset['head0']);
            static::assertEquals("l{$i}_d2", $dataset['head1']);
        }

        //
        // make sure we have iterated three times
        //
        static::assertEquals(3, $i);
    }

    /**
     * "head1","head2"
     * "l1_d1","l1_d2"
     * "l2_d1","l2_d2"
     * "l3_d1","l3_d2"
     *
     * @return void testing the next function
     * @throws exception
     */
    public function testDataSourceWrongOffset(): void
    {
        $datasource = new overriddenCsv(
            __DIR__ . "/" . 'testcsv2.csv',
            [
              'autodetect_utf8_bom' => true,
              'skip_empty_rows' => true,
              'skip_rows' => 1,
              'encoding' => ['from' => 'UTF-8', 'to' => 'UTF-8'],
            ]
        );

        $i = 0;
        foreach ($datasource as $dataset) {
            $i++;
            static::assertEquals("l{$i}_d1", $dataset['head0']);
            static::assertEquals("l{$i}_d2", $dataset['head1']);
        }

        //
        // make sure we have iterated three times
        //
        static::assertEquals(3, $i);
    }

    /**
     * [testDatasourceCsvWindows1252Decoding description]
     * @throws exception
     */
    public function testDatasourceCsvWindows1252Decoding(): void
    {
        $datasource = new csv(
            __DIR__ . "/" . 'csv_windows1252_umlauts.csv',
            [
              'delimiter' => ';',
              'encoding' => ['from' => 'Windows-1252', 'to' => 'UTF-8'],
            ]
        );

        $rows = [];
        foreach ($datasource as $row) {
            $rows[] = $row;
        }

        static::assertEquals([
          ['NormalColumn' => 'Ä', 'ColumnWithUmlautsÄüÖö' => '1'],
          ['NormalColumn' => 'Ü', 'ColumnWithUmlautsÄüÖö' => '2'],
          ['NormalColumn' => 'Ö', 'ColumnWithUmlautsÄüÖö' => '3'],
          ['NormalColumn' => 'ß', 'ColumnWithUmlautsÄüÖö' => '4'],
        ], $rows);
    }
}

/**
 * [overriddenCsv description]
 */
class overriddenCsv extends csv
{
    /**
     * [rewind description]
     * @return void [type] [description]
     */
    public function rewind(): void
    {
        fseek($this->handle, 1);
        if ($this->autodetectUtf8Bom) {
            $this->handleUtf8Bom();
        }
        $this->index = 0;
        $this->next();
    }
}
