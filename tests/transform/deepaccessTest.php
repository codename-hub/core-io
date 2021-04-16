<?php
namespace codename\core\io\tests\transform;

class deepaccessTest extends abstractTransformTest
{

  /**
   * Testing transforms for Erors
   */
  public function testConstructValid(): void {
    $transform = $this->getTransform('deepaccess', [
      'source'  => 'source',
      'field'   => 'example_source_field',
      'path'    => [],
    ]);

    $this->assertInstanceOf(\codename\core\io\transform\deepaccess::class, $transform);
  }

  /**
   * Testing transforms for Erors
   */
  public function testValueIsNull(): void {
    $transform = $this->getTransform('deepaccess', [
      'source'    => 'source',
      // 'field'     => 'example_source_field',
      'required'  => true,
    ]);
    $result = $transform->transform([
      'example_source_field'
    ]);
    // Make sure it stays an array
    $this->assertEquals(null, $result );
  }

  /**
   * Testing transforms for Erors
   */
  public function testValueValid(): void {
    $transform = $this->getTransform('deepaccess', [
      'source'  => 'source',
      'field'   => 'example_source_field',
    ]);
    $result = $transform->transform([
      'example_source_field'  => 'test'
    ]);
    $this->assertEmpty($result);
  }

  /**
   * Test Spec output (simple case)
   */
  public function testSpecification(): void {
    $this->expectException(\LogicException::class);
    $this->expectExceptionMessage('Not implemented');

    $transform = $this->getTransform('deepaccess', [
      'source'  => 'source',
      'field'   => 'example_source_field',
    ]);
    $specification = $transform->getSpecification();
  }
}
