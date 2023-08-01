<?php

namespace codename\core\io\tests\transform\compare;

use codename\core\exception;
use codename\core\io\tests\transform\abstractTransformTest;
use ReflectionException;

class numberTest extends abstractTransformTest
{
    /**
     * Testing transforms for Errors
     * @throws ReflectionException
     * @throws exception
     */
    public function testValueValidCase1(): void
    {
        $transform = $this->getTransform('compare_number', [
          'source' => 'source',
          'field' => 'example_source_field',
          'value' => [
            'source' => 'source',
            'field' => 'example_value_field',
          ],
          'operator' => '=',
          'precision' => 2,
        ]);
        $result = $transform->transform([
          'example_source_field' => 1,
          'example_value_field' => 1,
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
        $transform = $this->getTransform('compare_number', [
          'source' => 'source',
          'field' => 'example_source_field',
          'value' => [
            'source' => 'source',
            'field' => 'example_value_field',
          ],
          'operator' => '!=',
          'precision' => 2,
        ]);
        $result = $transform->transform([
          'example_source_field' => 1,
          'example_value_field' => 2,
        ]);
        // Make sure it stays an array
        static::assertTrue($result);
    }

    /**
     * Testing transforms for Errors
     * @throws ReflectionException
     * @throws exception
     */
    public function testValueValidCase3(): void
    {
        $transform = $this->getTransform('compare_number', [
          'source' => 'source',
          'field' => 'example_source_field',
          'value' => [
            'source' => 'source',
            'field' => 'example_value_field',
          ],
          'operator' => '>',
          'precision' => 2,
        ]);
        $result = $transform->transform([
          'example_source_field' => 2,
          'example_value_field' => 1,
        ]);
        // Make sure it stays an array
        static::assertTrue($result);
    }

    /**
     * Testing transforms for Errors
     * @throws ReflectionException
     * @throws exception
     */
    public function testValueValidCase4(): void
    {
        $transform = $this->getTransform('compare_number', [
          'source' => 'source',
          'field' => 'example_source_field',
          'value' => [
            'source' => 'source',
            'field' => 'example_value_field',
          ],
          'operator' => '<',
          'precision' => 2,
        ]);
        $result = $transform->transform([
          'example_source_field' => 1,
          'example_value_field' => 2,
        ]);
        // Make sure it stays an array
        static::assertTrue($result);
    }

    /**
     * Testing transforms for Errors
     * @throws ReflectionException
     * @throws exception
     */
    public function testValueValidCase5(): void
    {
        $transform = $this->getTransform('compare_number', [
          'source' => 'source',
          'field' => 'example_source_field',
          'value' => [
            'source' => 'source',
            'field' => 'example_value_field',
          ],
          'operator' => '>=',
          'precision' => 2,
        ]);
        $result = $transform->transform([
          'example_source_field' => 1,
          'example_value_field' => 1,
        ]);
        // Make sure it stays an array
        static::assertTrue($result);
    }

    /**
     * Testing transforms for Errors
     * @throws ReflectionException
     * @throws exception
     */
    public function testValueValidCase6(): void
    {
        $transform = $this->getTransform('compare_number', [
          'source' => 'source',
          'field' => 'example_source_field',
          'value' => [
            'source' => 'source',
            'field' => 'example_value_field',
          ],
          'operator' => '<=',
          'precision' => 2,
        ]);
        $result = $transform->transform([
          'example_source_field' => 1,
          'example_value_field' => 1,
        ]);
        // Make sure it stays an array
        static::assertTrue($result);
    }

    /**
     * Testing transforms for Errors
     * @throws ReflectionException
     * @throws exception
     */
    public function testValueInvalidOperator(): void
    {
        $this->expectException(exception::class);
        $this->expectExceptionMessage('INVALID_OPERATOR');

        $transform = $this->getTransform('compare_number', [
          'source' => 'source',
          'field' => 'example_source_field',
          'value' => [
            'source' => 'source',
            'field' => 'example_value_field',
          ],
          'operator' => '!!!',
          'precision' => 2,
        ]);
        $transform->transform([
          'example_source_field' => 1,
          'example_value_field' => 1,
        ]);
    }

    /**
     * Test Spec output (simple case)
     * @throws ReflectionException
     * @throws exception
     */
    public function testSpecification(): void
    {
        $transform = $this->getTransform('compare_number', [
          'source' => 'source',
          'field' => 'example_source_field',
          'value' => [
            'source' => 'source',
            'field' => 'example_value_field',
          ],
          'operator' => '=',
          'precision' => 2,
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
