<?php

namespace codename\core\io\tests\datasource;

use codename\core\io\datasource\parquet;
use codename\parquet\ParquetException;
use Exception;
use PHPUnit\Framework\TestCase;

class parquetTest extends TestCase
{
    /**
     * [testReadNonexistingFile description]
     */
    public function testReadNonexistingFile(): void
    {
        $this->expectException(\codename\core\exception::class);
        $this->expectExceptionMessage('FILE_COULD_NOT_BE_OPENED');
        new parquet(__DIR__ . "/" . 'parquet/does-not-exist.parquet');
    }

    /**
     * [testReadNonParquetFile description]
     * @throws \codename\core\exception
     */
    public function testReadNonParquetFile(): void
    {
        $this->expectException(Exception::class);
        new parquet(__DIR__ . "/" . 'testcsv1.csv');
    }

    /**
     * [testParquetReadingStepByStep description]
     * @throws \codename\core\exception
     * @throws ParquetException
     */
    public function testParquetReadingStepByStep(): void
    {
        $datasource = new parquet(__DIR__ . "/" . 'parquet/postcodes.plain.parquet');

        // Assert we're at the start, not having read any data
        static::assertEquals(0, $datasource->key());
        static::assertNull($datasource->current());
        static::assertEquals(0, $datasource->currentProgressPosition());
        $datasource->next();

        // NOTE: key() will return 0 on the first item
        // but as in every iterator, you'll have to evaluate valid() first
        static::assertEquals(0, $datasource->key());
        static::assertNotNull($datasource->current());
        static::assertEquals(0, $datasource->currentProgressPosition());

        $datasource->next();
        static::assertEquals(1, $datasource->key());
        static::assertNotNull($datasource->current());
        static::assertEquals(1, $datasource->currentProgressPosition());

        // loop til end
        while ($datasource->valid()) {
            $datasource->next();
        }
        $datasource->next();

        // TODO: evaluate this situation...
        // print_r([$datasource->currentProgressLimit(), $datasource->currentProgressPosition()]);
        // static::assertEquals($lastKey, $datasource->currentProgressLimit());
        // static::assertEquals($lastKey, $datasource->currentProgressPosition());
    }

    /**
     * [testParquetReading description]
     * @throws ParquetException
     * @throws \codename\core\exception
     */
    public function testParquetReading(): void
    {
        $datasource = new parquet(__DIR__ . "/" . 'parquet/postcodes.plain.parquet');
        $rows = [];
        foreach ($datasource as $row) {
            $rows[] = $row;
        }

        static::assertEquals(237, $datasource->key());
        static::assertCount(237, $rows);
        static::assertEquals(count($rows), $datasource->currentProgressLimit());

        // randomly call next()...
        $datasource->next();

        // At this point we reached the end of the file
        static::assertFalse($datasource->valid());

        // Try to go even further...
        $datasource->next();
        static::assertFalse($datasource->valid());

        // iterate a second time and compare results
        // tests rewinding, internally
        $rows2 = [];
        foreach ($datasource as $row) {
            $rows2[] = $row;
        }

        static::assertEquals($rows, $rows2);
    }

    /**
     * [testParquetReadingMultipage description]
     * @throws ParquetException
     * @throws \codename\core\exception
     */
    public function testParquetReadingMultipage(): void
    {
        // TODO: evaluate whether we really have a multipage parquet file here
        $datasource = new parquet(__DIR__ . "/" . 'parquet/running_numbers_spark.gz.parquet');
        $rows = [];
        foreach ($datasource as $row) {
            $rows[] = $row;
        }

        static::assertEquals(10000, $datasource->key());
        static::assertCount(10000, $rows);

        // randomly call next()...
        $datasource->next();

        // iterate a second time and compare results
        // tests rewinding, internally
        $rows2 = [];
        foreach ($datasource as $row) {
            $rows2[] = $row;
        }

        static::assertEquals($rows, $rows2);
    }
}
