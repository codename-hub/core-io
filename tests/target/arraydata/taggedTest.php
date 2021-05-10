<?php
namespace codename\core\io\tests\target\arraydata;

class taggedTest extends \PHPUnit\Framework\TestCase
{

  /**
   * [testWithTags description]
   */
  public function testWithTags(): void {

    $target = new \codename\core\io\target\arraydata\tagged('general_example', []);

    // set data
    $result = $target->store([
      'example' => 'data'
    ],[
      'example' => true,
    ]);
    $this->assertTrue($result);

    // get data
    $result = $target->getVirtualStoreData();
    $this->assertEquals([
      [ 'example' => 'data' ]
    ], $result);

    // check finish
    $this->assertEmpty($target->finish());

    // getStructureResultArray
    $result = $target->getStructureResultArray();

    $this->assertCount(1, $result);
    $this->assertInstanceOf(\codename\core\io\value\structure\tagged::class, $result[0]);
    $this->assertEquals([
      'example' => 'data'
    ], $result[0]->get() ?? []);

    $this->assertEquals([
      [
        'example' => true,
      ]
    ], $result[0]->getTags());

  }

  /**
   * [testWithoutTags description]
   */
  public function testWithoutTags(): void {

    $target = new \codename\core\io\target\arraydata\tagged('general_example', []);

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

    // getStructureResultArray
    $result = $target->getStructureResultArray();

    $this->assertCount(1, $result);
    $this->assertInstanceOf(\codename\core\value\structure::class, $result[0]);
    $this->assertEquals([
      'example' => 'data'
    ], $result[0]->get() ?? []);

  }

}
