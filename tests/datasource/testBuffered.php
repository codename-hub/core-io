<?php
namespace codename\core\io\tests\datasource;

/**
 * [testBuffered description]
 */
class testBuffered extends \PHPUnit\Framework\TestCase
{
  /**
   * [testBufferSizeTooLowException description]
   * @return [type] [description]
   */
  public function testBufferSizeTooLowException() {
    $this->expectException(\codename\core\exception::class);
    $this->expectExceptionMessage('EXCEPTION_DATASOURCE_BUFFERED_BUFFERSIZE_TOO_LOW');
    $source = new \codename\core\io\datasource\arraydata();
    $buffered = new \codename\core\io\datasource\buffered($source, 0);
  }

  /**
   * [testSmallBufferOnEmptySource description]
   * @return [type] [description]
   */
  public function testSmallBufferOnEmptySource() {
    $source = new \codename\core\io\datasource\arraydata();
    $source->setData([]);
    $buffered = new \codename\core\io\datasource\buffered($source, 1);

    $r = [];
    foreach($buffered as $b) {
      $r[] = $b;
    }

    // var_dump($buffered->current());

    $buffered->rewind();

    // var_dump([
    //   '$buffered->current()' => $buffered->current(),
    //   '$buffered->valid()' => $buffered->valid(),
    //   '$buffered->key()' => $buffered->key(),
    // ]);
    //
    // var_dump($r);

    $this->assertEquals(0, $source->currentProgressLimit());
    $this->assertEquals(0, $buffered->currentProgressLimit());
    $this->assertEmpty($r);
  }

  /**
   * [testBufferReadingWithMultipleBufferSizes description]
   */
  public function testBufferReadingWithMultipleBufferSizes(): void {
    $source = new \codename\core\io\datasource\arraydata();
    $source->setData([
      1, 2, 3, 4, 5, 6, 7, 8
    ]);

    for ($bufferSize=1; $bufferSize <= 16; $bufferSize++) {
      $buffered = new \codename\core\io\datasource\buffered($source, $bufferSize);
      $r = [];
      foreach($buffered as $b) {
        $r[] = $b;
      }
      $this->assertEquals([1, 2, 3, 4, 5, 6, 7, 8], $r);
    }
  }

  /**
   * [testBufferReadingWithMultipleBufferSizesDynamic description]
   */
  public function testBufferReadingWithMultipleBufferSizesDynamic(): void {
    $source = new \codename\core\io\datasource\arraydata();
    $source->setData([
      1, 2, 3, 4, 5, 6, 7, 8
    ]);

    $resultCount = $source->currentProgressLimit();

    $buffered = new \codename\core\io\datasource\buffered($source, 999);

    for ($bufferSize=1; $bufferSize <= 16; $bufferSize++) {
      // Modify buffer size of the only instance
      $buffered->setBufferSize($bufferSize);
      $buffered->rewind();

      $this->assertLessThanOrEqual($bufferSize, $buffered->getBuffer()->count());

      $r = [];
      $cnt = 0;
      foreach($buffered as $b) {
        $r[] = $b;
        $this->assertEquals($cnt, $buffered->currentProgressPosition());
        $this->assertEquals($cnt, $buffered->key());
        $cnt++;
      }

      $this->assertEquals([1, 2, 3, 4, 5, 6, 7, 8], $r);
    }
  }

  /**
  * [testSetConfigPassthrough description]
  */
  public function testSetConfigPassthrough(): void {
    // TODO, see below
    $this->markTestIncomplete('TODO: test config passthrough and verify change using non-arraydata datasource');
  }

}
