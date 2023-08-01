<?php

namespace codename\core\io\tests\transform\get;

use codename\core\exception;
use codename\core\io\pipeline;
use codename\core\io\tests\transform\abstractTransformTest;
use ReflectionException;

class arraycolumnTest extends abstractTransformTest
{
    /**
     * Testing transforms for Errors
     * @throws ReflectionException
     * @throws exception
     */
    public function testValueValidCase1(): void
    {
        $transform = $this->getTransform('get_arraycolumn', [
          'source' => 'source',
          'field' => 'example_source_field',
          'index' => 'example',
        ]);
        $result = $transform->transform([
          'example_source_field' => [
            [
              'example' => 'test',
            ],
          ],
        ]);
        // Make sure it stays an array
        // static::assertTrue($result);
        static::assertEquals(['test'], $result);
    }

    /**
     * Testing transforms for Errors
     * @throws ReflectionException
     * @throws exception
     */
    public function testValueValidCase2(): void
    {
        $transform = $this->getTransform('get_arraycolumn', [
          'source' => 'source',
          'field' => 'example_source_field',
          'index' => [
            'source' => 'source',
            'field' => 'example_index_field',
          ],
        ]);
        $result = $transform->transform([
          'example_index_field' => 'example',
          'example_source_field' => [
            [
              'example' => 'test',
            ],
          ],
        ]);
        // Make sure it stays an array
        // static::assertTrue($result);
        static::assertEquals(['test'], $result);
    }

    /**
     * Testing transforms for Errors
     * @throws ReflectionException
     * @throws exception
     */
    public function testValueValidCase3(): void
    {
        $transform = $this->getTransform('get_arraycolumn', [
          'source' => 'option',
          'field' => 'example_source_field',
          'index' => [
            'source' => 'source',
            'field' => 'example_index_field',
          ],
        ]);

        $pipeline = new pipeline(null, []);
        $pipeline->setOptions([
          'example_source_field' => [
            [
              'example' => 'test',
            ],
          ],
        ]);
        $transform->setPipelineInstance($pipeline);

        $result = $transform->transform([
          'example_index_field' => 'example',
        ]);

        // Make sure it stays an array
        static::assertEquals(['test'], $result);
    }

    /**
     * Test Spec output (simple case)
     * @throws ReflectionException
     * @throws exception
     */
    public function testSpecification(): void
    {
        $transform = $this->getTransform('get_arraycolumn', [
          'source' => 'source',
          'field' => 'example_source_field',
          'index' => 'example',
        ]);
        static::assertEquals(
            [
              'type' => 'transform',
              'source' => ['source.example'],
            ],
            $transform->getSpecification()
        );
    }
}
