<?php
namespace codename\core\tests\target\buffered\file;

use codename\core\tests\target\abstractWriteReadTest;

class rawWriteReadTest extends abstractWriteReadTest {

  /**
   * @inheritDoc
   */
  protected function getWriteReadTargetInstance(array $configOverride = []): \codename\core\io\target
  {
    return new \codename\core\io\target\buffered\file\raw('raw_test', [
      'padding_string'  => ' ',
      'padding_mode'    => 'left',
      'truncate'        => true,
      'mapping' => [
        'key1' => [ 'rowIndex' => 0, 'columnIndex' => 0, 'length' => 10 ],
        'key2' => [ 'rowIndex' => 0, 'columnIndex' => 1, 'length' => 10 ],
        'key3' => [ 'rowIndex' => 0, 'columnIndex' => 2, 'length' => 10 ],
        'key4' => [ 'rowIndex' => 0, 'columnIndex' => 3, 'length' => 10 ],
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
      $datasource = new \codename\core\io\datasource\raw($filepath, [
        'format' => [
          'map' => [
            'key1' => [ 'type' => 'fixed', 'length' => 10 ],
            'key2' => [ 'type' => 'fixed', 'length' => 10 ],
            'key3' => [ 'type' => 'fixed', 'length' => 10 ],
            'key4' => [ 'type' => 'fixed', 'length' => 10 ],
          ],
          'trim' => true,
        ],
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
