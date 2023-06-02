<?php

namespace codename\core\io\tests\transform\compare;

use codename\core\exception;
use codename\core\io\tests\transform\abstractTransformTest;
use ReflectionException;

class isdayTest extends abstractTransformTest
{
    /**
     * Testing transforms for Errors
     * @throws ReflectionException
     * @throws exception
     */
    public function testValueValidCase1(): void
    {
        $transform = $this->getTransform('compare_isday', [
          'source' => 'source',
          'field' => 'example_source_field',
          'value' => 'Sunday',
        ]);
        $result = $transform->transform([
          'example_source_field' => '2021-04-11',
        ]);
        // Make sure it stays an array
        static::assertTrue($result);
    }

    /**
     * Testing transforms for Errors
     * @throws ReflectionException
     * @throws exception
     */
    public function testValueValidCase2(): void
    {
        $transform = $this->getTransform('compare_isday', [
          'source' => 'source',
          'field' => 'example_source_field',
          'value' => ['Sunday'],
        ]);
        $result = $transform->transform([
          'example_source_field' => '2021-04-11',
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
        $transform = $this->getTransform('compare_isday', [
          'source' => 'source',
          'field' => 'example_source_field',
          'value' => 1,
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
