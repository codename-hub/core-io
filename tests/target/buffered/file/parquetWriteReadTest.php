<?php
namespace codename\core\tests\target\buffered\file;

use codename\core\tests\target\abstractWriteReadTest;

class parquetWriteReadTest extends abstractWriteReadTest {

  /**
   * [testOtherDatatypesUsingAutoguess description]
   */
  public function testOtherDatatypesUsingAutoguess(): void {

    if(!extension_loaded('gmp')) {
      $this->addWarning('GMP extension required for working with arbitrary precision numbers in Parquet.');
      return;
    }

    $this->testWriteReadTarget([
      'mapping' => [
        'key_string'            => [ ],
        'key_string_nullable'   => [ ],
        'key_integer'           => [ ],
        'key_integer_nullable'  => [ ],
        'key_bool'              => [ ],
        'key_bool_nullable'     => [ ],
        'key_float'             => [ ],
        'key_double_nullable'   => [ ],
        'key_datetime'          => [ ],
        'key_datetime_nullable' => [ ],
      ]
    ], [
      [
        'key_string'            => 'abc',
        'key_string_nullable'   => 'abc',
        'key_integer'           => 123,
        'key_integer_nullable'  => 123,
        'key_bool'              => true,
        'key_bool_nullable'     => true,
        'key_float'             => 1.23,
        'key_double_nullable'   => 1.2386768767676,
        'key_datetime'          => new \DateTimeImmutable('now'),
        'key_datetime_nullable' => new \DateTimeImmutable('now'),
      ],
      [
        'key_string'            => 'def',
        'key_string_nullable'   => null,
        'key_integer'           => 123,
        'key_integer_nullable'  => null,
        'key_bool'              => false,
        'key_bool_nullable'     => null,
        'key_float'             => 2.34,
        'key_double_nullable'   => 2.3465656565656,
        'key_datetime'          => new \DateTimeImmutable('now'),
        'key_datetime_nullable' => null,
      ],
      [
        'key_string'            => 'ghi',
        'key_string_nullable'   => 'ghi',
        'key_integer'           => 234,
        'key_integer_nullable'  => 2,
        'key_bool'              => true,
        'key_bool_nullable'     => true,
        'key_float'             => 3.45,
        'key_double_nullable'   => null,
        'key_datetime'          => new \DateTimeImmutable('now'),
        'key_datetime_nullable' => new \DateTimeImmutable('now'),
      ]
    ]);
  }

  /**
   * [testOtherDatatypesUsingAutoguessFailByNotUniqueTypes description]
   */
  public function testOtherDatatypesUsingAutoguessFailByNotUniqueTypes(): void {
    $this->expectException(\codename\core\exception::class);
    $this->expectExceptionMessage('NOT UNIQUE TYPES');
    $this->testWriteReadTarget([
      'mapping' => [
        'key_string'            => [ ],
        'key_integer_nullable'  => [ ],
        'key_datetime'          => [ ],
        'key_datetime_nullable' => [ ],
      ]
    ], [
      [
        'key_string'            => 'abc',
        'key_integer_nullable'  => 123,
        'key_datetime'          => new \DateTimeImmutable('now'),
      ],
      [
        'key_string'            => 'def',
        'key_integer_nullable'  => null,
        'key_datetime'          => new \DateTimeImmutable('now'),
      ],
      [
        'key_string'            => 333, // NOTE: suddenly provide a non-string, but instead integer value
        'key_integer_nullable'  => 123,
        'key_datetime'          => new \DateTimeImmutable('now'),
      ]
    ]);
  }

  /**
   * [testExtraExplicitDatatypes description]
   */
  public function testExtraExplicitDatatypes(): void {
    $this->testWriteReadTarget([
      'mapping' => [
        'key_datetime'  => [ 'php_class' => \DateTimeImmutable::class,  'is_nullable' => true, 'datetime_format' => 'Y-m-d H:i:s',  ],
        'key_decimal'   => [ 'php_type'  => 'decimal',                  'is_nullable' => true, 'precision' => 24, 'scale' => 16     ],
      ]
    ], [
      [
        'key_decimal'           => '0.000000022122',
        'key_datetime'          => new \DateTimeImmutable('now'),
      ],
      [
        'key_decimal'           => '1231234.442424242',
        'key_datetime'          => null,
      ],
      [
        'key_decimal'           => null,
        'key_datetime'          => new \DateTimeImmutable('now'),
      ]
    ]);
  }

  /**
   * @inheritDoc
   */
  protected function compareData(
    \codename\core\io\target $target,
    array $samples
  ): void {
    $result = $this->readTargetData($target);
    $this->assertCount(count($samples), $result);

    // We're overriding the regular comparison method
    // to allow hooking into decimal comparisons,
    // where we need additional precision parameters
    foreach($samples as $index => $sample) {
      $resultRow = $result[$index];

      foreach($sample as $key => $value) {
        if(is_numeric($value) && is_string($value)) {
          // delta comparison of decimals
          $this->assertEquals(0, bccomp($value, $resultRow[$key]), 24);
        } else {
          $this->assertEquals($value, $resultRow[$key]);
        }
      }
    }
  }

  /**
   * @inheritDoc
   */
  protected function getWriteReadTargetInstance(array $configOverride = []): \codename\core\io\target
  {
    return new \codename\core\io\target\buffered\file\parquet('parquet_test', array_replace([
      'mapping' => [
        'key1' => [ 'php_type' => 'string'  ],
        'key2' => [ 'php_type' => 'integer' ],
        'key3' => [ 'php_type' => 'double'  ],
        'key4' => [ 'php_type' => 'string', 'is_nullable' => true  ],
      ]
    ], $configOverride));
  }

  /**
   * @inheritDoc
   */
  protected function readTargetData(\codename\core\io\target $target): array {
    $files = $target->getFileResultArray();
    $this->assertCount(1, $files);
    foreach($files as $file) {
      $filepath = $file->get();
      $datasource = new \codename\core\io\datasource\parquet($filepath);

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
