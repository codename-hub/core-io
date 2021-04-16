<?php
namespace codename\core\io\tests\transform\calculate;

class divideTest extends \codename\core\io\tests\transform\abstractTransformTest
{

  /**
   * Testing transforms for Erors
   */
  public function testValueValid(): void {
    $transform = $this->getTransform('calculate_divide', [
      'factors'    => [
        [ 'source'  => 'source', 'field'   => 'example_source_field1' ],
        [ 'source'  => 'source', 'field'   => 'example_source_field2' ],
        1.2345,
      ],
      'precision' => 2,
    ]);
    $result = $transform->transform([
      'example_source_field1'  => 5.4321,
      'example_source_field2'  => 1.2345,
    ]);
    // Make sure it stays an array
    $this->assertEquals(bcdiv(bcdiv(5.4321, 1.2345, 2), 1.2345, 2), $result);
  }

  /**
   * Test Spec output (simple case)
   */
  public function testSpecification(): void {
    $transform = $this->getTransform('calculate_divide', [
      'factors'    => [
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
