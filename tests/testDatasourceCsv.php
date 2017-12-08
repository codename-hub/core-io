<?php
namespace codename\core\io\tests;

class testDataSourceCsv extends \PHPUnit\Framework\TestCase
{
  /**
   * test an simple csv file
   * @return [type] [description]
   */
  public function testDataSourceIsValid()
  {
    //$this->expectException();
    $uut = new \codename\core\io\datasource\csv(__DIR__ . "/" . 'test1.csv');
    $uut->next();
    $data = $uut->current();
    $this->assertEquals($data['head1'], 'bla');
    $this->assertEquals($data['head2'], 'foo');

  }

  /**
   * test an simple csv file
   * @return [type] [description]
   */
  public function testDataSourceIsNotValid()
  {
    //$this->expectException();
    $uut = new \codename\core\io\datasource\csv(__DIR__ . "/" . 'test1.csv');

    $uut->next();
    $data = $uut->current();

    // @TODO: we have to use an INVALID csv file for this
    // $this->assertEquals($data[0], 'bla');


  }

  /**
   * "head1","head2"
   * "l1_d1","l1_d2"
   * "l2_d1","l2_d2"
   * "l3_d1","l3_d2"
   *
   * @return void testing the next function
   */
  public function testDataSourceNext()
  {
    $datasource = new \codename\core\io\datasource\csv(__DIR__ . "/" . 'test2.csv');

    $i = 0;
    foreach($datasource as $dataset) {
      $i++;
      $this->assertEquals($dataset['head0'], "l{$i}_d1");
      $this->assertEquals($dataset['head1'], "l{$i}_d2");
    }

    //
    // make sure we have iterated three times
    //
    $this->assertEquals(3, $i);
  }

  /**
   * tests key stability of the multicsv datasource
   * @return void
   */
  public function testDatasourceMulticsvKeys () {
    $datasource = new \codename\core\io\datasource\multicsv([
      __DIR__ . "/" . 'sequence0.csv',
      __DIR__ . "/" . 'sequence1.csv',
      __DIR__ . "/" . 'sequence2.csv'
    ], [
      'delimiter' => ','
    ]);

    $datasource->rewind();

    // keep track of keys we've iterated over
    $keysIterated = [];
    $keyExpected = 0;

    $i = 0;
    foreach($datasource as $key => $dataset) {
      $this->assertFalse(in_array($key, $keysIterated), "Assert index/key '{$key}' hasn't been iterated over yet (keysIterated: ".implode(',', $keysIterated).").");
      $this->assertEquals($keyExpected, $key, "Assert a stable and linear key progression from 0...n in a +1 manner.");
      $keyExpected++;
      $i++;
      $keysIterated[] = $key;
    }

    //
    // make sure we have iterated NINE times
    //
    $this->assertEquals(9, $i, "Asset we've iterated over the complete datasource");

  }


  /**
   * "head1","head2"
   * "l1_d1","l1_d2"
   * "l2_d1","l2_d2"
   * "l3_d1","l3_d2"
   *
   * @return void testing the next function
   */
  public function testMultiDataSourceNext()
  {
    $datasource = new \codename\core\io\datasource\multicsv([
      __DIR__ . "/" . 'sequence0.csv',
      __DIR__ . "/" . 'sequence1.csv',
      __DIR__ . "/" . 'sequence2.csv'
    ], [
      'delimiter' => ','
    ]);

    $datasource->rewind();

    $i = 0;
    foreach($datasource as $dataset) {
      $i++;

      // DEBUG output
      // echo(chr(10).chr(10)."testMultiDataSourceNext entry".chr(10));
      // print_r($dataset);
      // echo(chr(10).chr(10));

      $this->assertEquals($dataset['head1'], "l{$i}_d1");
      $this->assertEquals($dataset['head2'], "l{$i}_d2");
    }

    //
    // make sure we have iterated NINE times
    //
    $this->assertEquals(9, $i);
  }


  public function testImport()
  {
      /*
      $import = new \codename\core\io\import('config/import/example_import.json');

      $import->setDatasource('source1', new \codename\core\io\datasource\csv(__DIR__."/"."test1.csv", false));
      $import->setDatasource('source2', new \codename\core\io\datasource\csv(__DIR__."/"."test2.csv", false));

      $import->setDatasource('source3'  , new \codename\core\io\datasource\multicsv(
        [__DIR__."/"."sequence0.csv",
        __DIR__."/"."sequence1.csv",
        __DIR__."/"."sequence2.csv"]
      ));
      */
      $config = new \codename\core\config\json(__DIR__ . '/test1.json');



      //$import->setTarget('target', )

     //todo: manage where and when dataflows
    /*

     */
  }

}
