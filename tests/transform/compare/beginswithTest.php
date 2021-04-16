<?php
namespace codename\core\io\tests\transform\compare;

class beginswithTest extends \codename\core\io\tests\transform\abstractTransformTest
{

  /**
   * Testing transforms for Erors
   */
  public function testValueValid(): void {
    $transform = $this->getTransform('compare_beginswith', [
      'source'      => 'source',
      'field'       => 'example_source_field',
      'value'       => 'test',
    ]);
    $result = $transform->transform([
      'example_source_field'  => 'testvalue',
    ]);
    // Make sure it stays an array
    $this->assertTrue($result);
  }

  /**
   * Test Spec output (simple case)
   */
  public function testSpecification(): void {
    $transform = $this->getTransform('compare_beginswith', [
      'source'      => 'source',
      'field'       => 'example_source_field',
      'value'       => 'example_source_field',
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
