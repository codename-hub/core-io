<?php

namespace codename\core\io\tests\transform\get;

use codename\core\exception;
use codename\core\io\tests\transform\abstractTransformTest;
use ReflectionException;

class filteredTest extends abstractTransformTest
{
    /**
     * Testing transforms for Errors
     * @throws ReflectionException
     * @throws exception
     */
    public function testValueValid(): void
    {
        $transform = $this->getTransform('get_filtered', [
          'source' => 'source',
          'field' => 'example_source_field',
          'filter' => [
            [
              'operator' => '=',
              'value' => 1,
            ],
          ],
        ]);
        $result = $transform->transform([
          'example_source_field' => 1,
        ]);

        // Make sure it stays an array
        static::assertEquals('1', $result);
    }

    /**
     * Testing transforms for Errors
     * @throws ReflectionException
     * @throws exception
     */
    public function testValueValidCase2(): void
    {
        $transform = $this->getTransform('get_filtered', [
          'source' => 'source',
          'field' => 'example_source_field',
          'filter' => [
            [
              'operator' => '!=',
              'value' => 0,
            ],
          ],
        ]);
        $result = $transform->transform([
          'example_source_field' => 1,
        ]);

        // Make sure it stays an array
        static::assertEquals('1', $result);
    }

    /**
     * Testing transforms for Errors
     * @throws ReflectionException
     * @throws exception
     */
    public function testValueNoMatch(): void
    {
        $transform = $this->getTransform('get_filtered', [
          'source' => 'source',
          'field' => 'example_source_field',
          'filter' => [
            [
              'operator' => '=',
              'value' => 0,
            ],
            [
              'operator' => '!=',
              'value' => 1,
            ],
            [
              'operator' => '<>',
              'value' => 1,
            ],
          ],
        ]);
        $result = $transform->transform([
          'example_source_field' => 1,
        ]);

        // Make sure it stays an array
        static::assertNull($result);
    }

    /**
     * Test Spec output (simple case)
     * @throws ReflectionException
     * @throws exception
     */
    public function testSpecification(): void
    {
        $transform = $this->getTransform('get_filtered', [
          'source' => 'source',
          'field' => 'example_source_field',
          'filter' => [
            [
              'operator' => '=',
              'value' => 1,
            ],
          ],
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
