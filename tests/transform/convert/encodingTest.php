<?php

namespace codename\core\io\tests\transform\convert;

use codename\core\exception;
use codename\core\io\tests\transform\abstractTransformTest;
use ReflectionException;
use ValueError;

class encodingTest extends abstractTransformTest
{
    /**
     * Testing transforms for Errors
     * @throws ReflectionException
     * @throws exception
     */
    public function testValueValid(): void
    {
        $transform = $this->getTransform('convert_encoding', [
          'source' => 'source',
          'field' => 'example_source_field',
          'from' => 'UTF-8',
          'to' => 'ASCII',
        ]);
        $result = $transform->transform([
          'example_source_field' => 'Täst',
        ]);
        // Make sure it stays an array
        static::assertEquals(mb_convert_encoding('Täst', 'ASCII', 'UTF-8'), $result);
    }

    /**
     * Testing transforms for Errors
     * @throws ReflectionException
     * @throws exception
     */
    public function testValueIsNull(): void
    {
        $transform = $this->getTransform('convert_encoding', [
          'source' => 'source',
          'field' => 'example_source_field',
          'from' => 'UTF-8',
          'to' => 'ASCII',
        ]);
        $result = $transform->transform([
          'example_source_field' => null,
        ]);
        // Make sure it stays an array
        static::assertNull($result);
    }

    /**
     * Testing transforms for Errors
     * @throws ReflectionException
     * @throws exception
     */
    public function testValueInvalid(): void
    {
        $this->expectException(ValueError::class);
        // $this->expectExceptionMessage('INVALID_OPERATOR');

        $transform = $this->getTransform('convert_encoding', [
          'source' => 'source',
          'field' => 'example_source_field',
          'from' => 'example',
          'to' => 'example',
        ]);
        $transform->transform([
          'example_source_field' => 'Täst',
        ]);
    }

    /**
     * Test Spec output (simple case)
     * @throws ReflectionException
     * @throws exception
     */
    public function testSpecification(): void
    {
        $transform = $this->getTransform('convert_encoding', [
          'source' => 'source',
          'field' => 'example_source_field',
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
