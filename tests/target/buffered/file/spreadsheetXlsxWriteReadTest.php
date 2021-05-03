<?php
namespace codename\core\tests\target\buffered\file;

use codename\core\tests\target\abstractWriteReadTest;

class spreadsheetXlsxWriteReadTest extends abstractWriteReadTest {

  /**
   * @inheritDoc
   */
  protected function getWriteReadTargetInstance(array $configOverride = []): \codename\core\io\target
  {
    return new \codename\core\io\target\buffered\file\spreadsheet('xlsx_test', [
      'use_writer' => 'Xlsx',
      'key_row' => 1,
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
      $datasource = new \codename\core\io\datasource\spreadsheet($filepath, [
        // default config
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
