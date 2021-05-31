<?php
namespace codename\core\io\tests\target\buffered\file;

use codename\core\io\tests\target\abstractWriteReadTest;

class csvArchivedWriteReadTest extends abstractWriteReadTest {


  /**
   * @inheritDoc
   */
  protected function getSampleTags(): ?array
  {
    return [
      'archive_name' => 'testarchive',
      'archive_encryption' => true,
      'archive_encryption_type' => 'EM_AES_256',
      'archive_encryption_passphrase' => 'abcd1234',
      // NOTE: not-specifying file_name may lead to
      // absolute paths being added to the zip archive
      'file_name' => 'test',
    ];
  }
  /**
   * @inheritDoc
   */
  protected function getWriteReadTargetInstance(array $configOverride = []): \codename\core\io\target
  {
    return new \codename\core\io\target\buffered\file\csv('csv_test', [

      'delimiter' => ';',
      'config' => [
        'archive' => true,
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

      // ZIP archive working dir...
      $dir = sys_get_temp_dir().'/'.uniqid('tempCsvArchived_').'/';
      mkdir($dir, 0777, true);

      // decrypt/unzip
      $zip = new \ZipArchive();
      $zip->open($filepath);
      $zip->setPassword('abcd1234');

      // make sure there's only a single file
      $this->assertEquals(1, $zip->numFiles);

      $files = [];
      for ($i=0; $i < $zip->numFiles; $i++) {
        $files[] = $zip->getNameIndex($i);
      }
      $zip->extractTo($dir);

      $extractedCsv = $dir . '/' . $files[0];
      $datasource = new \codename\core\io\datasource\csv($extractedCsv, [
        'delimiter' => ';',
        'autodetect_utf8_bom' => true,
      ]);
      $res = [];
      foreach($datasource as $r) {
        $res[] = $r;
      }

      // remove temp file
      $datasource = null; // dispose datasource to free the filehandle
      unlink($extractedCsv);
      @rmdir($dir); // remove temp working dir, too.

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
