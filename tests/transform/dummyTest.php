<?php
namespace codename\core\io\tests\transform;

class dummyTest extends abstractTransformTest
{

  /**
   * [testInternalTransform description]
   */
  public function testInternalTransform(): void {
    $this->expectException(\LogicException::class);
    $this->expectExceptionMessage('Not implemented and shouln\'t be');

    $transform = $this->getTransform('dummy', [
      'source'  => 'source',
      'field'   => 'example_source_field',
    ]);
    $specification = $transform->internalTransform([]);
  }

  /**
   * Test Spec output (simple case)
   */
  public function testSpecification(): void {
    $this->expectException(\LogicException::class);
    $this->expectExceptionMessage('Not implemented and shouln\'t be');

    $transform = $this->getTransform('dummy', [
      'source'  => 'source',
      'field'   => 'example_source_field',
    ]);
    $specification = $transform->getSpecification();
  }
}
