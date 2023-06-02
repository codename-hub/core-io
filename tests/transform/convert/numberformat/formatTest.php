<?php

namespace codename\core\io\tests\transform\convert\numberformat;

use codename\core\exception;
use codename\core\io\tests\transform\abstractTransformTest;
use ReflectionException;

class formatTest extends abstractTransformTest
{
    /**
     * Testing transforms for Errors
     * @throws ReflectionException
     * @throws exception
     */
    public function testValueValid(): void
    {
        $transform = $this->getTransform('convert_numberformat_format', [
          'source' => 'source',
          'field' => 'example_source_field',
          'style' => 'decimal',
          'locale' => 'en-EN',
          'fraction_digits' => 2,
          'grouping_separator_symbol' => '',
        ]);
        $result = $transform->transform([
          'example_source_field' => '1234.5678',
        ]);
        // Make sure it stays an array
        static::assertEquals('1234.57', $result);
    }


    /**
     * Tests a case with rounding,
     * and we exceed the max target digit count,
     * so we expect fewer digits in the result - but rounded with an explicit rounding_mode
     * @throws ReflectionException
     * @throws exception
     */
    public function testRoundHalfUpWithMaxFractionDigitsExceeded(): void
    {
        $transform = $this->getTransform('convert_numberformat_format', [
          'source' => 'source',
          'field' => 'example_source_field',
          'style' => 'decimal',
          'locale' => 'en-EN',
          'rounding_mode' => 'financial', // Alias
          'max_fraction_digits' => 2,
        ]);
        $result = $transform->transform([
          'example_source_field' => 1.234,
        ]);
        static::assertEquals('1.23', $result);

        $transform->reset();
        $result = $transform->transform([
          'example_source_field' => 1.235,
        ]);
        static::assertEquals('1.24', $result);
    }

    /**
     * Tests a case with rounding,
     * and we do NOT reach the max target digit count,
     * so we expect MORE digits (0-padded) in the result - but rounded with an explicit rounding_mode
     * @throws ReflectionException
     * @throws exception
     */
    public function testRoundHalfUpWithMaxFractionDigitsNotReached(): void
    {
        $transform = $this->getTransform('convert_numberformat_format', [
          'source' => 'source',
          'field' => 'example_source_field',
          'style' => 'decimal',
          'locale' => 'en-EN',
          'rounding_mode' => 'financial', // Alias
          'min_fraction_digits' => 4,
        ]);
        $result = $transform->transform([
          'example_source_field' => 1.23,
        ]);
        static::assertEquals('1.2300', $result);
    }

    /**
     * Testing transforms for Errors
     * @throws ReflectionException
     * @throws exception
     */
    public function testValueIsNull(): void
    {
        $transform = $this->getTransform('convert_numberformat_format', [
          'source' => 'source',
          'field' => 'example_source_field',
          'style' => 'decimal',
          'locale' => 'en-EN',
          'fraction_digits' => 2,
          'grouping_separator_symbol' => '',
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
        static::assertEquals('convert_numberformat_format', $errors[0]['__IDENTIFIER']);
        static::assertEquals('TRANSFORM.MISSING_VALUE', $errors[0]['__CODE']);
    }
}
