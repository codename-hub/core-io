<?php
namespace codename\core\io\tests\transform\get\number;

class fractionTest extends \codename\core\io\tests\transform\abstractTransformTest
{

  /**
   * Testing transforms for Erors
   */
  public function testValueValidCase1(): void {
    $transform = $this->getTransform('get_number_fraction', [
      'source'    => 'source',
      'field'     => 'example_source_field',
    ]);
    $result = $transform->transform([
      'example_source_field'  => 123.456,
    ]);
    $this->assertEquals(456, $result);
  }

  /**
   * Testing transforms for Erors
   */
  public function testValueValidCase2(): void {
    $transform = $this->getTransform('get_number_fraction', [
      'source'          => 'source',
      'field'           => 'example_source_field',
      'fraction_digits' => 2
    ]);
    $result = $transform->transform([
      'example_source_field'  => 123.456,
    ]);
    $this->assertEquals(45, $result);
  }

  /**
   * Testing transforms for Erors
   */
  public function testValueIsNull(): void {
    $transform = $this->getTransform('get_number_fraction', [
      'source'    => 'source',
      'field'     => 'example_source_field',
      'required'  => true
    ]);
    $result = $transform->transform([
      'example_source_field'  => null,
    ]);
    $this->assertNull($result);

    $errors = $transform->getErrors();
    $this->assertNotEmpty($errors);
    $this->assertCount(1, $errors);
    $this->assertEquals('GET_NUMBER_REQUIRED', $errors[0]['__IDENTIFIER'] );
    $this->assertEquals('TRANSFORM.0', $errors[0]['__CODE'] );
  }

  /**
   * Testing transforms for Erors
   */
  public function testValueInvalid(): void {
    $this->expectException(\codename\core\exception::class);
    $this->expectExceptionMessage('EXCEPTION_CORE_IO_TRANSFORM_GET_NUMBER_FRACTION_NOT_NUMERIC');

    $transform = $this->getTransform('get_number_fraction', [
      'source'          => 'source',
      'field'           => 'example_source_field',
    ]);
    $result = $transform->transform([
      'example_source_field'  => 'example',
    ]);
  }

  /**
   * Test Spec output (simple case)
   */
  public function testSpecification(): void {
    $transform = $this->getTransform('get_number_fraction', [
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
