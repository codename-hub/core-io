<?php
namespace codename\core\io\tests\datasource;

class testParquet extends \PHPUnit\Framework\TestCase
{
  /**
   * [testReadNonexistingFile description]
   */
  public function testReadNonexistingFile(): void {
    $this->expectException(\codename\core\exception::class);
    $this->expectExceptionMessage('FILE_COULD_NOT_BE_OPENED');
    $datasource = new \codename\core\io\datasource\parquet(__DIR__ . "/" . 'parquet/does-not-exist.parquet');
  }

  /**
   * [testReadNonParquetFile description]
   */
  public function testReadNonParquetFile(): void {
    $this->expectException(\Exception::class);
    $datasource = new \codename\core\io\datasource\parquet(__DIR__ . "/" . 'testcsv1.csv');
  }

  /**
   * [testParquetReadingStepByStep description]
   */
  public function testParquetReadingStepByStep(): void {
    $datasource = new \codename\core\io\datasource\parquet(__DIR__ . "/" . 'parquet/postcodes.plain.parquet');

    // Assert we're at the start, not having read any data
    $this->assertEquals(0, $datasource->key());
    $this->assertNull($datasource->current());
    $this->assertEquals(0, $datasource->currentProgressPosition());
    $datasource->next();

    // NOTE: key() will return 0 on the first item
    // but as in every iterator, you'll have to evaluate valid() first
    $this->assertEquals(0, $datasource->key());
    $this->assertNotNull($datasource->current());
    $this->assertEquals(0, $datasource->currentProgressPosition());

    $datasource->next();
    $this->assertEquals(1, $datasource->key());
    $this->assertNotNull($datasource->current());
    $this->assertEquals(1, $datasource->currentProgressPosition());

    // loop til end
    while($datasource->valid()) {
      $datasource->next();
    }
    $lastKey = $datasource->key();
    $datasource->next();

    // TODO: evaluate this situation...
    // print_r([$datasource->currentProgressLimit(), $datasource->currentProgressPosition()]);
    // $this->assertEquals($lastKey, $datasource->currentProgressLimit());
    // $this->assertEquals($lastKey, $datasource->currentProgressPosition());
  }

  /**
   * [testParquetReading description]
   */
  public function testParquetReading(): void {
    $datasource = new \codename\core\io\datasource\parquet(__DIR__ . "/" . 'parquet/postcodes.plain.parquet');
    $rows = [];
    foreach($datasource as $row) {
      $rows[] = $row;
    }

    $this->assertEquals(237, $datasource->key());
    $this->assertCount(237, $rows);
    $this->assertEquals(count($rows), $datasource->currentProgressLimit());

    // randomly call next()...
    $datasource->next();

    // At this point we reached the end of the file
    $this->assertFalse($datasource->valid());

    // Try to go even further...
    $datasource->next();
    $this->assertFalse($datasource->valid());

    // iterate a second time and compare results
    // tests rewinding, internally
    $rows2 = [];
    foreach($datasource as $row) {
      $rows2[] = $row;
    }

    $this->assertEquals($rows, $rows2);
  }

  /**
   * [testParquetReadingMultipage description]
   */
  public function testParquetReadingMultipage(): void {
    // TODO: evaluate whether we really have a multipage parquet file here
    $datasource = new \codename\core\io\datasource\parquet(__DIR__ . "/" . 'parquet/running_numbers_spark.gz.parquet');
    $rows = [];
    foreach($datasource as $row) {
      $rows[] = $row;
    }

    $this->assertEquals(10000, $datasource->key());
    $this->assertCount(10000, $rows);

    // randomly call next()...
    $datasource->next();

    // iterate a second time and compare results
    // tests rewinding, internally
    $rows2 = [];
    foreach($datasource as $row) {
      $rows2[] = $row;
    }

    $this->assertEquals($rows, $rows2);
  }


}
