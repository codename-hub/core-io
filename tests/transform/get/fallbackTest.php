<?php
namespace codename\core\io\tests\transform\get;

class fallbackTest extends \codename\core\io\tests\transform\abstractTransformTest
{

  /**
   * Testing transforms for Erors
   */
  public function testValueValid(): void {
    $transform = $this->getTransform('get_fallback', [
      'fallback'  => [
        [
          'source'  => 'source',
          'field'   => 'example_source_field',
        ],
        [
          'source'  => 'source',
          'field'   => 'example_source_field2',
        ],
      ],
    ]);
    $result = $transform->transform([
      'example_source_field'  => null,
      'example_source_field2' => 'example',
    ]);

    // Make sure it stays an array
    $this->assertEquals('example', $result );
  }

  /**
   * Testing transforms for Erors
   */
  public function testValueRequired(): void {
    $transform = $this->getTransform('get_fallback', [
      'required'  => true,
      'fallback'  => [
        [
          'source'  => 'source',
          'field'   => 'example_source_field',
        ],
        [
          'source'  => 'source',
          'field'   => 'example_source_field2',
        ],
      ],
    ]);
    $result = $transform->transform([
      'example_source_field'  => null,
      'example_source_field2' => null,
    ]);

    // Make sure it stays an array
    $this->assertNull($result);

    $errors = $transform->getErrors();
    $this->assertNotEmpty($errors);
    $this->assertCount(1, $errors);
    $this->assertEquals('VALUE_NULL', $errors[0]['__IDENTIFIER'] );
    $this->assertEquals('TRANSFORM.0', $errors[0]['__CODE'] );
  }

  /**
   * Test Spec output (simple case)
   */
  public function testSpecification(): void {
    $transform = $this->getTransform('get_fallback', [
      'fallback'  => [
        [
          'source'  => 'source',
          'field'   => 'example_source_field',
        ],
        [
          'source'  => 'source',
          'field'   => 'example_source_field2',
        ],
      ]
    ]);
    $this->assertEquals(
      [
        'type'    => 'transform',
        'source'  => [ 'source.example_source_field', 'source.example_source_field2' ]
      ],
      $transform->getSpecification()
    );
  }

}
