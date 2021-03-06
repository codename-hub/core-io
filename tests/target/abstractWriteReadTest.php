<?php
namespace codename\core\io\tests\target;

use codename\core\test\base;

abstract class abstractWriteReadTest extends base {

  /**
   * [getSampleData description]
   * @return array [description]
   */
  protected function getSampleData(): array {
    return [
      [
        'key1' => 'value1',
        'key2' => 2,
        'key3' => 3.1415,
        'key4' => null,
      ],
      [
        'key1' => 'value2',
        'key2' => 3,
        'key3' => 4.23446,
        'key4' => null,
      ],
      [
        'key1' => 'value3',
        'key2' => 4,
        'key3' => 5.454545,
        'key4' => null,
      ],
    ];
  }

  /**
   * [getSampleTags description]
   * @return array|null
   */
  protected function getSampleTags(): ?array {
    return null;
  }

  /**
   * Creates the instance for the generic write-read test
   * @param  array $configOverride [optional configuration override]
   * @return \codename\core\io\target [description]
   */
  protected abstract function getWriteReadTargetInstance(array $configOverride = []): \codename\core\io\target;

  /**
   * Read the target's written/collected data
   * and re-parse it as an array
   * @param  \codename\core\io\target $target [description]
   * @return array                        [description]
   */
  protected abstract function readTargetData(\codename\core\io\target $target): array;

  /**
   * [compareData description]
   * @param  \codename\core\io\target $target  [description]
   * @param  array                $samples [description]
   */
  protected function compareData(\codename\core\io\target $target, array $samples): void {
    $result = $this->readTargetData($target);
    $this->assertEquals($samples, $result);
  }

  /**
   * [cleanupTarget description]
   * @param  \codename\core\io\target $target [description]
   * @return void
   */
  protected abstract function cleanupTarget(\codename\core\io\target $target): void;

  /**
   * [testWriteReadTarget description]
   * @param array $configOverride [description]
   * @param array|null $samplesOverride [description]
   */
  public function testWriteReadTarget(array $configOverride = [], ?array $samplesOverride = null): void {
    $target = $this->getWriteReadTargetInstance($configOverride);
    $samples = $samplesOverride ?? $this->getSampleData();
    $tags = $this->getSampleTags();
    foreach($samples as $sample) {
      $target->store($sample, $tags);
    }
    $target->finish();
    $this->compareData($target, $samples);
    $this->cleanupTarget($target);
  }

  /**
   * [testCallingStoreAfterFinishCrashes description]
   */
  public function testCallingStoreAfterFinishCrashes(): void {
    $target = $this->getWriteReadTargetInstance();
    $samples = $this->getSampleData();
    $tags = $this->getSampleTags();
    foreach($samples as $sample) {
      $target->store($sample, $tags);
    }
    $target->finish();

    // In this case, we do not compare,
    // but try to call store() again
    // which MUST result in an exception
    try {
      $target->store([]);
      $this->fail('Exception EXCEPTION_CORE_IO_TARGET_BUFFERED_ALREADY_FINISHED did not happen!');
    } catch (\codename\core\exception $e) {
      $this->assertEquals('EXCEPTION_CORE_IO_TARGET_BUFFERED_ALREADY_FINISHED', $e->getMessage());
    }

    $this->cleanupTarget($target);
  }


}
