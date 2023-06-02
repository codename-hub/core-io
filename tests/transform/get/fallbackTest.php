<?php

namespace codename\core\io\tests\transform\get;

use codename\core\exception;
use codename\core\io\tests\transform\abstractTransformTest;
use ReflectionException;

class fallbackTest extends abstractTransformTest
{
    /**
     * Testing transforms for Errors
     * @throws ReflectionException
     * @throws exception
     */
    public function testValueValid(): void
    {
        $transform = $this->getTransform('get_fallback', [
          'fallback' => [
            [
              'source' => 'source',
              'field' => 'example_source_field',
            ],
            [
              'source' => 'source',
              'field' => 'example_source_field2',
            ],
          ],
        ]);
        $result = $transform->transform([
          'example_source_field' => null,
          'example_source_field2' => 'example',
        ]);

        // Make sure it stays an array
        static::assertEquals('example', $result);
    }

    /**
     * Testing transforms for Errors
     * @throws ReflectionException
     * @throws exception
     */
    public function testValueRequired(): void
    {
        $transform = $this->getTransform('get_fallback', [
          'required' => true,
          'fallback' => [
            [
              'source' => 'source',
              'field' => 'example_source_field',
            ],
            [
              'source' => 'source',
              'field' => 'example_source_field2',
            ],
          ],
        ]);
        $result = $transform->transform([
          'example_source_field' => null,
          'example_source_field2' => null,
        ]);

        // Make sure it stays an array
        static::assertNull($result);

        $errors = $transform->getErrors();
        static::assertNotEmpty($errors);
        static::assertCount(1, $errors);
        static::assertEquals('VALUE_NULL', $errors[0]['__IDENTIFIER']);
        static::assertEquals('TRANSFORM.0', $errors[0]['__CODE']);
    }

    /**
     * Test Spec output (simple case)
     * @throws ReflectionException
     * @throws exception
     */
    public function testSpecification(): void
    {
        $transform = $this->getTransform('get_fallback', [
          'fallback' => [
            [
              'source' => 'source',
              'field' => 'example_source_field',
            ],
            [
              'source' => 'source',
              'field' => 'example_source_field2',
            ],
          ],
        ]);
        static::assertEquals(
            [
              'type' => 'transform',
              'source' => ['source.example_source_field', 'source.example_source_field2'],
            ],
            $transform->getSpecification()
        );
    }
}
