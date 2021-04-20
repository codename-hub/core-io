<?php
namespace codename\core\io\tests\transform\get;

class substrTest extends \codename\core\io\tests\transform\abstractTransformTest
{

  /**
   * Testing transforms for Erors
   */
  public function testValueValidCase1(): void {
    $transform = $this->getTransform('get_substr', [
      'source'  => 'source',
      'field'   => 'example_source_field',
      'start'   => -3,
    ]);
    $result = $transform->transform([
      'example_source_field'  => 'AbCdEfGhIjKlMnOpQrStUvWxYz',
    ]);

    // Make sure it stays an array
    $this->assertEquals('xYz', $result );
  }

  /**
   * Testing transforms for Erors
   */
  public function testValueValidCase2(): void {
    $transform = $this->getTransform('get_substr', [
      'source'  => 'source',
      'field'   => 'example_source_field',
      'start'   => 3,
      'length'  => 3,
    ]);
    $result = $transform->transform([
      'example_source_field'  => 'AbCdEfGhIjKlMnOpQrStUvWxYz',
    ]);

    // Make sure it stays an array
    $this->assertEquals('dEf', $result );
  }


  /**
   * Test Spec output (simple case)
   */
  public function testSpecification(): void {
    $transform = $this->getTransform('get_substr', [
      'source'  => 'source',
      'field'   => 'example_source_field',
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
