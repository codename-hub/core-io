<?php
namespace codename\core\io\tests\transform\convert;

class booleanTest extends \codename\core\io\tests\transform\abstractTransformTest
{

  /**
   * Testing transforms for Erors
   */
  public function testValueTrueCase1(): void {
    $transform = $this->getTransform('convert_boolean', [
      'source'      => 'source',
      'field'       => 'example_source_field',
    ]);
    $result = $transform->transform([
      'example_source_field'  => true,
    ]);
    // Make sure it stays an array
    $this->assertTrue($result);
  }

  /**
   * Testing transforms for Erors
   */
  public function testValueTrueCase2(): void {
    $transform = $this->getTransform('convert_boolean', [
      'source'      => 'source',
      'field'       => 'example_source_field',
    ]);
    $result = $transform->transform([
      'example_source_field'  => 1,
    ]);
    // Make sure it stays an array
    $this->assertTrue($result);
  }

  /**
   * Testing transforms for Erors
   */
  public function testValueTrueCase3(): void {
    $transform = $this->getTransform('convert_boolean', [
      'source'      => 'source',
      'field'       => 'example_source_field',
    ]);
    $result = $transform->transform([
      'example_source_field'  => 'true',
    ]);
    // Make sure it stays an array
    $this->assertTrue($result);
  }

  /**
   * Testing transforms for Erors
   */
  public function testValueTrueCase4(): void {
    $transform = $this->getTransform('convert_boolean', [
      'source'      => 'source',
      'field'       => 'example_source_field',
    ]);
    $result = $transform->transform([
      'example_source_field'  => '1',
    ]);
    // Make sure it stays an array
    $this->assertTrue($result);
  }

  /**
   * Testing transforms for Erors
   */
  public function testValueFalseCase1(): void {
    $transform = $this->getTransform('convert_boolean', [
      'source'      => 'source',
      'field'       => 'example_source_field',
    ]);
    $result = $transform->transform([
      'example_source_field'  => false,
    ]);
    // Make sure it stays an array
    $this->assertFalse($result);
  }

  /**
   * Testing transforms for Erors
   */
  public function testValueFalseCase2(): void {
    $transform = $this->getTransform('convert_boolean', [
      'source'      => 'source',
      'field'       => 'example_source_field',
    ]);
    $result = $transform->transform([
      'example_source_field'  => 0,
    ]);
    // Make sure it stays an array
    $this->assertFalse($result);
  }

  /**
   * Testing transforms for Erors
   */
  public function testValueFalseCase3(): void {
    $transform = $this->getTransform('convert_boolean', [
      'source'      => 'source',
      'field'       => 'example_source_field',
    ]);
    $result = $transform->transform([
      'example_source_field'  => 'false',
    ]);
    // Make sure it stays an array
    $this->assertFalse($result);
  }

  /**
   * Testing transforms for Erors
   */
  public function testValueFalseCase4(): void {
    $transform = $this->getTransform('convert_boolean', [
      'source'      => 'source',
      'field'       => 'example_source_field',
    ]);
    $result = $transform->transform([
      'example_source_field'  => '0',
    ]);
    // Make sure it stays an array
    $this->assertFalse($result);
  }

  /**
   * Testing transforms for Erors
   */
  public function testValueRequired(): void {
    $transform = $this->getTransform('convert_boolean', [
      'source'      => 'source',
      'field'       => 'example_source_field',
      'required'    => true
    ]);
    $result = $transform->transform([
      'example_source_field'  => null,
    ]);
    // Make sure it stays an array
    $this->assertNull($result);

    $errors = $transform->getErrors();
    $this->assertNotEmpty($errors);
    $this->assertCount(1, $errors);
    $this->assertEquals('convert_boolean', $errors[0]['__IDENTIFIER'] );
    $this->assertEquals('TRANSFORM.MISSING_VALUE', $errors[0]['__CODE'] );
  }

  /**
   * Testing transforms for Erors
   */
  public function testValueInvalidValue(): void {
    $transform = $this->getTransform('convert_boolean', [
      'source'      => 'source',
      'field'       => 'example_source_field'
    ]);
    $result = $transform->transform([
      'example_source_field'  => 'ABC',
    ]);
    // Make sure it stays an array
    $this->assertNull($result);

    $errors = $transform->getErrors();
    $this->assertNotEmpty($errors);
    $this->assertCount(1, $errors);
    $this->assertEquals('convert_boolean', $errors[0]['__IDENTIFIER'] );
    $this->assertEquals('TRANSFORM.INVALID_VALUE', $errors[0]['__CODE'] );
  }

  /**
   * Test Spec output (simple case)
   */
  public function testSpecification(): void {
    $transform = $this->getTransform('convert_boolean', [
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
