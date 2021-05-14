<?php
namespace codename\core\io\tests\target\buffered\file;

use codename\core\tests\base;
use codename\core\tests\overrideableApp;

class spreadsheetTest extends base
{
  /**
   * @inheritDoc
   */
  protected function setUp(): void
  {
    parent::setUp();
    overrideableApp::__injectApp([
      'vendor' => 'codename',
      'app' => 'core-io',
      'namespace' => '\\codename\\core\\io'
    ]);

    $app = static::createApp();

    $app->__setApp('targetbufferedfiletest');
    $app->__setVendor('codename');
    $app->__setNamespace('\\codename\\core\\io\\target\\buffered\\file');

    $app->getAppstack();
  }

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
   * [testEnclosure description]
   */
  public function testWrongWriter(): void {
    $this->expectException(\codename\core\exception::class);
    $this->expectExceptionMessage('INVALID_SPREADSHEET_FILE_FORMAT_SPECIFIED');

    $target = new \codename\core\io\target\buffered\file\spreadsheet('xlsx_test', [
      'use_writer'          => 'Dompdf',
      'mapping'             => [
        'key1' => [  ],
        'key2' => [  ],
        'key3' => [  ],
        'key4' => [  ],
      ],
    ]);

    $samples = $this->getSampleData();
    foreach($samples as $sample) {
      $target->store($sample);
    }
    $target->finish();

  }

  /**
   * [testWithTemplate description]
   */
  public function testWithTemplate(): void {

    $target = new \codename\core\io\target\buffered\file\spreadsheet('xlsx_test', [
      'use_writer'          => 'Xlsx',
      'use_template_file'   => 'tests/target/buffered/file/spreadsheetTest.xlsx',
      'encoding_utf8bom'    => true,
      'start_row'           => 3,
      'split_count'         => 2,
      'freeze'              => 'A2',
      'mapping'             => [
        'key1' => [ 'column' => 'A', 'setExplicitString' => true ],
        'key2' => [ 'column' => 'B', 'setExplicitString' => true ],
        'key3' => [ 'column' => 'C', 'formatCode' => '0.0000' ],
        'key4' => [ 'column' => 'D', 'setExplicitString' => true ],
      ],
    ]);

    $samples = $this->getSampleData();
    foreach($samples as $sample) {
      $target->store($sample, [ 'file_password' => '123456' ]);
    }
    $target->finish();

    $files = $target->getFileResultArray();
    $this->assertCount(2, $files);

    $res = [];
    foreach($files as $file) {
      $filepath = $file->get();
      $datasource = new \codename\core\io\datasource\spreadsheet($filepath, [
        'skip_rows'   => 3,
      ]);

      foreach($datasource as $r) {
        $res[] = $r;
      }

      unlink($filepath);
    }

    foreach($samples as $k => $sample) {
      $this->assertEquals(array_values(array_filter($sample)), array_values(array_filter($res[$k] ?? [])));
    }

  }

  /**
   * [testCsvConfig description]
   */
  public function testCsvConfig(): void {

    $target = new \codename\core\io\target\buffered\file\spreadsheet('csv_test', [
      'use_writer'          => 'Csv',
      'encoding_utf8bom'    => true,
      'start_row'           => 3,
      'split_count'         => 2,
      'freeze'              => 'A2',
      'mapping'             => [
        'key1' => [  ],
        'key2' => [  ],
        'key3' => [  ],
        'key4' => [  ],
      ],
      'config'             => [
        'decimal_separator'   => ',',
        'thousands_separator' => '.',
        'delimiter'           => ';',
        'enclosure'           => null,
      ],
    ]);

    $samples = $this->getSampleData();
    foreach($samples as $sample) {
      $target->store($sample);
    }
    $target->finish();

    $files = $target->getFileResultArray();
    $this->assertCount(2, $files);

    $res = [];
    foreach($files as $file) {
      $filepath = $file->get();
      $datasource = new \codename\core\io\datasource\spreadsheet($filepath, [
        'skip_rows'   => 3,
      ]);

      foreach($datasource as $r) {
        $res[] = $r;
      }

      unlink($filepath);
    }

    foreach($samples as $k => $sample) {
      $this->assertEquals(array_values(array_filter($sample)), array_values(array_filter($res[$k] ?? [])));
    }

  }

}
