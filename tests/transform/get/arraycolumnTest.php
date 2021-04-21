<?php
namespace codename\core\io\tests\transform\get;

class arraycolumnTest extends \codename\core\io\tests\transform\abstractTransformTest
{

  /**
   * Testing transforms for Erors
   */
  public function testValueValidCase1(): void {
    $transform = $this->getTransform('get_arraycolumn', [
      'source'  => 'source',
      'field'   => 'example_source_field',
      'index'   => 'example',
    ]);
    $result = $transform->transform([
      'example_source_field'  => [
        [
          'example' => 'test'
        ]
      ],
    ]);
    // Make sure it stays an array
    // $this->assertTrue($result);
    $this->assertEquals([ 'test' ], $result);
  }

  /**
   * Testing transforms for Erors
   */
  public function testValueValidCase2(): void {
    $transform = $this->getTransform('get_arraycolumn', [
      'source'  => 'source',
      'field'   => 'example_source_field',
      'index'   => [
        'source'  => 'source',
        'field'   => 'example_index_field',
      ]
    ]);
    $result = $transform->transform([
      'example_index_field'   => 'example',
      'example_source_field'  => [
        [
          'example' => 'test'
        ]
      ],
    ]);
    // Make sure it stays an array
    // $this->assertTrue($result);
    $this->assertEquals([ 'test' ], $result);
  }

  /**
   * Testing transforms for Erors
   */
  public function testValueValidCase3(): void {
    $transform = $this->getTransform('get_arraycolumn', [
      'source'  => 'option',
      'field'   => 'example_source_field',
      'index'   => [
        'source'  => 'source',
        'field'   => 'example_index_field',
      ]
    ]);

    $pipline = new \codename\core\io\pipeline(null, []);
    $pipline->setOptions([
      'example_source_field'  => [
        [
          'example' => 'test'
        ]
      ],
    ]);
    $transform->setPipelineInstance($pipline);

    $result = $transform->transform([
      'example_index_field'   => 'example',
    ]);

    // Make sure it stays an array
    $this->assertEquals([ 'test' ], $result );
  }

  /**
   * Test Spec output (simple case)
   */
  public function testSpecification(): void {
    $transform = $this->getTransform('get_arraycolumn', [
      'source'  => 'source',
      'field'   => 'example_source_field',
      'index'   => 'example',
    ]);
    $this->assertEquals(
      [
        'type'    => 'transform',
        'source'  => [ 'source.example' ]
      ],
      $transform->getSpecification()
    );
  }

}
