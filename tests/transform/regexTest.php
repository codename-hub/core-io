<?php
namespace codename\core\io\tests\transform;

class regexTest extends abstractTransformTest
{
  /**
   * Testing transforms for Erors
   */
  public function testConstructInvalid(): void {
    $this->expectException(\codename\core\exception::class);
    $this->expectExceptionMessage('TRANSFORM_REGEX_INVALID_CONFIG');

    $transform = $this->getTransform('regex', [
      'regex_value' => '/^[0-9]*(\\,[0-9]{3})*(\\.[0-9]*){0,1}$/',
      'mode'        => 'match_success_error',
      'source'      => 'source',
      'field'       => 'example_source_field',
    ]);
    $result = $transform->transform([
      'example_source_field'  => null
    ]);
  }

  /**
   * Testing transforms for Erors
   */
  public function testValueValidMatch(): void {
    $transform = $this->getTransform('regex', [
      'regex_value' => '/^[0-9]*(\\,[0-9]{3})*(\\.[0-9]*){0,1}$/',
      'mode'        => 'match',
      'source'      => 'source',
      'field'       => 'example_source_field',
    ]);
    $result = $transform->transform([
      'example_source_field'  => '1.23'
    ]);
    // Make sure it stays an array
    $this->assertEquals([
      '1.23',
      '',
      '.23'
    ], $result);
  }

  /**
   * Testing transforms for Erors
   */
  public function testValueValidMatchSuccess(): void {
    $transform = $this->getTransform('regex', [
      'regex_value' => '/^[0-9]*(\\,[0-9]{3})*(\\.[0-9]*){0,1}$/',
      'mode'        => 'match_success',
      'source'      => 'source',
      'field'       => 'example_source_field',
    ]);
    $result = $transform->transform([
      'example_source_field'  => '1.23'
    ]);
    // Make sure it stays an array
    $this->assertTrue($result);
  }

  /**
   * Testing transforms for Erors
   */
  public function testValueValidReplace(): void {
    $transform = $this->getTransform('regex', [
      'regex_value' => '/^[0-9]*(\\,[0-9]{3})*(\\.[0-9]*){0,1}$/',
      'mode'        => 'replace',
      'source'      => 'source',
      'field'       => 'example_source_field',
    ]);
    $result = $transform->transform([
      'example_source_field'  => '1.23'
    ]);
    // Make sure it stays an array
    $this->assertEmpty($result);
  }

  /**
   * Testing transforms for Erors
   */
  public function testValueInvalidMatch(): void {
    $transform = $this->getTransform('regex', [
      'regex_value' => '/^[0-9]*(\\,[0-9]{3})*(\\.[0-9]*){0,1}$/',
      'mode'        => 'match',
      'source'      => 'source',
      'field'       => 'example_source_field',
    ]);
    $result = $transform->transform([
      'example_source_field'  => '-1.23'
    ]);
    // Make sure it stays an array
    $this->assertEquals(null, $result);
  }

  /**
   * Testing transforms for Erors
   */
  public function testValueInvalidMatchSuccess(): void {
    $transform = $this->getTransform('regex', [
      'regex_value' => '/^[0-9]*(\\,[0-9]{3})*(\\.[0-9]*){0,1}$/',
      'mode'        => 'match_success',
      'source'      => 'source',
      'field'       => 'example_source_field',
    ]);
    $result = $transform->transform([
      'example_source_field'  => '-1.23'
    ]);
    // Make sure it stays an array
    $this->assertFalse($result);
  }

  /**
   * Testing transforms for Erors
   */
  public function testValueError(): void {
    $transform = $this->getTransform('regex', [
      'regex_value' => '/(?:\D+|<\d+>)*[!?]/',
      'mode'        => 'match',
      'source'      => 'source',
      'field'       => 'example_source_field',
    ]);
    $result = $transform->transform([
      'example_source_field'  => 'foobar foobar foobar'
    ]);
    // Make sure it stays an array
    $this->assertEquals(null, $result);

    $errors = $transform->getErrors();
    $this->assertNotEmpty($errors);
    $this->assertCount(1, $errors);
    $this->assertEquals('REGEX_ERROR', $errors[0]['__IDENTIFIER'] );
    $this->assertEquals('TRANSFORM.0', $errors[0]['__CODE'] );
  }

  /**
   * Test Spec output (simple case)
   */
  public function testSpecification(): void {
    $transform = $this->getTransform('regex', [
      'regex_value' => '/^[0-9]*(\\,[0-9]{3})*(\\.[0-9]*){0,1}$/',
      'mode'        => 'match_success',
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
