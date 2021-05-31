<?php
namespace codename\core\io\tests\target\buffered\file;

use codename\core\test\base;

class parquetTest extends base {

  /**
   * [testWriteReadParquet description]
   */
  public function testWriteReadParquet(): void {

    $samples = [
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

    $target = new \codename\core\io\target\buffered\file\parquet('parquet_test', [
      'mapping' => [
        'key1' => [ 'php_type' => 'string'  ],
        'key2' => [ 'php_type' => 'integer' ],
        'key3' => [ 'php_type' => 'double'  ],
        'key4' => [ 'php_type' => 'string', 'is_nullable' => true  ],
      ]
    ]);

    foreach($samples as $sample) {
      $target->store($sample);
    }

    $target->finish();

    foreach($target->getFileResultArray() as $file) {
      $filepath = $file->get();
      $datasource = new \codename\core\io\datasource\parquet($filepath);

      $res = [];
      foreach($datasource as $r) {
        $res[] = $r;
      }
      $this->assertEquals($samples, $res);
      unlink($filepath);
    }
  }

}
