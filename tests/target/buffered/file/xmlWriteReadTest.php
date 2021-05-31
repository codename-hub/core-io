<?php
namespace codename\core\io\tests\target\buffered\file;

use codename\core\io\tests\target\abstractWriteReadTest;

class xmlWriteReadTest extends abstractWriteReadTest {

  /**
   * @inheritDoc
   */
  protected function getWriteReadTargetInstance(array $configOverride = []): \codename\core\io\target
  {
    return new \codename\core\io\target\buffered\file\xml('xml_test', [
      'version' => '1.0',
      'encoding' => 'UTF-8',
      'template_elements_path' => ['data'],
      'mapping' => [
        'key1' => [ 'path' => ['element'] ],
        'key2' => [ 'path' => ['element'] ],
        'key3' => [ 'path' => ['element'] ],
        'key4' => [ 'path' => ['element'] ],
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
      $datasource = new \codename\core\io\datasource\xml($filepath, [
        'xpath_query' => '/data/element',
        'xpath_mapping' => [
          'key1' => 'key1',
          'key2' => 'key2',
          'key3' => 'key3',
          'key4' => 'key4',
        ]
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
