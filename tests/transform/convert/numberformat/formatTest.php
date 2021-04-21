<?php
namespace codename\core\io\tests\transform\convert\numberformat;

class formatTest extends \codename\core\io\tests\transform\abstractTransformTest
{

  /**
   * Testing transforms for Erors
   */
  public function testValueValid(): void {
    $transform = $this->getTransform('convert_numberformat_format', [
      'source'                    => 'source',
      'field'                     => 'example_source_field',
      'style'                     => 'decimal',
      'locale'                    => 'en-EN',
      'fraction_digits'           => 2,
      'grouping_separator_symbol' => '',
    ]);
    $result = $transform->transform([
      'example_source_field'  => '1234.5678',
    ]);
    // Make sure it stays an array
    $this->assertEquals('1234.57', $result);
  }

  /**
   * Testing transforms for Erors
   */
  public function testValueIsNull(): void {
    $transform = $this->getTransform('convert_numberformat_format', [
      'source'                    => 'source',
      'field'                     => 'example_source_field',
      'style'                     => 'decimal',
      'locale'                    => 'en-EN',
      'fraction_digits'           => 2,
      'grouping_separator_symbol' => '',
      'required'                  => true,
    ]);
    $result = $transform->transform([
      'example_source_field'  => null,
    ]);
    // Make sure it stays an array
    $this->assertNull($result);

    $errors = $transform->getErrors();
    $this->assertNotEmpty($errors);
    $this->assertCount(1, $errors);
    $this->assertEquals('convert_numberformat_format', $errors[0]['__IDENTIFIER'] );
    $this->assertEquals('TRANSFORM.MISSING_VALUE', $errors[0]['__CODE'] );
  }

  /**
   * Testing transforms for Erors
   */
  public function testValueInvalidParse(): void {
    $transform = $this->getTransform('convert_numberformat_format', [
      'source'                    => 'source',
      'field'                     => 'example_source_field',
      'style'                     => 'decimal',
      'locale'                    => 'de_DE',
    ]);

    $result = $transform->transform([
      'example_source_field'  => '123###456###',
    ]);
    // Make sure it stays an array
    $this->assertEquals('123', $result);
  }

}
