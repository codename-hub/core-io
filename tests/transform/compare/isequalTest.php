<?php
namespace codename\core\io\tests\transform\compare;

class isequalTest extends \codename\core\io\tests\transform\abstractTransformTest
{

  /**
   * Testing transforms for Erors
   */
  public function testValueValid(): void {
    $transform = $this->getTransform('compare_isequal', [
      'source'      => 'source',
      'field'       => 'example_source_field',
      'value'       => 'hello',
    ]);
    $result = $transform->transform([
      'example_source_field' => 'hello',
    ]);
    // Make sure it stays an array
    $this->assertTrue($result);
  }

  /**
   * Test Spec output (simple case)
   */
  public function testSpecification(): void {
    $transform = $this->getTransform('compare_isequal', [
      'source'      => 'source',
      'field'       => 'example_source_field',
      'value'       => 1,
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
