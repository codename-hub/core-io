<?php

namespace codename\core\io\tests\transform\compare;

use codename\core\exception;
use codename\core\io\tests\transform\abstractTransformTest;
use ReflectionException;

class isequalTest extends abstractTransformTest
{
    /**
     * Testing transforms for Errors
     * @throws ReflectionException
     * @throws exception
     */
    public function testValueValid(): void
    {
        $transform = $this->getTransform('compare_isequal', [
          'source' => 'source',
          'field' => 'example_source_field',
          'value' => 'hello',
        ]);
        $result = $transform->transform([
          'example_source_field' => 'hello',
        ]);
        // Make sure it stays an array
        static::assertTrue($result);
    }

    /**
     * Test Spec output (simple case)
     * @throws ReflectionException
     * @throws exception
     */
    public function testSpecification(): void
    {
        $transform = $this->getTransform('compare_isequal', [
          'source' => 'source',
          'field' => 'example_source_field',
          'value' => 1,
        ]);
        static::assertEquals(
            [
              'type' => 'transform',
              'source' => ['source.example_source_field'],
            ],
            $transform->getSpecification()
        );
    }
}
