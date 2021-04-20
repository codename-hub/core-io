<?php
namespace codename\core\io\tests\transform\convert;

class encodingTest extends \codename\core\io\tests\transform\abstractTransformTest
{

  /**
   * Testing transforms for Erors
   */
  public function testValueValid(): void {
    $transform = $this->getTransform('convert_encoding', [
      'source'  => 'source',
      'field'   => 'example_source_field',
      'from'    => 'UTF-8',
      'to'      => 'ASCII',
    ]);
    $result = $transform->transform([
      'example_source_field'  => 'Täst',
    ]);
    // Make sure it stays an array
    $this->assertEquals(mb_convert_encoding('Täst', 'ASCII', 'UTF-8'), $result);
  }

  /**
   * Testing transforms for Erors
   */
  public function testValueIsNull(): void {
    $transform = $this->getTransform('convert_encoding', [
      'source'  => 'source',
      'field'   => 'example_source_field',
      'from'    => 'UTF-8',
      'to'      => 'ASCII',
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
    $this->expectException(\codename\core\WarningException::class);
    // $this->expectExceptionMessage('INVALID_OPERATOR');

    $transform = $this->getTransform('convert_encoding', [
      'source'  => 'source',
      'field'   => 'example_source_field',
      'from'    => 'example',
      'to'      => 'example',
    ]);
    $result = $transform->transform([
      'example_source_field'  => 'Täst',
    ]);
  }

  /**
   * Test Spec output (simple case)
   */
  public function testSpecification(): void {
    $transform = $this->getTransform('convert_encoding', [
      'source'          => 'source',
      'field'           => 'example_source_field',
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
