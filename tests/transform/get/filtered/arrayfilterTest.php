<?php

namespace codename\core\io\tests\transform\get\filtered;

use codename\core\exception;
use codename\core\io\tests\transform\abstractTransformTest;
use ReflectionException;

class arrayfilterTest extends abstractTransformTest
{
    /**
     * Testing transforms for Errors
     * @throws ReflectionException
     * @throws exception
     */
    public function testValueValidCase1(): void
    {
        $transform = $this->getTransform('get_filtered_arrayfilter', [
          'force_array' => true,
          'source' => 'source',
          'field' => 'example_source_field',
          'path' => ['example'],
          'filter' => [
            [
              'operator' => '=',
              'value' => 1,
            ],
          ],
        ]);
        $result = $transform->transform([
          'example_source_field' => [
            [
              'example' => 1,
            ],
            [
              'example' => 2,
            ],
          ],
        ]);

        // Make sure it stays an array
        static::assertEquals([
          [
            'example' => 1,
          ],
        ], $result);
    }

    /**
     * Testing transforms for Errors
     * @throws ReflectionException
     * @throws exception
     */
    public function testValueValidCase2(): void
    {
        $transform = $this->getTransform('get_filtered_arrayfilter', [
          'source' => 'source',
          'field' => 'example_source_field',
          'path' => ['example'],
          'filter' => [
            [
              'operator' => '=',
              'value' => 1,
            ],
          ],
        ]);
        $result = $transform->transform([
          'example_source_field' => [
            [
              'example' => 1,
            ],
            [
              'example' => 2,
            ],
          ],
        ]);

        // Make sure it stays an array
        static::assertEquals([
          [
            'example' => 1,
          ],
        ], $result);
    }

    /**
     * Testing transforms for Errors
     * @throws ReflectionException
     * @throws exception
     */
    public function testValueWrongOperator(): void
    {
        $transform = $this->getTransform('get_filtered_arrayfilter', [
          'null_if_empty' => true,
          'source' => 'source',
          'field' => 'example_source_field',
          'path' => ['example'],
          'filter' => [
            [
              'operator' => '<>',
              'value' => 1,
            ],
          ],
        ]);
        $result = $transform->transform([
          'example_source_field' => [
            [
              'example' => 1,
            ],
            [
              'example' => 2,
            ],
          ],
        ]);

        // Make sure it stays an array
        static::assertEquals([
          [
            'example' => 1,
          ],
          [
            'example' => 2,
          ],
        ], $result);
    }

    /**
     * Testing transforms for Errors
     * @throws ReflectionException
     * @throws exception
     */
    public function testValueNoMatch(): void
    {
        $transform = $this->getTransform('get_filtered_arrayfilter', [
          'null_if_empty' => true,
          'source' => 'source',
          'field' => 'example_source_field',
          'path' => ['example'],
          'filter' => [
            [
              'operator' => '=',
              'value' => 1,
            ],
            [
              'operator' => '!=',
              'value' => 1,
            ],
          ],
        ]);
        $result = $transform->transform([
          'example_source_field' => [
            [
              'example' => 1,
            ],
            [
              'example' => 2,
            ],
          ],
        ]);

        // Make sure it stays an array
        static::assertNull($result);
    }
}
