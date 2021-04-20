<?php
namespace codename\core\io\tests\transform\get;

class valueTest extends \codename\core\io\tests\transform\abstractTransformTest
{

  /**
   * Testing transforms for Erors
   */
  public function testValue(): void {
    $transform = $this->getTransform('get_value', [
      'source'  => 'source',
      'field'   => 'example_source_field',
    ]);
    $result = $transform->transform([
      'example_source_field'  => 'AbCdEfGhIjKlMnOpQrStUvWxYz',
    ]);

    // Make sure it stays an array
    $this->assertEquals('AbCdEfGhIjKlMnOpQrStUvWxYz', $result );
  }


  /**
   * Test Spec output (simple case)
   */
  public function testSpecification(): void {
    $transform = $this->getTransform('get_value', [
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
