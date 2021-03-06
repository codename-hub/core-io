<?php
namespace codename\core\io\tests\target\buffered\file;

use codename\core\io\tests\target\abstractWriteReadTest;

class CsvBufferingAndSplittingWriteReadTest extends abstractWriteReadTest {

  /**
   * @inheritDoc
   */
  protected function setUp(): void
  {
    parent::setUp();
    $this->createdOutputFileCount = null;
  }

  /**
   * Variable that tracks the created output files count
   * @var int|null
   */
  protected $createdOutputFileCount = null;

  /**
   * [testEqualBufferSizeAndSplitCount description]
   */
  public function testEqualBufferSizeAndSplitCount(): void {
    $this->testWriteReadTarget([
      'buffer_size' => 2,
      'split_count' => 2,
    ]);
    $this->assertEquals(2, $this->createdOutputFileCount);
  }

  /**
   * [testBufferSizeGreaterThanSplitCount description]
   */
  public function testBufferSizeGreaterThanSplitCount(): void {
    $this->testWriteReadTarget([
      'buffer_size' => 3,
      'split_count' => 2,
    ]);
    $this->assertEquals(2, $this->createdOutputFileCount);
  }

  /**
   * [testBufferSizeLessThanSplitCount description]
   */
  public function testBufferSizeLessThanSplitCount(): void {
    $this->testWriteReadTarget([
      'buffer_size' => 1,
      'split_count' => 2,
    ]);
    $this->assertEquals(2, $this->createdOutputFileCount);
  }

  /**
   * Tests buffer_size == split_count == sample count
   */
  public function testBufferSizeAndSplitCountEqualsSamplesCount(): void {
    $samplesCount = count($this->getSampleData());
    $this->testWriteReadTarget([
      'buffer_size' => $samplesCount,
      'split_count' => $samplesCount,
    ]);
    $this->assertEquals(1, $this->createdOutputFileCount);
  }

  /**
   * Tests buffer_size < split_count, split count equaling the original sample count
   */
  public function testBufferSizeLessThanSplitCountWithMultipliedSamples(): void {
    $originalSamples = $this->getSampleData();
    $multipliedSamples = array_merge($originalSamples, $originalSamples, $originalSamples);
    shuffle($multipliedSamples);
    $samplesCount = count($multipliedSamples);
    $this->testWriteReadTarget([
      'buffer_size' => 2,
      'split_count' => 3,
    ], $multipliedSamples);
    $this->assertEquals(3, $this->createdOutputFileCount);
  }

  /**
   * Tests buffer_size > split_count, buffer size equaling the original sample count
   */
  public function testBufferSizeGreaterThanSplitCountWithMultipliedSamples(): void {
    $originalSamples = $this->getSampleData();
    $multipliedSamples = array_merge($originalSamples, $originalSamples, $originalSamples);
    shuffle($multipliedSamples);
    $samplesCount = count($multipliedSamples);
    $this->testWriteReadTarget([
      'buffer_size' => 3,
      'split_count' => 2,
    ], $multipliedSamples);
    $this->assertEquals(5, $this->createdOutputFileCount);
  }

  /**
   * Tests small buffer_size and large split_count, not a multiple of sample count
   */
  public function testBufferSizeByFarLessThanSplitCountWithMultipliedSamples(): void {
    $originalSamples = $this->getSampleData();
    $multipliedSamples = array_merge($originalSamples, $originalSamples, $originalSamples, $originalSamples, $originalSamples, $originalSamples);
    shuffle($multipliedSamples);
    $samplesCount = count($multipliedSamples);
    $this->testWriteReadTarget([
      'buffer_size' => 4,
      'split_count' => 10,
    ], $multipliedSamples);
    $this->assertEquals(2, $this->createdOutputFileCount);
  }

  /**
   * Tests large buffer_size and lower split_count, not a multiple of sample count
   */
  public function testBufferSizeByFarGreaterThanSplitCountWithMultipliedSamples(): void {
    $originalSamples = $this->getSampleData();
    $multipliedSamples = array_merge($originalSamples, $originalSamples, $originalSamples, $originalSamples, $originalSamples, $originalSamples);
    shuffle($multipliedSamples);
    $samplesCount = count($multipliedSamples);
    $this->testWriteReadTarget([
      'buffer_size' => 10,
      'split_count' => 4,
    ], $multipliedSamples);
    $this->assertEquals(5, $this->createdOutputFileCount);
  }

  /**
   * Tests implicitly disabling splittting by setting split_count = 0
   * and using a buffer size that is not a multiple of sample count
   */
  public function testBufferSizeNoSplittingWithMultipliedSamples(): void {
    $originalSamples = $this->getSampleData();
    $multipliedSamples = array_merge($originalSamples, $originalSamples, $originalSamples, $originalSamples, $originalSamples, $originalSamples);
    shuffle($multipliedSamples);
    $samplesCount = count($multipliedSamples);
    $this->testWriteReadTarget([
      'buffer_size' => 4,
      'split_count' => 0,
    ], $multipliedSamples);
    $this->assertEquals(1, $this->createdOutputFileCount);
  }


  /**
   * @inheritDoc
   */
  protected function getWriteReadTargetInstance(array $configOverride = []): \codename\core\io\target
  {
    //
    // We're simply writing a CSV split at 2 items/rows each
    // and re-read them using multicsv
    //
    return new \codename\core\io\target\buffered\file\csv('csv_buffer_and_split', array_replace([
      'delimiter' => ';',
      'buffer_size' => 2,
      'split_count' => 3,
      'mapping' => [
        'key1' => [ ],
        'key2' => [ ],
        'key3' => [ ],
        'key4' => [ ],
      ]
    ], $configOverride));
  }

  /**
   * @inheritDoc
   */
  protected function readTargetData(\codename\core\io\target $target): array {
    $files = $target->getFileResultArray();

    // TODO: we might assert the file count, somehow
    // as splitting is one of the features we're testing here...

    $filepaths = [];
    foreach($files as $file) {
      $filepaths[] = $file->get();
    }

    // for asserting amount of output files...
    $this->createdOutputFileCount = count($files);

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
