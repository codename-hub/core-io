<?php
namespace codename\core\io\tests\transform;

class setTest extends abstractTransformTest
{

  /**
   * [testInternalTransform description]
   */
  public function testInternalTransform(): void {
    $this->expectException(\LogicException::class);
    $this->expectExceptionMessage('Not implemented');

    $transform = $this->getTransform('set', [
      'source'  => 'source',
      'field'   => 'example_source_field',
    ]);
    $specification = $transform->internalTransform([]);
  }

  /**
   * Test Spec output (simple case)
   */
  public function testSpecification(): void {
    $transform = $this->getTransform('set', [
      'source'  => 'source',
      'field'   => 'example_source_field',
    ]);
    $specification = $transform->getSpecification();
    $this->assertEquals([], $specification);
  }
}
