<?php

namespace codename\core\io\tests\transform;

use codename\core\exception;
use ReflectionException;

class trimTest extends abstractTransformTest
{
    /**
     * Testing transforms for Errors
     * @throws ReflectionException
     * @throws exception
     */
    public function testValueValid(): void
    {
        $transform = $this->getTransform('trim', [
          'source' => 'source',
          'field' => 'example_source_field',
        ]);
        $result = $transform->transform([
          'example_source_field' => ' example ',
        ]);
        // Make sure it stays an array
        static::assertEquals('example', $result);
    }

    /**
     * Testing transforms for Errors
     * @throws ReflectionException
     * @throws exception
     */
    public function testValueValidWithMask(): void
    {
        $transform = $this->getTransform('trim', [
          'source' => 'source',
          'field' => 'example_source_field',
          'character_mask' => '0',
        ]);
        $result = $transform->transform([
          'example_source_field' => ' example ',
        ]);
        // Make sure it stays an array
        static::assertEquals(' example ', $result);
    }

    /**
     * Test Spec output (simple case)
     * @throws ReflectionException
     * @throws exception
     */
    public function testSpecification(): void
    {
        $transform = $this->getTransform('trim', [
          'source' => 'source',
          'field' => 'example_source_field',
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
