<?php

namespace codename\core\io\tests\transform\get\number;

use codename\core\exception;
use codename\core\io\tests\transform\abstractTransformTest;
use ReflectionException;

class fractionTest extends abstractTransformTest
{
    /**
     * Testing transforms for Errors
     * @throws ReflectionException
     * @throws exception
     */
    public function testValueValidCase1(): void
    {
        $transform = $this->getTransform('get_number_fraction', [
          'source' => 'source',
          'field' => 'example_source_field',
        ]);
        $result = $transform->transform([
          'example_source_field' => 123.456,
        ]);
        static::assertEquals(456, $result);
    }

    /**
     * Testing transforms for Errors
     * @throws ReflectionException
     * @throws exception
     */
    public function testValueValidCase2(): void
    {
        $transform = $this->getTransform('get_number_fraction', [
          'source' => 'source',
          'field' => 'example_source_field',
          'fraction_digits' => 2,
        ]);
        $result = $transform->transform([
          'example_source_field' => 123.456,
        ]);
        static::assertEquals(46, $result);
    }

    /**
     * Testing transforms for Errors
     * @throws ReflectionException
     * @throws exception
     */
    public function testValueIsNull(): void
    {
        $transform = $this->getTransform('get_number_fraction', [
          'source' => 'source',
          'field' => 'example_source_field',
          'required' => true,
        ]);
        $result = $transform->transform([
          'example_source_field' => null,
        ]);
        static::assertNull($result);

        $errors = $transform->getErrors();
        static::assertNotEmpty($errors);
        static::assertCount(1, $errors);
        static::assertEquals('GET_NUMBER_REQUIRED', $errors[0]['__IDENTIFIER']);
        static::assertEquals('TRANSFORM.0', $errors[0]['__CODE']);
    }

    /**
     * Testing transforms for Errors
     * @throws ReflectionException
     * @throws exception
     */
    public function testValueInvalid(): void
    {
        $this->expectException(exception::class);
        $this->expectExceptionMessage('EXCEPTION_CORE_IO_TRANSFORM_GET_NUMBER_FRACTION_NOT_NUMERIC');

        $transform = $this->getTransform('get_number_fraction', [
          'source' => 'source',
          'field' => 'example_source_field',
        ]);
        $transform->transform([
          'example_source_field' => 'example',
        ]);
    }

    /**
     * Test Spec output (simple case)
     * @throws ReflectionException
     * @throws exception
     */
    public function testSpecification(): void
    {
        $transform = $this->getTransform('get_number_fraction', [
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
