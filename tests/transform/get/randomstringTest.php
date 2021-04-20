<?php
namespace codename\core\io\tests\transform\get;

class randomstringTest extends \codename\core\io\tests\transform\abstractTransformTest
{

  /**
   * Testing transforms for Erors
   */
  public function testValueValid(): void {
    $transform = $this->getTransform('get_randomstring', [
      'chars'   => 'A',
      'length'  => 10,
    ]);
    $result = $transform->transform([]);

    // Make sure it stays an array
    $this->assertEquals('AAAAAAAAAA', $result );
  }

  /**
   * Test Spec output (simple case)
   */
  public function testSpecification(): void {
    $transform = $this->getTransform('get_randomstring', [
      'chars'   => 'example_source_field',
      'length'  => 10,
    ]);
    $this->assertEmpty($transform->getSpecification());
  }

}
