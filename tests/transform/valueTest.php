<?php
namespace codename\core\io\tests\transform;

class valueTest extends abstractTransformTest
{

  /**
   * Testing transforms for Erors
   */
  public function testValueValid(): void {
    $transform = $this->getTransform('value', [
      'value' => 'example',
    ]);
    $result = $transform->transform([
      'example_source_field'  => ' example '
    ]);
    // Make sure it stays an array
    $this->assertEquals('example', $result);
  }

  /**
   * Test Spec output (simple case)
   */
  public function testSpecification(): void {
    $transform = $this->getTransform('value', [
      'source'      => 'source',
      'field'       => 'example_source_field',
    ]);
    $this->assertEquals(
      [
        'type'    => 'transform',
        'source'  => [  ]
      ],
      $transform->getSpecification()
    );
  }

}
