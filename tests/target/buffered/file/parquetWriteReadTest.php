<?php
namespace codename\core\tests\target\buffered\file;

use codename\core\tests\target\abstractWriteReadTest;

class parquetWriteReadTest extends abstractWriteReadTest {

  /**
   * @inheritDoc
   */
  protected function getWriteReadTargetInstance(): \codename\core\io\target
  {
    return new \codename\core\io\target\buffered\file\parquet('parquet_test', [
      'mapping' => [
        'key1' => [ 'php_type' => 'string'  ],
        'key2' => [ 'php_type' => 'integer' ],
        'key3' => [ 'php_type' => 'double'  ],
        'key4' => [ 'php_type' => 'string', 'is_nullable' => true  ],
      ]
    ]);
  }

  /**
   * @inheritDoc
   */
  protected function readTargetData(\codename\core\io\target $target): array {
    $files = $target->getFileResultArray();
    $this->assertCount(1, $files);
    foreach($files as $file) {
      $filepath = $file->get();
      $datasource = new \codename\core\io\datasource\parquet($filepath);

      $res = [];
      foreach($datasource as $r) {
        $res[] = $r;
      }
      return $res;
    }
  }

  /**
   * @inheritDoc
   */
  protected function cleanupTarget(\codename\core\io\target $target): void
  {
    foreach($target->getFileResultArray() as $file) {
      unlink($file->get());
    }
  }

}
