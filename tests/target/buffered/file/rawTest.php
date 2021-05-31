<?php
namespace codename\core\io\tests\target\buffered\file;

use codename\core\test\base;
use codename\core\test\overrideableApp;

class rawTest extends base
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
   * [testWrongPaddingMode description]
   */
  public function testWrongPaddingMode(): void {
    $this->expectException(\codename\core\exception::class);
    $this->expectExceptionMessage('EXCEPTION_TARGET_BUFFERED_FILE_RAW_PADDING_MODE_NOT_SUPPORTED');

    $target = new \codename\core\io\target\buffered\file\raw('raw_test', [
      'padding_string'  => ' ',
      'padding_mode'    => 'wrong',
      'mapping' => [
        'key1' => [ 'rowIndex' => 0, 'columnIndex' => 0, 'length' => 10 ],
        'key2' => [ 'rowIndex' => 0, 'columnIndex' => 1, 'length' => 10 ],
        'key3' => [ 'rowIndex' => 0, 'columnIndex' => 2, 'length' => 10 ],
        'key4' => [ 'rowIndex' => 0, 'columnIndex' => 3, 'length' => 10 ],
      ]
    ]);

  }

  /**
   * [testValueTooLong description]
   */
  public function testValueTooLong(): void {
    $this->expectException(\codename\core\exception::class);
    $this->expectExceptionMessage('EXCEPTION_TARGET_BUFFERED_FILE_RAW_VALUE_TOO_LONG');

    $target = new \codename\core\io\target\buffered\file\raw('raw_test', [
      'padding_string'  => ' ',
      'padding_mode'    => 'right',
      'mapping' => [
        'key1' => [ 'rowIndex' => 0, 'columnIndex' => 0, 'length' => 10 ],
        'key2' => [ 'rowIndex' => 0, 'columnIndex' => 1, 'length' => 10 ],
        'key3' => [ 'rowIndex' => 0, 'columnIndex' => 2, 'length' => 10 ],
        'key4' => [ 'rowIndex' => 0, 'columnIndex' => 3, 'length' => 10 ],
      ]
    ]);

    $samples = $this->getSampleData();
    foreach($samples as $sample) {
      $sample['key1'] = 'valuetoolong';
      $target->store($sample);
      break;
    }
    $target->finish();

  }

  /**
   * [testPaddingRight description]
   */
  public function testPaddingRight(): void {

    $target = new \codename\core\io\target\buffered\file\raw('raw_test', [
      'padding_string'  => ' ',
      'padding_mode'    => 'right',
      'encoding'        => 'UTF-8',
      'truncate'        => true,
      'split_count'     => 2,
      'mapping' => [
        'key1' => [ 'rowIndex' => 0, 'columnIndex' => 0, 'length' => 10 ],
        'key2' => [ 'rowIndex' => 0, 'columnIndex' => 1, 'length' => 10 ],
        'key3' => [ 'rowIndex' => 0, 'columnIndex' => 2, 'length' => 10 ],
        'key4' => [ 'rowIndex' => 0, 'columnIndex' => 3, 'length' => 10 ],
      ]
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
      $datasource = new \codename\core\io\datasource\raw($filepath, [
        'format' => [
          'trim' => true,
          'map' => [
            'key1' => [ 'type' => 'fixed', 'length' => 10 ],
            'key2' => [ 'type' => 'fixed', 'length' => 10 ],
            'key3' => [ 'type' => 'fixed', 'length' => 10 ],
            'key4' => [ 'type' => 'fixed', 'length' => 10 ],
          ],
        ],
      ]);

      foreach($datasource as $r) {
        $res[] = $r;
      }

      unlink($filepath);
    }

    $this->assertEquals($samples, $res);

  }

}
