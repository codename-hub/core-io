<?php
namespace codename\core\io\tests\target\buffered\file;

use codename\core\tests\base;

class jsonTest extends base
{

  /**
   * [getSampleData description]
   * @return array [description]
   */
  protected function getSampleData(): array {
    return [
      [
        'key1' => 'value1',
        'key2' => '2',
        'key3' => '3.1415',
        'key4' => ''
      ],
      [
        'key1' => 'value2',
        'key2' => '3',
        'key3' => '4.2344',
        'key4' => ''
      ],
      [
        'key1' => 'value3',
        'key2' => '4',
        'key3' => '5.4545',
        'key4' => ''
      ],
    ];
  }

  /**
   * [testJson description]
   */
  public function testJson(): void {
    $tags = [
      'file_name'             => 'moep',
    ];

    $target = new \codename\core\io\target\buffered\file\json('json_test', [
      'encoding'                => 'UTF-8',
      'encoding_uft8bom'        => false,
      'template_elements_path'  => [ 'processes' ],
      'mapping' => [
        'key1' => [ 'path' => [ 'process' ] ],
        'key2' => [ 'path' => [ 'process' ] ],
        'key3' => [ 'path' => [ 'process' ] ],
        'key4' => [ 'path' => [ 'process' ] ],
      ],
      'tags'                    => $tags,
    ]);

    $samples = $this->getSampleData();
    foreach($samples as $sample) {
      $target->store($sample, $tags);
    }
    $target->finish();

    $files = $target->getFileResultArray();
    $this->assertCount(1, $files);

    $res = [];
    foreach($files as $file) {
      $filepath = $file->get();

      $data = (new \codename\core\config\json($filepath))->get();

      foreach($data['processes'] as $r) {
        $res[] = $r['process'] ?? [];
      }

      unlink($filepath);
    }

    $this->assertEquals($samples, $res);

  }

  /**
   * [testJson description]
   */
  public function testJsonWithSplitCount(): void {
    $tags = [
      'file_name'             => 'moep',
    ];

    $target = new \codename\core\io\target\buffered\file\json('json_test', [
      'encoding'                => 'UTF-8',
      'encoding_uft8bom'        => false,
      'split_count'             => 2,
      'mapping' => [
        'key1' => [ 'path' => [ 'process' ] ],
        'key2' => [ 'path' => [ 'process' ] ],
        'key3' => [ 'path' => [ 'process' ] ],
        'key4' => [ 'path' => [ 'process' ] ],
      ],
      'tags'                    => $tags,
    ]);

    $samples = $this->getSampleData();
    foreach($samples as $sample) {
      $target->store($sample, $tags);
    }
    $target->finish();

    $files = $target->getFileResultArray();
    $this->assertCount(2, $files);

    $res = [];
    foreach($files as $file) {
      $filepath = $file->get();

      $data = (new \codename\core\config\json($filepath))->get();

      foreach($data as $r) {
        $res[] = $r['process'] ?? $r;
      }

      unlink($filepath);
    }

    $this->assertEquals($samples, $res);

  }

  /**
   * [testJsonWithTemplate description]
   */
  public function testJsonWithTemplate(): void {
    $tags = [
      'file_name'             => 'moep',
    ];

    $target = new \codename\core\io\target\buffered\file\json('json_test', [
      'encoding'                => 'UTF-8',
      'encoding_uft8bom'        => false,
      'template'                => [
        'process'               => [

        ]
      ],
      'split_count'             => 1,
      'mapping'                 => [
        'process'               => [  ],
      ],
      'tags'                    => $tags,
    ]);

    $samples = $this->getSampleData();
    $target->store([
      'process' => $samples[0]
    ], $tags);
    $target->finish();

    $files = $target->getFileResultArray();
    $this->assertCount(1, $files);

    $res = [];
    foreach($files as $file) {
      $filepath = $file->get();

      $data = (new \codename\core\config\json($filepath))->get();

      foreach($data as $r) {
        $res[] = $r;
      }

      unlink($filepath);
    }

    $this->assertEquals($samples[0], $res[0]);

  }

}
