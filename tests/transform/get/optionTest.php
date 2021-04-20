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
  // public function testValueValid(): void {
  //   $transform = $this->getTransform('get_onetime', [
  //     'source'  => 'source',
  //     'field'   => 'example_source_field',
  //   ]);
  //   $result = $transform->transform([
  //     'example_source_field'  => 'example',
  //   ]);
  //
  //   // Make sure it stays an array
  //   $this->assertEquals('example', $result );
  // }

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
