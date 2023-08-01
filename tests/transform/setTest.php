<?php

namespace codename\core\io\tests\transform;

use codename\core\exception;
use LogicException;
use ReflectionException;

class setTest extends abstractTransformTest
{
    /**
     * [testInternalTransform description]
     * @throws ReflectionException
     * @throws exception
     */
    public function testInternalTransform(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Not implemented');

        $transform = $this->getTransform('set', [
          'source' => 'source',
          'field' => 'example_source_field',
        ]);
        $transform->internalTransform([]);
    }

    /**
     * Test Spec output (simple case)
     * @throws ReflectionException
     * @throws exception
     */
    public function testSpecification(): void
    {
        $transform = $this->getTransform('set', [
          'source' => 'source',
          'field' => 'example_source_field',
        ]);
        $specification = $transform->getSpecification();
        static::assertEquals([], $specification);
    }
}
