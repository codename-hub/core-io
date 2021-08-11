<?php
namespace codename\core\io\tests\helper;

use codename\core\io\helper\deepaccess;

class testDeepaccess extends \PHPUnit\Framework\TestCase
{
  /**
   * Tests the availability of deepaccess in the core framework itself.
   * NOTE: this CHANGED 2021-08-11, deepaccess is now in core, but we leave the core-io equivalent
   * for backwards-compatibility reasons
   */
  public function testDeepaccessMovedToCore(): void {
    $refClass = new \ReflectionClass(deepaccess::class);
    $this->assertTrue($refClass->isSubclassOf(\codename\core\helper\deepaccess::class));
  }

  /**
   * [testDeepaccessNotInitializable description]
   */
  public function testDeepaccessNotInitializable(): void {
    // Deepaccess helper is a pure static helper
    // and MUST NOT be initialized.
    $this->expectException(\Error::class);
    new deepaccess();
  }

  /**
   * [testDeepaccessGet description]
   * @return [type] [description]
   */
  public function testDeepaccess () {
    $example = [];

    // set example data
    $example = deepaccess::set($example, [ 'example1', 'example2' ], 'example');

    $this->assertEquals([
      'example1'  => [
        'example2'  => 'example'
      ],
    ], $example);

    // get example data
    $result = deepaccess::get($example, [ 'example1', 'example2' ]);

    $this->assertEquals('example', $result);

    // get not exists key
    $result = deepaccess::get($example, [ 'error1', 'error2' ]);

    $this->assertNull($result);

  }

}
