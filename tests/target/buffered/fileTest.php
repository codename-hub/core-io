<?php
namespace codename\core\io\tests\target\buffered;

use codename\core\test\base;

class fileTest extends base
{

  /**
   * [testFileHandle description]
   */
  public function testFileHandle(): void {
    $target = new dummyFile('file_test', []);

    $result = $target->getFileHandle();
    $this->assertIsResource($result);

  }

  /**
   * [testNewFileHandle description]
   */
  public function testWrongNewFileHandle(): void {
    $this->expectException(\codename\core\exception::class);
    $this->expectExceptionMessage('CORE_IO_TARGET_BUFFERED_FILE_HANDLE_ERROR');

    $target = new dummyFile('file_test', [
      'append'  => true,
    ]);

    $result = $target->getNewFileHandle(__DIR__ . "/" . '');

  }

  /**
   * [testNewFileHandle description]
   */
  public function testEmptyFileResultArray(): void {
    $target = new dummyFile('file_test', []);

    $result = $target->getFileResultArray();
    $this->assertEmpty($result);

  }

}

class dummyFile extends \codename\core\io\target\buffered\file
{

  public function storeBufferedData() {
    return;
  }

  public function getFileHandle() {
    return parent::getFileHandle();
  }

  public function getNewFileHandle(string $targetFilePath) {
    return parent::getNewFileHandle($targetFilePath);
  }

  public function getFileResultArray() : array {
    return parent::getFileResultArray();
  }

}
