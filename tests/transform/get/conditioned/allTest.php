<?php
namespace codename\core\io\tests\transform\get\conditioned;

class allTest extends \codename\core\io\tests\transform\abstractTransformTest
{

  /**
   * Testing transforms for Erors
   */
  public function testValueValidCase1(): void {
    $transform = $this->getTransform('get_conditioned_all', [
      'return'    => true,
      'condition' => [
        [
          'source'    => 'source',
          'field'     => 'example_source_field',
          'operator'  => '=',
          'value'     => 123,
        ],
        [
          'source'    => 'source',
          'field'     => 'example_source_field2',
          'operator'  => '!=',
          'value'     => 456,
        ],
      ],
    ]);
    $result = $transform->transform([
      'example_source_field'  => 123,
      'example_source_field2' => 123,
    ]);
    $this->assertEquals(true, $result);
  }

  /**
   * Testing transforms for Erors
   */
  public function testValueValidCase2(): void {
    $transform = $this->getTransform('get_conditioned_all', [
      'required'  => true,
      'condition' => [
        [
          'source'    => 'source',
          'field'     => 'example_source_field',
          'operator'  => '<>',
          'value'     => 123,
        ],
        [
          'source'    => 'source',
          'field'     => 'example_source_field2',
          'operator'  => '=',
          'value'     => 456,
        ],
      ],
    ]);
    $result = $transform->transform([
      'example_source_field'  => 123,
      'example_source_field2' => 123,
    ]);
    $this->assertEquals(null, $result);

    $errors = $transform->getErrors();
    $this->assertNotEmpty($errors);
    $this->assertCount(1, $errors);
    $this->assertEquals('GET_CONDITIONED_ALL_NOMATCH', $errors[0]['__IDENTIFIER'] );
    $this->assertEquals('TRANSFORM.0', $errors[0]['__CODE'] );
  }

  /**
   * Testing transforms for Erors
   */
  public function testValueValidCase3(): void {
    $transform = $this->getTransform('get_conditioned_all', [
      'default'   => 'example',
      'return'    => true,
      'condition' => [
        [
          'source'    => 'source',
          'field'     => 'example_source_field',
          'operator'  => '<>',
          'value'     => 123,
        ],
        [
          'source'    => 'source',
          'field'     => 'example_source_field2',
          'operator'  => '!=',
          'value'     => 123,
        ],
      ],
    ]);
    $result = $transform->transform([
      'example_source_field'  => 123,
      'example_source_field2' => 123,
    ]);
    $this->assertEquals('example', $result);
  }

}
