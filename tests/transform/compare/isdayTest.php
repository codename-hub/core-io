<?php
namespace codename\core\io\tests\transform\compare;

class isdayTest extends \codename\core\io\tests\transform\abstractTransformTest
{

  /**
   * Testing transforms for Erors
   */
  public function testValueValidCase1(): void {
    $transform = $this->getTransform('compare_isday', [
      'source'      => 'source',
      'field'       => 'example_source_field',
      'value'       => 'Sunday',
    ]);
    $result = $transform->transform([
      'example_source_field' => '2021-04-11',
    ]);
    // Make sure it stays an array
    $this->assertTrue($result);
  }

  /**
   * Testing transforms for Erors
   */
  public function testValueValidCase2(): void {
    $transform = $this->getTransform('compare_isday', [
      'source'      => 'source',
      'field'       => 'example_source_field',
      'value'       => [ 'Sunday' ],
    ]);
    $result = $transform->transform([
      'example_source_field' => '2021-04-11',
    ]);
    // Make sure it stays an array
    $this->assertTrue($result);
  }

  /**
   * Test Spec output (simple case)
   */
  public function testSpecification(): void {
    $transform = $this->getTransform('compare_isday', [
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
