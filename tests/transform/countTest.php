<?php
namespace codename\core\io\tests\transform;

class countTest extends abstractTransformTest
{
  /**
   * Testing transforms for Erors
   */
  public function testValueIsNull(): void {
    $transform = $this->getTransform('count', [
      'source'  => 'source',
      'field'   => 'example_source_field',
    ]);
    $result = $transform->transform([
      'example_source_field'  => null
    ]);
    // Make sure it stays an array
    $this->assertEquals(null, $result);
  }

  /**
   * Testing transforms for Erors
   */
  public function testValueValid(): void {
    $transform = $this->getTransform('count', [
      'source'  => 'source',
      'field'   => 'example_source_field',
    ]);
    $result = $transform->transform([
      'example_source_field'  => [ 'test', 'test2' ]
    ]);
    // Make sure it stays an array
    $this->assertEquals(2, $result);
  }

  /**
   * Test Spec output (simple case)
   */
  public function testSpecification(): void {
    $transform = $this->getTransform('count', [
      'source'  => 'source',
      'field'   => 'example_source_field',
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
