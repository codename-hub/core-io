<?php
namespace codename\core\io\tests\helper;

class testDeepaccess extends \PHPUnit\Framework\TestCase
{

  /**
   * [testDeepaccessGet description]
   * @return [type] [description]
   */
  public function testDeepaccess () {
    $helper = new \codename\core\io\helper\deepaccess();
    $example = [];

    // set example data
    $example = $helper->set($example, [ 'example1', 'example2' ], 'example');

    $this->assertEquals([
      'example1'  => [
        'example2'  => 'example'
      ],
    ], $example);

    // get example data
    $result = $helper->get($example, [ 'example1', 'example2' ]);

    $this->assertEquals('example', $result);

    // get not exists key
    $result = $helper->get($example, [ 'error1', 'error2' ]);

    $this->assertNull($result);

  }

}
