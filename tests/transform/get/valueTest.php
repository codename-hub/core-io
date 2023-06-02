<?php

namespace codename\core\io\tests\transform\get;

use codename\core\exception;
use codename\core\io\tests\transform\abstractTransformTest;
use ReflectionException;

class valueTest extends abstractTransformTest
{
    /**
     * Testing transforms for Errors
     * @throws ReflectionException
     * @throws exception
     */
    public function testValue(): void
    {
        $transform = $this->getTransform('get_value', [
          'source' => 'source',
          'field' => 'example_source_field',
        ]);
        $result = $transform->transform([
          'example_source_field' => 'AbCdEfGhIjKlMnOpQrStUvWxYz',
        ]);

        // Make sure it stays an array
        static::assertEquals('AbCdEfGhIjKlMnOpQrStUvWxYz', $result);
    }


    /**
     * Test Spec output (simple case)
     * @throws ReflectionException
     * @throws exception
     */
    public function testSpecification(): void
    {
        $transform = $this->getTransform('get_value', [
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
