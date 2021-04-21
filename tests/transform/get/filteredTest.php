<?php
namespace codename\core\io\tests\transform\get;

class filteredTest extends \codename\core\io\tests\transform\abstractTransformTest
{

  /**
   * Testing transforms for Erors
   */
  public function testValueValid(): void {
    $transform = $this->getTransform('get_filtered', [
      'source'  => 'source',
      'field'   => 'example_source_field',
      'filter'  => [
        [
          'operator'  => '=',
          'value'     => 1,
        ]
      ]
    ]);
    $result = $transform->transform([
      'example_source_field'  => 1,
    ]);

    // Make sure it stays an array
    $this->assertEquals('1', $result );
  }

  /**
   * Testing transforms for Erors
   */
  public function testValueValidCase2(): void {
    $transform = $this->getTransform('get_filtered', [
      'source'  => 'source',
      'field'   => 'example_source_field',
      'filter'  => [
        [
          'operator'  => '!=',
          'value'     => 0,
        ]
      ]
    ]);
    $result = $transform->transform([
      'example_source_field'  => 1,
    ]);

    // Make sure it stays an array
    $this->assertEquals('1', $result );
  }

  /**
   * Testing transforms for Erors
   */
  public function testValueNoMatch(): void {
    $transform = $this->getTransform('get_filtered', [
      'source'  => 'source',
      'field'   => 'example_source_field',
      'filter'  => [
        [
          'operator'  => '=',
          'value'     => 0,
        ],
        [
          'operator'  => '!=',
          'value'     => 1,
        ],
        [
          'operator'  => '<>',
          'value'     => 1,
        ]
      ]
    ]);
    $result = $transform->transform([
      'example_source_field'  => 1,
    ]);

    // Make sure it stays an array
    $this->assertNull($result);
  }

  /**
   * Test Spec output (simple case)
   */
  public function testSpecification(): void {
    $transform = $this->getTransform('get_filtered', [
      'source'  => 'source',
      'field'   => 'example_source_field',
      'filter'  => [
        [
          'operator'  => '=',
          'value'     => 1,
        ]
      ]
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
