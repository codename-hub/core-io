<?php
namespace codename\core\io\tests\target;

/**
 * [arraydataTest description]
 */
class arraydataTest extends \PHPUnit\Framework\TestCase
{

  /**
   * [testArraydataGeneral description]
   */
  public function testArraydataGeneral(): void {

    $target = new \codename\core\io\target\arraydata('general_example', []);

    // set data
    $result = $target->store([
      'example' => 'data'
    ]);
    $this->assertTrue($result);

    // get data
    $result = $target->getVirtualStoreData();
    $this->assertEquals([
      [ 'example' => 'data' ]
    ], $result);

    // check finish
    $this->assertEmpty($target->finish());
    
  }

}
