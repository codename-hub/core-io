<?php
namespace codename\core\tests\target\buffered\file;

use codename\core\tests\target\abstractWriteReadTest;

class csvWriteReadTest extends abstractWriteReadTest {

  /**
   * @inheritDoc
   */
  protected function getWriteReadTargetInstance(array $configOverride = []): \codename\core\io\target
  {
    return new \codename\core\io\target\buffered\file\csv('csv_test', [
      'delimiter' => ';',
      'mapping' => [
        'key1' => [ ],
        'key2' => [ ],
        'key3' => [ ],
        'key4' => [ ],
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
      $datasource = new \codename\core\io\datasource\csv($filepath, [
        'delimiter' => ';',
        'autodetect_utf8_bom' => true,
      ]);
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
