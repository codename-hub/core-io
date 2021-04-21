<?php
namespace codename\core\io\tests\transform\convert;

class numberformatTest extends \codename\core\io\tests\transform\abstractTransformTest
{

  /**
   * Testing transforms for Erors
   */
  public function testValueValidCase1(): void {
    $transform = $this->getTransform('convert_numberformat', [
      'source'                    => 'source',
      'field'                     => 'example_source_field',
      'style'                     => 'decimal',
      'locale'                    => 'en-EN',
    ]);
    $result = $transform->transform([
      'example_source_field'  => 1.234,
    ]);
    // Make sure it stays an array
    $this->assertEquals(1.234, $result);
  }

  /**
   * Testing transforms for Erors
   */
  public function testValueValidCase2(): void {
    $this->expectException(\codename\core\exception::class);
    $this->expectExceptionMessage('TRANSFORM_NUMBERFORMAT_CONFIG_LOCALE_INVALID_SOURCE');

    $transform = $this->getTransform('convert_numberformat', [
      'source'                    => 'source',
      'field'                     => 'example_source_field',
      'style'                     => [
        'source'                    => 'source',
        'field'                     => 'example_style_field',
      ],
      'locale'                    => [
        'source'                    => 'source',
        'field'                     => 'example_locale_field',
      ]
    ]);
    $result = $transform->transform([
      'example_source_field'  => 1.2345,
      'example_style_field'   => 'decimal',
      'example_locale_field'  => 'en-EN',
    ]);
    // Make sure it stays an array
    $this->assertEquals('1.2345', $result);
  }

  /**
   * Testing transforms for Erors
   */
  public function testValueValidCase3(): void {
    $this->expectException(\codename\core\exception::class);
    $this->expectExceptionMessage('TRANSFORM_NUMBERFORMAT_CONFIG_STYLE_INVALID_SOURCE');

    $transform = $this->getTransform('convert_numberformat', [
      'source'                    => 'source',
      'field'                     => 'example_source_field',
      'style'                     => [
        'source'                    => 'source',
        'field'                     => 'example_style_field',
      ],
      'locale'                    => 'en-EN'
    ]);
    $result = $transform->transform([
      'example_source_field'  => 1.2345,
    ]);
  }

  /**
   * Testing transforms for Erors
   */
  public function testValueValidCase4(): void {
    $transform = $this->getTransform('convert_numberformat', [
      'source'                    => 'source',
      'field'                     => 'example_source_field',
      'style'                     => 'decimal',
      'locale'                    => [
        'source'                    => 'option',
        'field'                     => 'example_locale_field',
      ],
    ]);

    $pipline = new \codename\core\io\pipeline(null, []);
    $pipline->setOptions([
      'example_style_field'   => 'decimal',
      'example_locale_field'  => 'en-EN',
    ]);
    $transform->setPipelineInstance($pipline);

    $result = $transform->transform([
      'example_source_field'  => 1.2345,
    ]);
    // Make sure it stays an array
    $this->assertEquals('1.2345', $result);
  }

  /**
   * Testing transforms for Erors
   */
  public function testValueValidCase5(): void {
    $transform = $this->getTransform('convert_numberformat', [
      'source'                    => 'source',
      'field'                     => 'example_source_field',
      'style'                     => [
        'source'                    => 'option',
        'field'                     => 'example_style_field',
      ],
      'locale'                    => [
        'source'                    => 'option',
        'field'                     => 'example_locale_field',
      ]
    ]);

    $pipline = new \codename\core\io\pipeline(null, []);
    $pipline->setOptions([
      'example_style_field'   => 'decimal',
      'example_locale_field'  => 'en-EN',
    ]);
    $transform->setPipelineInstance($pipline);

    $result = $transform->transform([
      'example_source_field'  => 1.2345,
    ]);
    // Make sure it stays an array
    $this->assertEquals('1.2345', $result);
  }

  /**
   * Testing transforms for Erors
   */
  public function testValueWrongStyle(): void {
    $this->expectException(\codename\core\exception::class);
    $this->expectExceptionMessage('EXCEPTION_CORE_IO_TRANSFORM_CONVERT_NUMBERFORMAT_INVALID_TYPE');

    $transform = $this->getTransform('convert_numberformat', [
      'source'                    => 'source',
      'field'                     => 'example_source_field',
      'style'                     => 'example',
      'locale'                    => 'en-EN'
    ]);
  }

  /**
   * Testing transforms for Erors
   */
  public function testValueInvalidParse(): void {
    $transform = $this->getTransform('convert_numberformat', [
      'source'                    => 'source',
      'field'                     => 'example_source_field',
      'style'                     => 'decimal',
      'locale'                    => 'en-EN'
    ]);

    $result = $transform->transform([
      'example_source_field'  => '123,2345',
    ]);
    // Make sure it stays an array
    $this->assertFalse($result);

    $errors = $transform->getErrors();
    $this->assertNotEmpty($errors);
    $this->assertCount(1, $errors);
    $this->assertEquals('convert_numberformat', $errors[0]['__IDENTIFIER'] );
    $this->assertEquals('TRANSFORM.INVALID_PARSE', $errors[0]['__CODE'] );
  }

  /**
   * Test Spec output (simple case)
   */
  public function testSpecification(): void {
    $transform = $this->getTransform('convert_numberformat', [
      'source'  => 'source',
      'field'   => 'example_source_field',
      'style'   => 'decimal',
      'locale'  => 'de-DE',
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
