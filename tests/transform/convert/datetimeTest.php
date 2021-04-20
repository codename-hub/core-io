<?php
namespace codename\core\io\tests\transform\convert;

class datetimeTest extends \codename\core\io\tests\transform\abstractTransformTest
{

  /**
   * Testing transforms for Erors
   */
  public function testValueValidCase1(): void {
    $transform = $this->getTransform('convert_datetime', [
      'source'          => 'source',
      'field'           => 'example_source_field',
      'source_format'   => 'Y-m-d H:i:s',
      'target_format'   => 'd.m.Y H:i:s',
    ]);
    $result = $transform->transform([
      'example_source_field'  => '2021-04-19 10:30:25',
    ]);
    // Make sure it stays an array
    $this->assertEquals('19.04.2021 10:30:25', $result);
  }

  /**
   * Testing transforms for Erors
   */
  public function testValueValidCase2(): void {
    $transform = $this->getTransform('convert_datetime', [
      'source'          => 'source',
      'field'           => 'example_source_field',
      'source_format'   => [ 'Y-m-d H:i:s', 'Y-m-d' ],
      'target_format'   => 'd.m.Y H:i:s',
    ]);
    $result = $transform->transform([
      'example_source_field'  => '2021-04-19 10:30:25',
    ]);
    // Make sure it stays an array
    $this->assertEquals('19.04.2021 10:30:25', $result);
  }

  /**
   * Testing transforms for Erors
   */
  public function testValueValidCase3(): void {
    $transform = $this->getTransform('convert_datetime', [
      'source'            => 'source',
      'field'             => 'example_source_field',
      'source_format'     => [ 'Y-m-d H:i:s', 'Y-m-d' ],
      'target_format'     => 'd.m.Y H:i:s',
      'set_time_to_null'  => true,
      'modify'            => '+1 day',
    ]);
    $result = $transform->transform([
      'example_source_field'  => '2021-04-19 10:30:25',
    ]);
    // Make sure it stays an array
    $this->assertEquals('20.04.2021 00:00:00', $result);
  }

  /**
   * Testing transforms for Erors
   */
  public function testValueValidCase4(): void {
    $transform = $this->getTransform('convert_datetime', [
      'source'            => 'source',
      'field'             => 'example_source_field',
      'source_format'     => [ 'Y-m-d H:i:s', 'Y-m-d' ],
      'target_format'     => 'd.m.Y H:i:s',
      'set_time_to_null'  => true,
      'modify'            => [
        'source'            => 'source',
        'field'             => 'example_modify_field',
      ],
    ]);
    $result = $transform->transform([
      'example_source_field'  => '2021-04-19 10:30:25',
      'example_modify_field'  => '+2 days',
    ]);
    // Make sure it stays an array
    $this->assertEquals('21.04.2021 00:00:00', $result);
  }

  /**
   * Testing transforms for Erors
   */
  public function testValueIsNull(): void {
    $transform = $this->getTransform('convert_datetime', [
      'source'          => 'source',
      'field'           => 'example_source_field',
      'source_format'   => [ 'Y-m-d H:i:s', 'Y-m-d' ],
      'target_format'   => 'd.m.Y H:i:s',
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
  public function testValueIsNullRequired(): void {
    $transform = $this->getTransform('convert_datetime', [
      'source'          => 'source',
      'field'           => 'example_source_field',
      'source_format'   => [ 'Y-m-d H:i:s', 'Y-m-d' ],
      'target_format'   => 'd.m.Y H:i:s',
      'required'        => true
    ]);
    $result = $transform->transform([
      'example_source_field'  => null,
    ]);
    // Make sure it stays an array
    $this->assertNull($result);

    $errors = $transform->getErrors();
    $this->assertNotEmpty($errors);
    $this->assertCount(1, $errors);
    $this->assertEquals('VALUE_NULL', $errors[0]['__IDENTIFIER'] );
    $this->assertEquals('TRANSFORM.0', $errors[0]['__CODE'] );
  }

  /**
   * Testing transforms for Erors
   */
  public function testValueInvalidDatetime(): void {
    $transform = $this->getTransform('convert_datetime', [
      'source'          => 'source',
      'field'           => 'example_source_field',
      'source_format'   => [ 'Y-m-d H:i:s', 'Y-m-d' ],
      'target_format'   => 'd.m.Y H:i:s',
      'required'        => true
    ]);
    $result = $transform->transform([
      'example_source_field'  => '19.04.2021 11:22:33',
    ]);
    // Make sure it stays an array
    $this->assertNull($result);

    $errors = $transform->getErrors();
    $this->assertNotEmpty($errors);
    $this->assertCount(1, $errors);
    $this->assertEquals('convert_datetime', $errors[0]['__IDENTIFIER'] );
    $this->assertEquals('TRANSFORM.INVALID_FORMAT', $errors[0]['__CODE'] );
  }

  /**
   * Testing transforms for Erors
   */
  public function testValueIsDateTimeObject(): void {
    $transform = $this->getTransform('convert_datetime', [
      'source'          => 'source',
      'field'           => 'example_source_field',
      'source_format'   => [ 'Y-m-d H:i:s', 'Y-m-d' ],
      'target_format'   => 'DateTime'
    ]);
    $result = $transform->transform([
      'example_source_field'  => '2021-04-19 11:22:33',
    ]);
    // Make sure it stays an array
    $this->assertInstanceOf(\DateTime::class, $result);
  }

  /**
   * Testing transforms for Erors
   */
  public function testValueIsDateTimeImmutableObject(): void {
    $transform = $this->getTransform('convert_datetime', [
      'source'          => 'source',
      'field'           => 'example_source_field',
      'source_format'   => [ 'Y-m-d H:i:s', 'Y-m-d' ],
      'target_format'   => 'DateTimeImmutable'
    ]);
    $result = $transform->transform([
      'example_source_field'  => '2021-04-19 11:22:33',
    ]);
    // Make sure it stays an array
    $this->assertInstanceOf(\DateTimeImmutable::class, $result);
  }

  /**
   * Test Spec output (simple case)
   */
  public function testSpecification(): void {
    $transform = $this->getTransform('convert_datetime', [
      'source'          => 'source',
      'field'           => 'example_source_field',
      'source_format'   => 'Y-m-d H:i:s',
      'target_format'   => 'd.m.Y H:i:s',
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
