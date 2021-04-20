<?php
namespace codename\core\io\tests\transform\get;

class strcaseTest extends \codename\core\io\tests\transform\abstractTransformTest
{

  /**
   * Testing transforms for Erors
   */
  public function testValueInvalidMode(): void {
    $this->expectException(\codename\core\exception::class);
    $this->expectExceptionMessage('INVALID_STRCASE_MODE');

    $transform = $this->getTransform('get_strcase', [
      'source'  => 'source',
      'field'   => 'example_source_field',
      'mode'    => 'example',
    ]);
    $result = $transform->transform([
      'example_source_field'  => 1,
    ]);
  }

  /**
   * Testing transforms for Erors
   */
  public function testValueValidCase1(): void {
    $transform = $this->getTransform('get_strcase', [
      'source'  => 'source',
      'field'   => 'example_source_field',
      'mode'    => 'lower',
    ]);
    $result = $transform->transform([
      'example_source_field'  => 'AbCdEfGhIjKlMnOpQrStUvWxYz',
    ]);

    // Make sure it stays an array
    $this->assertEquals('abcdefghijklmnopqrstuvwxyz', $result );
  }

  /**
   * Testing transforms for Erors
   */
  public function testValueValidCase2(): void {
    $transform = $this->getTransform('get_strcase', [
      'source'  => 'source',
      'field'   => 'example_source_field',
      'mode'    => 'upper',
    ]);
    $result = $transform->transform([
      'example_source_field'  => 'AbCdEfGhIjKlMnOpQrStUvWxYz',
    ]);

    // Make sure it stays an array
    $this->assertEquals('ABCDEFGHIJKLMNOPQRSTUVWXYZ', $result );
  }

  /**
   * Testing transforms for Erors
   */
  public function testValueIsNull(): void {
    $transform = $this->getTransform('get_strcase', [
      'source'  => 'source',
      'field'   => 'example_source_field',
      'mode'    => 'upper',
    ]);
    $result = $transform->transform([
      'example_source_field'  => null,
    ]);

    // Make sure it stays an array
    $this->assertNull($result );
  }


  /**
   * Test Spec output (simple case)
   */
  public function testSpecification(): void {
    $transform = $this->getTransform('get_strcase', [
      'source'  => 'source',
      'field'   => 'example_source_field',
      'mode'    => 'lower',
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
