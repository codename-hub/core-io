<?php
namespace codename\core\tests\target\buffered\file;

use codename\core\tests\target\abstractWriteReadTest;

class spreadsheetCsvWriteReadTest extends abstractWriteReadTest {

  /**
   * @inheritDoc
   */
  protected function setUp(): void
  {
    parent::setUp();

    // PHPSpreadsheet needs fileinfo extension
    // which provides mime_content_type
    // make sure it's available - early block otherwise.
    $this->assertTrue(extension_loaded('fileinfo'), 'PhpSpreadsheet needs fileinfo extension - test cannot proceed.');
    $this->assertTrue(function_exists('mime_content_type'), 'PhpSpreadsheet needs mime_content_type() from fileinfo extension - test cannot proceed.');
  }

  /**
   * @inheritDoc
   */
  protected function getWriteReadTargetInstance(array $configOverride = []): \codename\core\io\target
  {
    return new \codename\core\io\target\buffered\file\spreadsheet('spreadsheet_csv_test', [
      'use_writer' => 'Csv',
      'key_row' => 1,
      'config' => [
        'encoding_utf8bom' => true,
      ],
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
