<?php

namespace codename\core\io\tests\transform;

use codename\core\exception;
use LogicException;
use ReflectionException;

class regexTest extends abstractTransformTest
{
    /**
     * Testing transforms for Errors
     * @throws ReflectionException
     * @throws exception
     */
    public function testConstructInvalid(): void
    {
        $this->expectException(exception::class);
        $this->expectExceptionMessage('TRANSFORM_REGEX_INVALID_CONFIG');

        $transform = $this->getTransform('regex', [
          'regex_value' => '/^[0-9]*(\\,[0-9]{3})*(\\.[0-9]*){0,1}$/',
          'mode' => 'match_success_error',
          'source' => 'source',
          'field' => 'example_source_field',
        ]);
        $transform->transform([
          'example_source_field' => null,
        ]);
    }

    /**
     * Testing transforms for Errors
     * @throws ReflectionException
     * @throws exception
     */
    public function testValueValidMatch(): void
    {
        $transform = $this->getTransform('regex', [
          'regex_value' => '/^[0-9]*(\\,[0-9]{3})*(\\.[0-9]*){0,1}$/',
          'mode' => 'match',
          'source' => 'source',
          'field' => 'example_source_field',
        ]);
        $result = $transform->transform([
          'example_source_field' => '1.23',
        ]);
        // Make sure it stays an array
        static::assertEquals([
          '1.23',
          '',
          '.23',
        ], $result);
    }

    /**
     * Testing transforms for Errors
     * @throws ReflectionException
     * @throws exception
     */
    public function testValueValidMatchSuccess(): void
    {
        $transform = $this->getTransform('regex', [
          'regex_value' => '/^[0-9]*(\\,[0-9]{3})*(\\.[0-9]*){0,1}$/',
          'mode' => 'match_success',
          'source' => 'source',
          'field' => 'example_source_field',
        ]);
        $result = $transform->transform([
          'example_source_field' => '1.23',
        ]);
        // Make sure it stays an array
        static::assertTrue($result);
    }

    /**
     * Testing transforms for Errors
     * @throws ReflectionException
     * @throws exception
     */
    public function testValueValidReplace(): void
    {
        $this->expectException(LogicException::class);

        $transform = $this->getTransform('regex', [
          'regex_value' => '/^[0-9]*(\\,[0-9]{3})*(\\.[0-9]*){0,1}$/',
          'mode' => 'replace',
          'source' => 'source',
          'field' => 'example_source_field',
        ]);
        $transform->transform([
          'example_source_field' => '1.23',
        ]);
    }

    /**
     * Testing transforms for Errors
     * @throws ReflectionException
     * @throws exception
     */
    public function testValueInvalidMatch(): void
    {
        $transform = $this->getTransform('regex', [
          'regex_value' => '/^[0-9]*(\\,[0-9]{3})*(\\.[0-9]*){0,1}$/',
          'mode' => 'match',
          'source' => 'source',
          'field' => 'example_source_field',
        ]);
        $result = $transform->transform([
          'example_source_field' => '-1.23',
        ]);
        // Make sure it stays an array
        static::assertEquals(null, $result);
    }

    /**
     * Testing transforms for Errors
     * @throws ReflectionException
     * @throws exception
     */
    public function testValueInvalidMatchSuccess(): void
    {
        $transform = $this->getTransform('regex', [
          'regex_value' => '/^[0-9]*(\\,[0-9]{3})*(\\.[0-9]*){0,1}$/',
          'mode' => 'match_success',
          'source' => 'source',
          'field' => 'example_source_field',
        ]);
        $result = $transform->transform([
          'example_source_field' => '-1.23',
        ]);
        // Make sure it stays an array
        static::assertFalse($result);
    }

    /**
     * Testing transforms for Errors
     * @throws ReflectionException
     * @throws exception
     */
    public function testValueError(): void
    {
        $transform = $this->getTransform('regex', [
          'regex_value' => '/(?:\D+|<\d+>)*[!?]/',
          'mode' => 'match',
          'source' => 'source',
          'field' => 'example_source_field',
        ]);
        $result = $transform->transform([
          'example_source_field' => 'foobar foobar foobar',
        ]);
        // Make sure it stays an array
        static::assertEquals(null, $result);

        $errors = $transform->getErrors();
        static::assertNotEmpty($errors);
        static::assertCount(1, $errors);
        static::assertEquals('REGEX_ERROR', $errors[0]['__IDENTIFIER']);
        static::assertEquals('TRANSFORM.0', $errors[0]['__CODE']);
    }

    /**
     * Test Spec output (simple case)
     * @throws ReflectionException
     * @throws exception
     */
    public function testSpecification(): void
    {
        $transform = $this->getTransform('regex', [
          'regex_value' => '/^[0-9]*(\\,[0-9]{3})*(\\.[0-9]*){0,1}$/',
          'mode' => 'match_success',
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
