<?php
namespace codename\core\tests\target;

use codename\core\tests\base;

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
   * Creates the instance for the generic write-read test
   * @return \codename\core\io\target [description]
   */
  protected abstract function getWriteReadTargetInstance(): \codename\core\io\target;

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
   */
  public function testWriteReadTarget(): void {
    $target = $this->getWriteReadTargetInstance();
    $samples = $this->getSampleData();
    foreach($samples as $sample) {
      $target->store($sample);
    }
    $target->finish();
    $this->compareData($target, $samples);
    $this->cleanupTarget($target);
  }


}
