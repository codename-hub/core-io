<?php

namespace codename\core\io\tests\transform\compare;

use codename\core\exception;
use codename\core\io\tests\transform\abstractTransformTest;
use ReflectionException;

class beginswithTest extends abstractTransformTest
{
    /**
     * Testing transforms for Errors
     * @throws ReflectionException
     * @throws exception
     */
    public function testValueValid(): void
    {
        $transform = $this->getTransform('compare_beginswith', [
          'source' => 'source',
          'field' => 'example_source_field',
          'value' => 'test',
        ]);
        $result = $transform->transform([
          'example_source_field' => 'testvalue',
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
        $transform = $this->getTransform('compare_beginswith', [
          'source' => 'source',
          'field' => 'example_source_field',
          'value' => 'example_source_field',
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
