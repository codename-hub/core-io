<?php

namespace codename\core\io\tests\transform;

use codename\core\exception;
use ReflectionException;

class hashTest extends abstractTransformTest
{
    /**
     * Testing transforms for Errors
     * @throws ReflectionException
     * @throws exception
     */
    public function testConstructInvalid(): void
    {
        $this->expectException(exception::class);
        $this->expectExceptionMessage('EXCEPTION_TRANSFORM_HASH_NO_ALGORITHM_SPECIFIED');

        $transform = $this->getTransform('hash', [
          'source' => 'source',
          'field' => 'example_source_field',
        ]);
        $transform->transform([
          'example_source_field' => null,
        ]);
    }

    /**
     * Testing transforms for Errors
     * @throws ReflectionException
     * @throws exception
     */
    public function testValueValid(): void
    {
        $transform = $this->getTransform('hash', [
          'source' => 'source',
          'field' => 'example_source_field',
          'algorithm' => 'md5',
        ]);
        $result = $transform->transform([
          'example_source_field' => 'test',
        ]);
        // Make sure it stays an array
        static::assertEquals(hash('md5', 'test'), $result);
    }

    /**
     * Test Spec output (simple case)
     * @throws ReflectionException
     * @throws exception
     */
    public function testSpecification(): void
    {
        $transform = $this->getTransform('hash', [
          'source' => 'source',
          'field' => 'example_source_field',
          'algorithm' => 'md5',
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
