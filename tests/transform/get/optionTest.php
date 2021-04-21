<?php
namespace codename\core\io\tests\transform\get;

class optionTest extends \codename\core\io\tests\transform\abstractTransformTest
{

  /**
   * Testing transforms for Erors
   */
  public function testFunctionExistsResetCache(): void {
    $transform = $this->getTransform('get_option', [
      'field'   => 'example_source_field',
    ]);
    $result = $transform->resetCache();

    // Make sure it stays an array
    $this->assertEmpty($result);
  }

  /**
   * Testing transforms for Erors
   */
  public function testFunctionExistsResetErrors(): void {
    $transform = $this->getTransform('get_option', [
      'field'   => 'example_source_field',
    ]);
    $result = $transform->resetErrors();

    // Make sure it stays an array
    $this->assertEmpty($result);
  }

  /**
   * Testing transforms for Erors
   */
  public function testValueValid(): void {
    $transform = $this->getTransform('get_option', [
      'field'   => 'example_source_field',
    ]);

    $pipline = new \codename\core\io\pipeline(null, []);
    $pipline->setOptions([
      'example_source_field'  => 'example',
    ]);
    $transform->setPipelineInstance($pipline);

    $result = $transform->transform([]);

    // Make sure it stays an array
    $this->assertEquals('example', $result );
  }

  /**
   * Testing transforms for Erors
   */
  public function testValueIsNull(): void {
    $transform = $this->getTransform('get_option', [
      'field'     => 'example_source_field',
      'required'  => true,
    ]);

    $pipline = new \codename\core\io\pipeline(null, []);
    $pipline->setOptions([
      'example_source_field'  => null,
    ]);
    $transform->setPipelineInstance($pipline);

    $result = $transform->transform([]);

    // Make sure it stays an array
    $this->assertNull($result);

    $errors = $transform->getErrors();
    $this->assertNotEmpty($errors);
    $this->assertCount(1, $errors);
    $this->assertEquals('OPTION_VALUE_NULL', $errors[0]['__IDENTIFIER'] );
    $this->assertEquals('TRANSFORM.0', $errors[0]['__CODE'] );
  }

  /**
   * Test Spec output (simple case)
   */
  public function testSpecification(): void {
    $transform = $this->getTransform('get_option', [
      'field'   => 'example_source_field',
    ]);
    $this->assertEquals(
      [
        'type'    => 'transform',
        'source'  => [ 'option.example_source_field' ]
      ],
      $transform->getSpecification()
    );
  }

}
