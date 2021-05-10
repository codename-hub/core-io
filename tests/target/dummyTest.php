<?php
namespace codename\core\io\tests\target;

/**
 * [dummyTest description]
 */
class dummyTest extends \PHPUnit\Framework\TestCase
{

  /**
   * [testDummyGeneral description]
   */
  public function testDummyGeneral(): void {

    $target = new \codename\core\io\target\dummy('general_example', []);

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

    // check set virtual store
    $this->assertEmpty($target->setVirtualStoreEnabled(true));

    // check virtual store state
    $this->assertTrue($target->getVirtualStoreEnabled());
    
  }

  /**
   * [testDummyGeneral description]
   */
  public function testDummyFinishedError(): void {
    $this->expectException(\codename\core\exception::class);
    $this->expectExceptionMessage('EXCEPTION_CORE_IO_TARGET_VIRTUAL_ALREADY_FINISHED');

    $target = new \codename\core\io\target\dummy('finished_error', []);

    // set finish
    $this->assertEmpty($target->finish());

    // set data
    $result = $target->store([
      'example' => 'data'
    ]);

  }

}
