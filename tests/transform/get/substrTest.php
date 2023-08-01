<?php

namespace codename\core\io\tests\transform\get;

use codename\core\exception;
use codename\core\io\tests\transform\abstractTransformTest;
use ReflectionException;

class substrTest extends abstractTransformTest
{
    /**
     * Testing transforms for Errors
     * @throws ReflectionException
     * @throws exception
     */
    public function testValueValidCase1(): void
    {
        $transform = $this->getTransform('get_substr', [
          'source' => 'source',
          'field' => 'example_source_field',
          'start' => -3,
        ]);
        $result = $transform->transform([
          'example_source_field' => 'AbCdEfGhIjKlMnOpQrStUvWxYz',
        ]);

        // Make sure it stays an array
        static::assertEquals('xYz', $result);
    }

    /**
     * Testing transforms for Errors
     * @throws ReflectionException
     * @throws exception
     */
    public function testValueValidCase2(): void
    {
        $transform = $this->getTransform('get_substr', [
          'source' => 'source',
          'field' => 'example_source_field',
          'start' => 3,
          'length' => 3,
        ]);
        $result = $transform->transform([
          'example_source_field' => 'AbCdEfGhIjKlMnOpQrStUvWxYz',
        ]);

        // Make sure it stays an array
        static::assertEquals('dEf', $result);
    }


    /**
     * Test Spec output (simple case)
     * @throws ReflectionException
     * @throws exception
     */
    public function testSpecification(): void
    {
        $transform = $this->getTransform('get_substr', [
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
