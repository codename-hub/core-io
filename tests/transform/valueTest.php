<?php

namespace codename\core\io\tests\transform;

use codename\core\exception;
use ReflectionException;

class valueTest extends abstractTransformTest
{
    /**
     * Testing transforms for Errors
     * @throws ReflectionException
     * @throws exception
     */
    public function testValueValid(): void
    {
        $transform = $this->getTransform('value', [
          'value' => 'example',
        ]);
        $result = $transform->transform([
          'example_source_field' => ' example ',
        ]);
        // Make sure it stays an array
        static::assertEquals('example', $result);
    }

    /**
     * Test Spec output (simple case)
     * @throws ReflectionException
     * @throws exception
     */
    public function testSpecification(): void
    {
        $transform = $this->getTransform('value', [
          'source' => 'source',
          'field' => 'example_source_field',
        ]);
        static::assertEquals(
            [
              'type' => 'transform',
              'source' => [],
            ],
            $transform->getSpecification()
        );
    }
}
