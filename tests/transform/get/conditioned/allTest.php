<?php

namespace codename\core\io\tests\transform\get\conditioned;

use codename\core\exception;
use codename\core\io\tests\transform\abstractTransformTest;
use ReflectionException;

class allTest extends abstractTransformTest
{
    /**
     * Testing transforms for Errors
     * @throws ReflectionException
     * @throws exception
     */
    public function testValueValidCase1(): void
    {
        $transform = $this->getTransform('get_conditioned_all', [
          'return' => true,
          'condition' => [
            [
              'source' => 'source',
              'field' => 'example_source_field',
              'operator' => '=',
              'value' => 123,
            ],
            [
              'source' => 'source',
              'field' => 'example_source_field2',
              'operator' => '!=',
              'value' => 456,
            ],
          ],
        ]);
        $result = $transform->transform([
          'example_source_field' => 123,
          'example_source_field2' => 123,
        ]);
        static::assertTrue($result);
    }

    /**
     * Testing transforms for Errors
     * @throws ReflectionException
     * @throws exception
     */
    public function testValueValidCase2(): void
    {
        $transform = $this->getTransform('get_conditioned_all', [
          'required' => true,
          'condition' => [
            [
              'source' => 'source',
              'field' => 'example_source_field',
              'operator' => '<>',
              'value' => 123,
            ],
            [
              'source' => 'source',
              'field' => 'example_source_field2',
              'operator' => '=',
              'value' => 456,
            ],
          ],
        ]);
        $result = $transform->transform([
          'example_source_field' => 123,
          'example_source_field2' => 123,
        ]);
        static::assertEquals(null, $result);

        $errors = $transform->getErrors();
        static::assertNotEmpty($errors);
        static::assertCount(1, $errors);
        static::assertEquals('GET_CONDITIONED_ALL_NOMATCH', $errors[0]['__IDENTIFIER']);
        static::assertEquals('TRANSFORM.0', $errors[0]['__CODE']);
    }

    /**
     * Testing transforms for Errors
     * @throws ReflectionException
     * @throws exception
     */
    public function testValueValidCase3(): void
    {
        $transform = $this->getTransform('get_conditioned_all', [
          'default' => 'example',
          'return' => true,
          'condition' => [
            [
              'source' => 'source',
              'field' => 'example_source_field',
              'operator' => '<>',
              'value' => 123,
            ],
            [
              'source' => 'source',
              'field' => 'example_source_field2',
              'operator' => '!=',
              'value' => 123,
            ],
          ],
        ]);
        $result = $transform->transform([
          'example_source_field' => 123,
          'example_source_field2' => 123,
        ]);
        static::assertEquals('example', $result);
    }
}
