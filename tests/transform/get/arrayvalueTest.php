<?php
namespace codename\core\io\tests\transform\get;

class arrayvalueTest extends \codename\core\io\tests\transform\abstractTransformTest
{

  /**
   * Testing transforms for Erors
   */
  public function testValueValidCase1(): void {
    $transform = $this->getTransform('get_arrayvalue', [
      'source'  => 'source',
      'field'   => 'example_source_field',
      'index'   => 'example',
    ]);
    $result = $transform->transform([
      'example_source_field'  => [
        'example' => true
      ],
    ]);
    // Make sure it stays an array
    $this->assertTrue($result);
  }

  /**
   * Testing transforms for Erors
   */
  public function testValueValidCase2(): void {
    $transform = $this->getTransform('get_arrayvalue', [
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
        'example' => true
      ],
    ]);
    // Make sure it stays an array
    $this->assertTrue($result);
  }

  /**
   * Testing transforms for Erors
   */
  public function testValueValidCase3(): void {
    $transform = $this->getTransform('get_arrayvalue', [
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
        'example' => true
      ],
    ]);
    $transform->setPipelineInstance($pipline);

    $result = $transform->transform([
      'example_index_field'   => 'example',
    ]);

    // Make sure it stays an array
    $this->assertTrue($result);
  }

  /**
   * Testing transforms for Erors
   */
  public function testValueMissingValueCase1(): void {
    $transform = $this->getTransform('get_arrayvalue', [
      'source'    => 'source',
      'field'     => 'example_source_field',
      'index'     => 'example_index',
      'required'  => true,
    ]);
    $result = $transform->transform([
      'example_source_field'  => [
        'example' => 'test'
      ]
    ]);
    // Make sure it stays an array
    $this->assertNull($result, print_r($result, true));

    $errors = $transform->getErrors();
    $this->assertNotEmpty($errors);
    $this->assertCount(1, $errors);
    $this->assertEquals('GET_ARRAYVALUE_MISSING', $errors[0]['__IDENTIFIER'] );
    $this->assertEquals('TRANSFORM.0', $errors[0]['__CODE'] );
  }

  /**
   * Testing transforms for Erors
   */
  public function testValueMissingValueCase2(): void {
    $transform = $this->getTransform('get_arrayvalue', [
      'source'    => 'source',
      'field'     => 'example_source_field',
      'index'     => [
        'source'    => 'source',
        'field'     => 'example_index_field',
      ],
      'required'  => true,
    ]);
    $result = $transform->transform([
      'example_index'         => 'example_index',
      'example_source_field'  => [
        'example' => 'test'
      ]
    ]);
    // Make sure it stays an array
    $this->assertNull($result, print_r($result, true));

    $errors = $transform->getErrors();
    $this->assertNotEmpty($errors);
    $this->assertCount(1, $errors);
    $this->assertEquals('GET_ARRAYVALUE_MISSING', $errors[0]['__IDENTIFIER'] );
    $this->assertEquals('TRANSFORM.0', $errors[0]['__CODE'] );
  }

  /**
   * Test Spec output (simple case)
   */
  public function testSpecification(): void {
    $transform = $this->getTransform('get_arrayvalue', [
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
