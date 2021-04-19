<?php
namespace codename\core\io\tests\transform\calculate;

class sumTest extends \codename\core\io\tests\transform\abstractTransformTest
{

  /**
   * Testing transforms for Erors
   */
  public function testValueValid(): void {
    $transform = $this->getTransform('calculate_sum', [
      'fields'    => [
        [ 'source'  => 'source', 'field'   => 'example_source_field1' ],
        1.2345,
      ],
      'precision' => 2,
    ]);
    $result = $transform->transform([
      'example_source_field1'  => 1.2345,
    ]);
    // Make sure it stays an array
    $this->assertEquals(bcadd(1.2345, 1.2345, 2), $result);
  }

  /**
   * Test Spec output (simple case)
   */
  public function testSpecification(): void {
    $transform = $this->getTransform('calculate_sum', [
      'fields'    => [
        [ 'source'  => 'source', 'field'   => 'example_source_field1' ],
        'example_source_field2',
      ],
      'precision' => 2,
    ]);
    $this->assertEquals(
      [
        'type'    => 'transform',
        'source'  => [ 'source.example_source_field1' ]
      ],
      $transform->getSpecification()
    );
  }

}
