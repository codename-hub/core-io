<?php

namespace codename\core\io\tests\transform;

use codename\core\exception;
use ReflectionException;

class containsTest extends abstractTransformTest
{
    /**
     * Testing transforms for Errors
     * @throws ReflectionException
     * @throws exception
     */
    public function testValueInvalid(): void
    {
        $transform = $this->getTransform('contains', [
          'collection' => ['source' => 'source', 'field' => 'example_collection_source_field'],
          'item' => ['source' => 'source', 'field' => 'example_item_source_field'],
        ]);
        $result = $transform->transform([
          'example_collection_source_field' => [
            'test',
            'test1',
            'test2',
          ],
          'example_item_source_field' => 'test3',
        ]);
        // Make sure it stays an array
        static::assertFalse($result);
    }

    /**
     * Testing transforms for Errors
     * @throws ReflectionException
     * @throws exception
     */
    public function testValueValid(): void
    {
        $transform = $this->getTransform('contains', [
          'collection' => ['source' => 'source', 'field' => 'example_collection_source_field'],
          'item' => ['source' => 'source', 'field' => 'example_item_source_field'],
        ]);
        $result = $transform->transform([
          'example_collection_source_field' => [
            'test',
            'test1',
            'test2',
          ],
          'example_item_source_field' => 'test1',
        ]);
        // Make sure it stays an array
        static::assertTrue($result);
    }

    /**
     * Testing transforms for Errors
     * @throws ReflectionException
     * @throws exception
     */
    public function testValueNotSourceFields(): void
    {
        $this->expectException(exception::class);

        $transform = $this->getTransform('contains', [
          'collection' => 'example_collection_source_field',
          'item' => 'example_item_source_field',
        ]);
        $transform->transform([]);
    }

    /**
     * Testing transforms for Errors
     * @throws ReflectionException
     * @throws exception
     */
    public function testValueCollectionNotAArray(): void
    {
        $transform = $this->getTransform('contains', [
          'collection' => ['source' => 'source', 'field' => 'example_collection_source_field'],
          'item' => 'example_item_source_field',
        ]);
        $result = $transform->transform([
          'example_collection_source_field' => 'test',
        ]);
        static::assertNull($result);
    }

    /**
     * Test Spec output (simple case)
     * @throws ReflectionException
     * @throws exception
     */
    public function testSpecification(): void
    {
        $transform = $this->getTransform('contains', [
          'collection' => ['source' => 'source', 'field' => 'example_collection_source_field'],
          'item' => ['source' => 'source', 'field' => 'example_item_source_field'],
        ]);
        static::assertEquals(
            [
              'type' => 'transform',
              'source' => ['source.example_item_source_field', 'source.example_collection_source_field'],
            ],
            $transform->getSpecification()
        );
    }
}
