<?php

namespace codename\core\io\tests\transform\get;

use codename\core\exception;
use codename\core\io\tests\transform\abstractTransformTest;
use ReflectionException;

class strcaseTest extends abstractTransformTest
{
    /**
     * Testing transforms for Errors
     * @throws ReflectionException
     * @throws exception
     */
    public function testValueInvalidMode(): void
    {
        $this->expectException(exception::class);
        $this->expectExceptionMessage('INVALID_STRCASE_MODE');

        $transform = $this->getTransform('get_strcase', [
          'source' => 'source',
          'field' => 'example_source_field',
          'mode' => 'example',
        ]);
        $transform->transform([
          'example_source_field' => 1,
        ]);
    }

    /**
     * Testing transforms for Errors
     * @throws ReflectionException
     * @throws exception
     */
    public function testValueValidCase1(): void
    {
        $transform = $this->getTransform('get_strcase', [
          'source' => 'source',
          'field' => 'example_source_field',
          'mode' => 'lower',
        ]);
        $result = $transform->transform([
          'example_source_field' => 'AbCdEfGhIjKlMnOpQrStUvWxYz',
        ]);

        // Make sure it stays an array
        static::assertEquals('abcdefghijklmnopqrstuvwxyz', $result);
    }

    /**
     * Testing transforms for Errors
     * @throws ReflectionException
     * @throws exception
     */
    public function testValueValidCase2(): void
    {
        $transform = $this->getTransform('get_strcase', [
          'source' => 'source',
          'field' => 'example_source_field',
          'mode' => 'upper',
        ]);
        $result = $transform->transform([
          'example_source_field' => 'AbCdEfGhIjKlMnOpQrStUvWxYz',
        ]);

        // Make sure it stays an array
        static::assertEquals('ABCDEFGHIJKLMNOPQRSTUVWXYZ', $result);
    }

    /**
     * Testing transforms for Errors
     * @throws ReflectionException
     * @throws exception
     */
    public function testValueIsNull(): void
    {
        $transform = $this->getTransform('get_strcase', [
          'source' => 'source',
          'field' => 'example_source_field',
          'mode' => 'upper',
        ]);
        $result = $transform->transform([
          'example_source_field' => null,
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
        $transform = $this->getTransform('get_strcase', [
          'source' => 'source',
          'field' => 'example_source_field',
          'mode' => 'lower',
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
