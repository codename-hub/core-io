<?php
namespace codename\core\io\tests\transform\compare;

class datetimeTest extends \codename\core\io\tests\transform\abstractTransformTest
{

  /**
   * Testing transforms for Erors
   */
  public function testValueValidCase1(): void {
    $transform = $this->getTransform('compare_datetime', [
      'set_time_to_null'  => true,
      'left'              => [
        'source'        => 'source',
        'field'         => 'example_left_source_field',
        'source_format' => 'Y-m-d',
        'modify'        => '+1 day',
      ],
      'right'              => [
        'source'        => 'source',
        'field'         => 'example_right_source_field',
        'source_format' => 'Y-m-d',
        'modify'        => '+1 day',
      ],
    ]);
    $result = $transform->transform([
      'example_left_source_field'   => '2021-04-16',
      'example_right_source_field'  => '2021-04-16',
    ]);
    // Make sure it stays an array
    $this->assertEquals(0, $result);
  }

  /**
   * Testing transforms for Erors
   */
  public function testValueValidCase2(): void {
    $transform = $this->getTransform('compare_datetime', [
      'set_time_to_null'  => true,
      'left'              => [
        'source'        => 'source',
        'field'         => 'example_left_source_field',
        'source_format' => 'Y-m-d',
      ],
      'right'              => [
        'source'        => 'source',
        'field'         => 'example_right_source_field',
        'source_format' => 'Y-m-d',
      ],
    ]);
    $result = $transform->transform([
      'example_left_source_field'   => '2021-04-16',
      'example_right_source_field'  => '2021-04-17',
    ]);
    // Make sure it stays an array
    $this->assertEquals(-1, $result);
  }

  /**
   * Testing transforms for Erors
   */
  public function testValueValidCase3(): void {
    $transform = $this->getTransform('compare_datetime', [
      'set_time_to_null'  => true,
      'left'              => [
        'source'        => 'source',
        'field'         => 'example_left_source_field',
        'source_format' => 'Y-m-d',
      ],
      'right'              => [
        'source'        => 'source',
        'field'         => 'example_right_source_field',
        'source_format' => 'Y-m-d',
      ],
    ]);
    $result = $transform->transform([
      'example_left_source_field'   => '2021-04-17',
      'example_right_source_field'  => '2021-04-16',
    ]);
    // Make sure it stays an array
    $this->assertEquals(1, $result);
  }

  /**
   * Test Spec output (simple case)
   */
  public function testSpecification(): void {
    $transform = $this->getTransform('compare_datetime', [
      'source'      => 'source',
      'field'       => 'example_source_field',
      'value'       => 'example_source_field',
    ]);
    $this->assertEquals(
      [
        'type'    => 'transform',
        'source'  => [ 'TODO_SPEC' ]
      ],
      $transform->getSpecification()
    );
  }

}
