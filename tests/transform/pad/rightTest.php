<?php
namespace codename\core\io\tests\transform\pad;

class rightTest extends \codename\core\io\tests\transform\abstractTransformTest
{

  /**
   * Testing transforms for Erors
   */
  public function testValueValid(): void {
    $transform = $this->getTransform('pad_right', [
      'source'  => 'source',
      'field'   => 'example_source_field',
      'length'  => 10,
      'string'  => ' '
    ]);
    $result = $transform->transform([
      'example_source_field'  => 'example'
    ]);
    $this->assertEquals('example   ', $result);
  }

  /**
   * [testNullValuePadding description]
   */
  public function testNullValuePadding(): void {
    $transform = $this->getTransform('pad_right', [
      'source'  => 'source',
      'field'   => 'example_source_field',
      'length'  => 10,
      'string'  => ' '
    ]);
    //
    // NOTE: at the moment, str_pad using a NULL input value
    // also pads the string, as it was an empty string.
    //
    $result = $transform->transform([
      'example_source_field'  => null
    ]);
    $this->assertEquals('          ', $result);
  }

  /**
   * [testEmptyStringPadding description]
   */
  public function testEmptyStringPadding(): void {
    $transform = $this->getTransform('pad_right', [
      'source'  => 'source',
      'field'   => 'example_source_field',
      'length'  => 10,
      'string'  => ' '
    ]);
    $result = $transform->transform([
      'example_source_field'  => ''
    ]);
    $this->assertEquals('          ', $result);
  }

  /**
   * [testValueExceedsPadLength description]
   */
  public function testValueExceedsPadLength(): void {
    $transform = $this->getTransform('pad_right', [
      'source'  => 'source',
      'field'   => 'example_source_field',
      'length'  => 10,
      'string'  => ' '
    ]);
    $result = $transform->transform([
      'example_source_field'  => 'exampleexample'
    ]);
    // Make sure the strings stays the same,
    // as we only pad, if there are characters missing
    $this->assertEquals('exampleexample', $result);
  }

  /**
   * Test Spec output (simple case)
   */
  public function testSpecification(): void {
    $transform = $this->getTransform('pad_right', [
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
