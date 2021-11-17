<?php
namespace codename\core\io\tests\datasource;

class testMulticsv extends \PHPUnit\Framework\TestCase
{

  /**
   * tests general function of the multicsv datasource
   * @return [type] [description]
   */
  public function testMulticsvGeneral () {
    $datasource = new \codename\core\io\datasource\multicsv(__DIR__ . "/" . 'testmulticsv1.csv');

    $this->assertEquals('0', $datasource->currentProgressPosition());

    $this->assertEquals('68', $datasource->currentProgressLimit());

    $datasource->setConfig([
      'delimiter' => ';',
      'headed'    => true,
    ]);

  }

  /**
   * tests key stability of the multicsv datasource
   * @return void
   */
  public function testDatasourceMulticsvKeys () {
    $datasource = new \codename\core\io\datasource\multicsv([
      __DIR__ . "/" . 'testmulticsv1.csv',
      __DIR__ . "/" . 'testmulticsv2.csv',
      __DIR__ . "/" . 'testmulticsv3.csv'
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
      __DIR__ . "/" . 'testmulticsv1.csv',
      __DIR__ . "/" . 'testmulticsv2.csv',
      __DIR__ . "/" . 'testmulticsv3.csv'
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

}
