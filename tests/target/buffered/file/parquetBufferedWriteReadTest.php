<?php
namespace codename\core\tests\target\buffered\file;

use codename\core\tests\target\abstractWriteReadTest;

class parquetBufferedWriteReadTest extends abstractWriteReadTest {

  /**
   * [testCompressionNone description]
   */
  public function testCompressionNone(): void {
    $this->testWriteReadTarget([
      'compression' => 'none',
    ]);
  }

  /**
   * [testAutoGuessTypes description]
   */
  public function testAutoGuessTypes(): void {
    $this->testWriteReadTarget([
      'mapping' => [
        'key1' => [ ],
        'key2' => [ ],
        'key3' => [ ],
        'key4' => [ 'php_type' => 'string', ], // having only null items can't be type-guessed
      ]
    ]);
  }

  /**
   * [testCompressionGzip description]
   */
  public function testCompressionGzip(): void {
    $this->testWriteReadTarget([
      'compression' => 'gzip',
    ]);
  }

  /**
   * Special test with an overridden target class
   * to allow empty data pages/RGs to be written in-between
   */
  public function testEmptyDataPagesInbetween(): void {
    $target = new overriddenParquetTarget('parquet_empty_data_pages_test', [
      'buffer'      => true,
      'buffer_size' => 2,
      'mapping' => [
        'key1' => [ 'php_type' => 'string'  ],
        'key2' => [ 'php_type' => 'integer' ],
        'key3' => [ 'php_type' => 'double'  ],
        'key4' => [ 'php_type' => 'string', 'is_nullable' => true  ],
      ]
    ]);
    $samples = $this->getSampleData();
    $tags = $this->getSampleTags();
    foreach($samples as $sample) {
      $target->publicWriteData([]);
      $target->store($sample, $tags);
      $target->publicWriteData([]);
    }
    $target->finish();
    $this->compareData($target, $samples);
    $this->cleanupTarget($target);
  }

  /**
   * @inheritDoc
   */
  protected function getWriteReadTargetInstance(array $configOverride = []): \codename\core\io\target
  {
    return new \codename\core\io\target\buffered\file\parquet('parquet_test', array_replace([
      'buffer'      => true,
      'buffer_size' => 2,
      'mapping' => [
        'key1' => [ 'php_type' => 'string'  ],
        'key2' => [ 'php_type' => 'integer' ],
        'key3' => [ 'php_type' => 'double'  ],
        'key4' => [ 'php_type' => 'string', 'is_nullable' => true  ],
      ]
    ], $configOverride));
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

class overriddenParquetTarget extends \codename\core\io\target\buffered\file\parquet {
  /**
   * public access to ::writeData for simulating special kinds
   * of parquet files (e.g. writing empty RGs/data pages)
   * @param  array  $data [description]
   * @return void
   */
  public function publicWriteData(array $data): void {
    $this->writeData($data);
  }
}
