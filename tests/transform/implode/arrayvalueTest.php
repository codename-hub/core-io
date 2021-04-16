<?php
namespace codename\core\io\tests\transform\implode;

class arrayvalueTest extends \codename\core\io\tests\transform\abstractTransformTest
{

  /**
   * Testing transforms for Erors
   */
  public function testValueValid(): void {
    $transform = $this->getTransform('implode_arrayvalue', [
      'glue'        => ',',
      'source'      => 'source',
      'field'       => 'example_source_field',
    ]);
    $result = $transform->transform([
      'example_source_field'  => [
        'test',
        'test1',
        'test2',
      ]
    ]);
    // Make sure it stays an array
    $this->assertEquals('test,test1,test2', $result);
  }

  /**
   * Test Spec output (simple case)
   */
  public function testSpecification(): void {
    $transform = $this->getTransform('implode_arrayvalue', [
      'glue'        => ',',
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
