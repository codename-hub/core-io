<?php
namespace codename\core\io\tests\datasource;

/**
 * [testArraydata description]
 */
class testArraydata extends \PHPUnit\Framework\TestCase
{

  /**
   * tests general function of the arraydata datasource
   * @return [type] [description]
   */
  public function testArraydata () {
    $datasource = new \codename\core\io\datasource\arraydata();
    $datasource->setData([
      [
        'oldkey1' => 'abc',
        'oldkey2' => 'def',
        'oldkey3' => 'ghi',
      ],
      [
        'oldkey1' => 'jkl',
        'oldkey2' => 'mno',
        'oldkey3' => 'pqr',
      ],
      [
        'oldkey1' => 'stu',
        'oldkey2' => 'vwx',
        'oldkey3' => 'yz',
      ]
    ]);

    $this->assertNull($datasource->setConfig([]));

    $this->assertEquals('0', $datasource->currentProgressPosition());

    $this->assertEquals('3', $datasource->currentProgressLimit());

    // rewind the datasources
    $datasource->rewind();

    $this->assertEquals('0', $datasource->key());

    $this->assertTrue($datasource->valid());

    // get current data
    $this->assertEquals([
      'oldkey1' => 'abc',
      'oldkey2' => 'def',
      'oldkey3' => 'ghi',
    ], $datasource->current());

    $this->assertEquals([
      'oldkey1' => 'jkl',
      'oldkey2' => 'mno',
      'oldkey3' => 'pqr',
    ], $datasource->next());

  }

}
