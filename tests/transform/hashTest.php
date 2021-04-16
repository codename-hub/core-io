<?php
namespace codename\core\io\tests\transform;

class hashTest extends abstractTransformTest
{

  /**
   * Testing transforms for Erors
   */
  public function testConstructInvalid(): void {
    $this->expectException(\codename\core\exception::class);
    $this->expectExceptionMessage('EXCEPTION_TRANSFORM_HASH_NO_ALGORITHM_SPECIFIED');

    $transform = $this->getTransform('hash', [
      'source'    => 'source',
      'field'     => 'example_source_field',
    ]);
    $result = $transform->transform([
      'example_source_field'  => null
    ]);
  }

  /**
   * Testing transforms for Erors
   */
  public function testValueValid(): void {
    $transform = $this->getTransform('hash', [
      'source'    => 'source',
      'field'     => 'example_source_field',
      'algorithm' => 'md5',
    ]);
    $result = $transform->transform([
      'example_source_field'  => 'test'
    ]);
    // Make sure it stays an array
    $this->assertEquals(hash('md5', 'test'), $result);
  }

  /**
   * Test Spec output (simple case)
   */
  public function testSpecification(): void {
    $transform = $this->getTransform('hash', [
      'source'    => 'source',
      'field'     => 'example_source_field',
      'algorithm' => 'md5',
    ]);
    $this->assertEquals(
      [
        'type'    => 'transform',
        'source'  => [ 'source.example_source_field' ]
      ],
      $transform->getSpecification()
    );
  }
}
