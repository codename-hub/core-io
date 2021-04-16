<?php
namespace codename\core\io\tests\transform\pad;

class rightTest extends \codename\core\io\tests\transform\abstractTransformTest
{

  /**
   * Testing transforms for Erors
   */
  public function testValueValid(): void {
    $transform = $this->getTransform('pad_right', [
      'source'  => 'source',
      'field'   => 'example_source_field',
      'length'  => 10,
      'string'  => ' '
    ]);
    $result = $transform->transform([
      'example_source_field'  => 'example'
    ]);
    // Make sure it stays an array
    $this->assertEquals('example   ', $result);
  }

  /**
   * Test Spec output (simple case)
   */
  public function testSpecification(): void {
    $transform = $this->getTransform('pad_right', [
      'source'      => 'source',
      'field'       => 'example_source_field',
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
