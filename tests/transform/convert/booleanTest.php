<?php

namespace codename\core\io\tests\transform\convert;

use codename\core\exception;
use codename\core\io\tests\transform\abstractTransformTest;
use ReflectionException;

class booleanTest extends abstractTransformTest
{
    /**
     * Testing transforms for Errors
     * @throws ReflectionException
     * @throws exception
     */
    public function testValueTrueCase1(): void
    {
        $transform = $this->getTransform('convert_boolean', [
          'source' => 'source',
          'field' => 'example_source_field',
        ]);
        $result = $transform->transform([
          'example_source_field' => true,
        ]);
        // Make sure it stays an array
        static::assertTrue($result);
    }

    /**
     * Testing transforms for Errors
     * @throws ReflectionException
     * @throws exception
     */
    public function testValueTrueCase2(): void
    {
        $transform = $this->getTransform('convert_boolean', [
          'source' => 'source',
          'field' => 'example_source_field',
        ]);
        $result = $transform->transform([
          'example_source_field' => 1,
        ]);
        // Make sure it stays an array
        static::assertTrue($result);
    }

    /**
     * Testing transforms for Errors
     * @throws ReflectionException
     * @throws exception
     */
    public function testValueTrueCase3(): void
    {
        $transform = $this->getTransform('convert_boolean', [
          'source' => 'source',
          'field' => 'example_source_field',
        ]);
        $result = $transform->transform([
          'example_source_field' => 'true',
        ]);
        // Make sure it stays an array
        static::assertTrue($result);
    }

    /**
     * Testing transforms for Errors
     * @throws ReflectionException
     * @throws exception
     */
    public function testValueTrueCase4(): void
    {
        $transform = $this->getTransform('convert_boolean', [
          'source' => 'source',
          'field' => 'example_source_field',
        ]);
        $result = $transform->transform([
          'example_source_field' => '1',
        ]);
        // Make sure it stays an array
        static::assertTrue($result);
    }

    /**
     * Testing transforms for Errors
     * @throws ReflectionException
     * @throws exception
     */
    public function testValueFalseCase1(): void
    {
        $transform = $this->getTransform('convert_boolean', [
          'source' => 'source',
          'field' => 'example_source_field',
        ]);
        $result = $transform->transform([
          'example_source_field' => false,
        ]);
        // Make sure it stays an array
        static::assertFalse($result);
    }

    /**
     * Testing transforms for Errors
     * @throws ReflectionException
     * @throws exception
     */
    public function testValueFalseCase2(): void
    {
        $transform = $this->getTransform('convert_boolean', [
          'source' => 'source',
          'field' => 'example_source_field',
        ]);
        $result = $transform->transform([
          'example_source_field' => 0,
        ]);
        // Make sure it stays an array
        static::assertFalse($result);
    }

    /**
     * Testing transforms for Errors
     * @throws ReflectionException
     * @throws exception
     */
    public function testValueFalseCase3(): void
    {
        $transform = $this->getTransform('convert_boolean', [
          'source' => 'source',
          'field' => 'example_source_field',
        ]);
        $result = $transform->transform([
          'example_source_field' => 'false',
        ]);
        // Make sure it stays an array
        static::assertFalse($result);
    }

    /**
     * Testing transforms for Errors
     * @throws ReflectionException
     * @throws exception
     */
    public function testValueFalseCase4(): void
    {
        $transform = $this->getTransform('convert_boolean', [
          'source' => 'source',
          'field' => 'example_source_field',
        ]);
        $result = $transform->transform([
          'example_source_field' => '0',
        ]);
        // Make sure it stays an array
        static::assertFalse($result);
    }

    /**
     * Testing transforms for Errors
     * @throws ReflectionException
     * @throws exception
     */
    public function testValueRequired(): void
    {
        $transform = $this->getTransform('convert_boolean', [
          'source' => 'source',
          'field' => 'example_source_field',
          'required' => true,
        ]);
        $result = $transform->transform([
          'example_source_field' => null,
        ]);
        // Make sure it stays an array
        static::assertNull($result);

        $errors = $transform->getErrors();
        static::assertNotEmpty($errors);
        static::assertCount(1, $errors);
        static::assertEquals('convert_boolean', $errors[0]['__IDENTIFIER']);
        static::assertEquals('TRANSFORM.MISSING_VALUE', $errors[0]['__CODE']);
    }

    /**
     * Testing transforms for Errors
     * @throws ReflectionException
     * @throws exception
     */
    public function testValueInvalidValue(): void
    {
        $transform = $this->getTransform('convert_boolean', [
          'source' => 'source',
          'field' => 'example_source_field',
        ]);
        $result = $transform->transform([
          'example_source_field' => 'ABC',
        ]);
        // Make sure it stays an array
        static::assertNull($result);

        $errors = $transform->getErrors();
        static::assertNotEmpty($errors);
        static::assertCount(1, $errors);
        static::assertEquals('convert_boolean', $errors[0]['__IDENTIFIER']);
        static::assertEquals('TRANSFORM.INVALID_VALUE', $errors[0]['__CODE']);
    }

    /**
     * Test Spec output (simple case)
     * @throws ReflectionException
     * @throws exception
     */
    public function testSpecification(): void
    {
        $transform = $this->getTransform('convert_boolean', [
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
