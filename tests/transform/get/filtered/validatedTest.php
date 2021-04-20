<?php
namespace codename\core\io\tests\transform\get\filtered;

class validatedTest extends \codename\core\io\tests\transform\abstractTransformTest
{

  /**
   * Testing transforms for Erors
   */
  public function testValueValid(): void {
    $transform = $this->getTransform('get_filtered_validated', [
      'source'    => 'source',
      'field'     => 'example_source_field',
      'validator' => [
        'text_bic'
      ],
    ]);
    $result = $transform->transform([
      'example_source_field'  => 'GENODEF1BEB',
    ]);

    // Make sure it stays an array
    $this->assertEquals('GENODEF1BEB', $result );
  }

  /**
   * Testing transforms for Erors
   */
  public function testValueInvalid(): void {
    $transform = $this->getTransform('get_filtered_validated', [
      'source'    => 'source',
      'field'     => 'example_source_field',
      'validator' => 'text_bic',
    ]);
    $result = $transform->transform([
      'example_source_field'  => '12345678901',
    ]);

    // Make sure it stays an array
    $this->assertNull($result );

    $errors = $transform->getErrors();
    $this->assertNotEmpty($errors);
    $this->assertCount(1, $errors);
    $this->assertEquals('VALIDATION.VALUE_NOT_A_BIC', $errors[0]['__CODE'] );
  }

  /**
   * Testing transforms for Erors
   */
  public function testValueMissingValidator(): void {
    $this->expectException(\codename\core\exception::class);

    $transform = $this->getTransform('get_filtered_validated', [
      'source'    => 'source',
      'field'     => 'example_source_field',
    ]);
  }

}
