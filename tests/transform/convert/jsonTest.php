<?php
namespace codename\core\io\tests\transform\convert;

class jsonTest extends \codename\core\io\tests\transform\abstractTransformTest
{

  /**
   * Testing transforms for Erors
   */
  public function testValueValidEncode(): void {
    $transform = $this->getTransform('convert_json', [
      'source'  => 'source',
      'field'   => 'example_source_field',
      'mode'    => 'encode',
    ]);
    $result = $transform->transform([
      'example_source_field'  => [ 'example' => true ],
    ]);
    // Make sure it stays an array
    $this->assertEquals(json_encode([ 'example' => true ]), $result);
  }

  /**
   * Testing transforms for Erors
   */
  public function testValueValidDecode(): void {
    $transform = $this->getTransform('convert_json', [
      'source'  => 'source',
      'field'   => 'example_source_field',
      'mode'    => 'decode',
    ]);
    $result = $transform->transform([
      'example_source_field'  => json_encode([ 'example' => true ]),
    ]);
    // Make sure it stays an array
    $this->assertEquals([ 'example' => true ], $result);
  }

  /**
   * Testing transforms for Erors
   */
  public function testValueIsNull(): void {
    $transform = $this->getTransform('convert_json', [
      'source'  => 'source',
      'field'   => 'example_source_field',
      'mode'    => 'encode',
    ]);
    $result = $transform->transform([
      'example_source_field'  => null,
    ]);
    // Make sure it stays an array
    $this->assertNull($result);
  }

  /**
   * Testing transforms for Erors
   */
  public function testValueInvalid(): void {
    $transform = $this->getTransform('convert_json', [
      'source'  => 'source',
      'field'   => 'example_source_field',
      'mode'    => 'example',
    ]);
    $result = $transform->transform([
      'example_source_field'  => 'example',
    ]);
    // Make sure it stays an array
    $this->assertEmpty($result);
    // $this->markTestSkipped();
  }

  /**
   * Test Spec output (simple case)
   */
  public function testSpecification(): void {
    $transform = $this->getTransform('convert_json', [
      'source'  => 'source',
      'field'   => 'example_source_field',
      'mode'    => 'encode',
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
