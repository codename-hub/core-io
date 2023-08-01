<?php

namespace codename\core\io\tests\transform\convert;

use codename\core\exception;
use codename\core\io\pipeline;
use codename\core\io\tests\transform\abstractTransformTest;
use ReflectionException;

class numberformatTest extends abstractTransformTest
{
    /**
     * Testing transforms for Errors
     * @throws ReflectionException
     * @throws exception
     */
    public function testValueValidCase1(): void
    {
        $transform = $this->getTransform('convert_numberformat', [
          'source' => 'source',
          'field' => 'example_source_field',
          'style' => 'decimal',
          'locale' => 'en-EN',
        ]);
        $result = $transform->transform([
          'example_source_field' => 1.234,
        ]);
        // Make sure it stays an array
        static::assertEquals(1.234, $result);
    }

    /**
     * Testing transforms for Errors
     * @throws ReflectionException
     * @throws exception
     */
    public function testValueValidCase2(): void
    {
        $this->expectException(exception::class);
        $this->expectExceptionMessage('TRANSFORM_NUMBERFORMAT_CONFIG_LOCALE_INVALID_SOURCE');

        $transform = $this->getTransform('convert_numberformat', [
          'source' => 'source',
          'field' => 'example_source_field',
          'style' => [
            'source' => 'source',
            'field' => 'example_style_field',
          ],
          'locale' => [
            'source' => 'source',
            'field' => 'example_locale_field',
          ],
        ]);
        $result = $transform->transform([
          'example_source_field' => 1.2345,
          'example_style_field' => 'decimal',
          'example_locale_field' => 'en-EN',
        ]);
        // Make sure it stays an array
        static::assertEquals('1.2345', $result);
    }

    /**
     * Testing transforms for Errors
     * @throws ReflectionException
     * @throws exception
     */
    public function testValueValidCase3(): void
    {
        $this->expectException(exception::class);
        $this->expectExceptionMessage('TRANSFORM_NUMBERFORMAT_CONFIG_STYLE_INVALID_SOURCE');

        $transform = $this->getTransform('convert_numberformat', [
          'source' => 'source',
          'field' => 'example_source_field',
          'style' => [
            'source' => 'source',
            'field' => 'example_style_field',
          ],
          'locale' => 'en-EN',
        ]);
        $transform->transform([
          'example_source_field' => 1.2345,
        ]);
    }

    /**
     * Testing transforms for Errors
     * @throws ReflectionException
     * @throws exception
     */
    public function testValueValidCase4(): void
    {
        $transform = $this->getTransform('convert_numberformat', [
          'source' => 'source',
          'field' => 'example_source_field',
          'style' => 'decimal',
          'locale' => [
            'source' => 'option',
            'field' => 'example_locale_field',
          ],
        ]);

        $pipeline = new pipeline(null, []);
        $pipeline->setOptions([
          'example_style_field' => 'decimal',
          'example_locale_field' => 'en-EN',
        ]);
        $transform->setPipelineInstance($pipeline);

        $result = $transform->transform([
          'example_source_field' => 1.2345,
        ]);
        // Make sure it stays an array
        static::assertEquals('1.2345', $result);
    }

    /**
     * Testing transforms for Errors
     * @throws ReflectionException
     * @throws exception
     */
    public function testValueValidCase5(): void
    {
        $transform = $this->getTransform('convert_numberformat', [
          'source' => 'source',
          'field' => 'example_source_field',
          'style' => [
            'source' => 'option',
            'field' => 'example_style_field',
          ],
          'locale' => [
            'source' => 'option',
            'field' => 'example_locale_field',
          ],
        ]);

        $pipeline = new pipeline(null, []);
        $pipeline->setOptions([
          'example_style_field' => 'decimal',
          'example_locale_field' => 'en-EN',
        ]);
        $transform->setPipelineInstance($pipeline);

        $result = $transform->transform([
          'example_source_field' => 1.2345,
        ]);
        // Make sure it stays an array
        static::assertEquals('1.2345', $result);
    }

    /**
     * Testing transforms for Errors
     * @throws ReflectionException
     * @throws exception
     */
    public function testValueWrongStyle(): void
    {
        $this->expectException(exception::class);
        $this->expectExceptionMessage('EXCEPTION_CORE_IO_TRANSFORM_CONVERT_NUMBERFORMAT_INVALID_TYPE');

        $this->getTransform('convert_numberformat', [
          'source' => 'source',
          'field' => 'example_source_field',
          'style' => 'example',
          'locale' => 'en-EN',
        ]);
    }

    /**
     * Testing transforms for Errors
     * @throws ReflectionException
     * @throws exception
     */
    public function testValueInvalidParse(): void
    {
        $transform = $this->getTransform('convert_numberformat', [
          'source' => 'source',
          'field' => 'example_source_field',
          'style' => 'decimal',
          'locale' => 'en-EN',
        ]);

        $result = $transform->transform([
          'example_source_field' => '123,2345',
        ]);
        // Make sure it stays an array
        static::assertFalse($result);

        $errors = $transform->getErrors();
        static::assertNotEmpty($errors);
        static::assertCount(1, $errors);
        static::assertEquals('convert_numberformat', $errors[0]['__IDENTIFIER']);
        static::assertEquals('TRANSFORM.INVALID_PARSE', $errors[0]['__CODE']);
    }

    /**
     * Test Spec output (simple case)
     * @throws ReflectionException
     * @throws exception
     */
    public function testSpecification(): void
    {
        $transform = $this->getTransform('convert_numberformat', [
          'source' => 'source',
          'field' => 'example_source_field',
          'style' => 'decimal',
          'locale' => 'de-DE',
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
