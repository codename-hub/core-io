<?php
namespace codename\core\io\tests\target\buffered\file;

use codename\core\test\base;

class csvTest extends base
{

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
   * [testEnclosure description]
   */
  public function testPartialWriteouts(): void {

    $target = new \codename\core\io\target\buffered\file\csv('csv_test_partial_writeouts', [
      'delimiter'   => ';',
      'buffer_size' => 2,
      'config' => [
      ],
      'mapping' => [
        'key1' => [  ],
        'key2' => [  ],
        'key3' => [  ],
        'key4' => [  ],
      ]
    ]);

    $samples = $this->getSampleData();
    foreach($samples as $sample) {
      $target->store($sample);
    }
    $target->finish();

    $files = $target->getFileResultArray();
    $this->assertCount(1, $files);

    $filepath = $files[0]->get();
    $datasource = new \codename\core\io\datasource\csv($filepath, [
      'delimiter' => ';',
      'autodetect_utf8_bom' => true,
    ]);
    $res = [];
    foreach($datasource as $r) {
      $res[] = $r;
    }

    unlink($filepath);

    $this->assertEquals($samples, $res);
  }

  /**
   * [testPartialWriteoutsWithSplitting description]
   */
  public function testPartialWriteoutsWithSplitting(): void {

    $target = new \codename\core\io\target\buffered\file\csv('csv_test_partial_writeouts', [
      'delimiter'   => ';',
      'buffer_size' => 2,
      'split_count' => 3,
      'config' => [
      ],
      'mapping' => [
        'key1' => [  ],
        'key2' => [  ],
        'key3' => [  ],
        'key4' => [  ],
      ]
    ]);

    $samples = $this->getSampleData();
    foreach($samples as $sample) {
      $target->store($sample);
    }
    $target->finish();

    $files = $target->getFileResultArray();

    $this->assertCount(1, $files);

    $filepath = $files[0]->get();
    $datasource = new \codename\core\io\datasource\csv($filepath, [
      'delimiter' => ';',
      'autodetect_utf8_bom' => true,
    ]);
    $res = [];
    foreach($datasource as $r) {
      $res[] = $r;
    }

    unlink($filepath);

    $this->assertEquals($samples, $res);
  }

  /**
   * [testEnclosure description]
   */
  public function testEnclosure(): void {

    $target = new \codename\core\io\target\buffered\file\csv('csv_test', [
      'delimiter' => ';',
      'enclosure' => null,
      'config' => [
      ],
      'mapping' => [
        'key1' => [  ],
        'key2' => [  ],
        'key3' => [  ],
        'key4' => [  ],
      ]
    ]);

    $samples = $this->getSampleData();
    foreach($samples as $sample) {
      $target->store($sample);
    }
    $target->finish();

    $files = $target->getFileResultArray();
    $this->assertCount(1, $files);

    $filepath = $files[0]->get();
    $datasource = new \codename\core\io\datasource\csv($filepath, [
      'delimiter' => ';',
      'autodetect_utf8_bom' => true,
    ]);
    $res = [];
    foreach($datasource as $r) {
      $res[] = $r;
    }

    unlink($filepath);

    $this->assertEquals($samples, $res);

  }

  /**
   * [testNumericIndexes description]
   */
  public function testNumericIndexes(): void {

    $target = new \codename\core\io\target\buffered\file\csv('csv_test', [
      'delimiter' => ';',
      'numeric_indexes' => true,
      'numeric_index_start' => 1,
      'config' => [
      ],
      'mapping' => [
        '1' => [ 'column' => 'key1' ],
        '2' => [ 'column' => 'key2' ],
        '3' => [ 'column' => 'key3' ],
        '4' => [ 'column' => 'key4' ],
      ]
    ]);

    $samples = $this->getSampleData();
    foreach($samples as $sample) {
      $target->store($sample);
    }
    $target->finish();

    $files = $target->getFileResultArray();
    $this->assertCount(1, $files);

    $filepath = $files[0]->get();
    $datasource = new \codename\core\io\datasource\csv($filepath, [
      'delimiter' => ';',
      'autodetect_utf8_bom' => true,
    ]);
    $res = [];
    foreach($datasource as $r) {
      $res[] = $r;
    }

    unlink($filepath);

    $this->assertEquals($samples, $res);

  }

  /**
   * [testNumericIndexes description]
   */
  public function testNumericIndexesInvalidIndex(): void {
    $this->expectException(\codename\core\exception::class);
    $this->expectExceptionMessage('EXCEPTION_CORE_IO_TARGET_NUMERIC_INDEX_INVALID');

    $target = new \codename\core\io\target\buffered\file\csv('csv_test', [
      'delimiter' => ';',
      'numeric_indexes' => true,
      'numeric_index_start' => 0,
      'config' => [
      ],
      'mapping' => [
        '1' => [ 'column' => 'key1' ],
        '2' => [ 'column' => 'key2' ],
        '3' => [ 'column' => 'key3' ],
        '4' => [ 'column' => 'key4' ],
      ]
    ]);

    $samples = $this->getSampleData();
    foreach($samples as $sample) {
      $target->store($sample);
    }
    $target->finish();

    $files = $target->getFileResultArray();
    $this->assertCount(1, $files);

    $filepath = $files[0]->get();
    $datasource = new \codename\core\io\datasource\csv($filepath, [
      'delimiter' => ';',
      'autodetect_utf8_bom' => true,
    ]);
    $res = [];
    foreach($datasource as $r) {
      $res[] = $r;
    }

    unlink($filepath);

    $this->assertEquals($samples, $res);

  }

  /**
   * [testNumericIndexes description]
   */
  public function testNumericIndexesWithoutEnclosure(): void {

    $target = new \codename\core\io\target\buffered\file\csv('csv_test', [
      'delimiter' => ';',
      'enclosure' => null,
      'numeric_indexes' => true,
      'numeric_index_start' => 1,
      'config' => [
      ],
      'mapping' => [
        '1' => [ 'column' => 'key1' ],
        '2' => [ 'column' => 'key2' ],
        '3' => [ 'column' => 'key3' ],
        '4' => [ 'column' => 'key4' ],
      ]
    ]);

    $samples = $this->getSampleData();
    foreach($samples as $sample) {
      $target->store($sample);
    }
    $target->finish();

    $files = $target->getFileResultArray();
    $this->assertCount(1, $files);

    $filepath = $files[0]->get();
    $datasource = new \codename\core\io\datasource\csv($filepath, [
      'delimiter' => ';',
      'autodetect_utf8_bom' => true,
    ]);
    $res = [];
    foreach($datasource as $r) {
      $res[] = $r;
    }

    unlink($filepath);

    $this->assertEquals($samples, $res);

  }

  /**
   * [testUnsupportedEncoding description]
   */
  public function testUnsupportedEncoding(): void {
    $this->expectException(\codename\core\exception::class);
    $this->expectExceptionMessage('EXCEPTION_TARGET_BUFFERED_FILE_CSV_UNSUPPORTED_REENCODE');

    $target = new \codename\core\io\target\buffered\file\csv('csv_test', [
      'delimiter' => ';',
      'enclosure' => null,
      'encoding' => 'error',
      'config' => [
      ],
      'mapping' => [
        'key1' => [ ],
        'key2' => [ ],
        'key3' => [ ],
        'key4' => [ ],
      ]
    ]);
    $target->store([
      'key1' => 'value1',
      'key2' => 2,
      'key3' => 3.1415,
      'key4' => null,
    ]);
    $target->finish();

  }

}
