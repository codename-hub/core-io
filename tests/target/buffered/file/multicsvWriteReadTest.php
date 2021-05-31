<?php
namespace codename\core\io\tests\target\buffered\file;

use codename\core\io\tests\target\abstractWriteReadTest;

class multicsvWriteReadTest extends abstractWriteReadTest {

  /**
   * @inheritDoc
   */
  protected function getWriteReadTargetInstance(array $configOverride = []): \codename\core\io\target
  {
    //
    // We're simply writing a CSV split at 2 items/rows each
    // and re-read them using multicsv
    //
    return new \codename\core\io\target\buffered\file\csv('csv_test', [
      'delimiter' => ';',
      'split_count' => 2,
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
    $this->assertCount(2, $files);
    $filepaths = [];
    foreach($files as $file) {
      $filepaths[] = $file->get();
    }
    $datasource = new \codename\core\io\datasource\multicsv($filepaths, [
      'delimiter' => ';',
      'autodetect_utf8_bom' => true,
    ]);
    $res = [];
    foreach($datasource as $r) {
      $res[] = $r;
    }
    return $res;
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
