<?php
namespace codename\core\io\tests\transform\trim;

class leftTest extends \codename\core\io\tests\transform\abstractTransformTest
{

  /**
   * Testing transforms for Erors
   */
  public function testValueValid(): void {
    $transform = $this->getTransform('trim_left', [
      'source'  => 'source',
      'field'   => 'example_source_field',
    ]);
    $result = $transform->transform([
      'example_source_field'  => ' example '
    ]);
    // Make sure it stays an array
    $this->assertEquals('example ', $result);
  }

  /**
   * Testing transforms for Erors
   */
  public function testValueValidWithMask(): void {
    $transform = $this->getTransform('trim_left', [
      'source'          => 'source',
      'field'           => 'example_source_field',
      'character_mask'  => '0',
    ]);
    $result = $transform->transform([
      'example_source_field'  => ' example '
    ]);
    // Make sure it stays an array
    $this->assertEquals(' example ', $result);
  }

  /**
   * Test Spec output (simple case)
   */
  public function testSpecification(): void {
    $transform = $this->getTransform('trim_left', [
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
