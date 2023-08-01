<?php

namespace codename\core\io\tests\transform\get\filtered;

use codename\core\exception;
use codename\core\io\tests\transform\abstractTransformTest;
use ReflectionException;

class validatedTest extends abstractTransformTest
{
    /**
     * Testing transforms for Errors
     * @throws ReflectionException
     * @throws exception
     */
    public function testValueValid(): void
    {
        $transform = $this->getTransform('get_filtered_validated', [
          'source' => 'source',
          'field' => 'example_source_field',
          'validator' => [
            'text_bic',
          ],
        ]);
        $result = $transform->transform([
          'example_source_field' => 'GENODEF1BEB',
        ]);

        // Make sure it stays an array
        static::assertEquals('GENODEF1BEB', $result);
    }

    /**
     * Testing transforms for Errors
     * @throws ReflectionException
     * @throws exception
     */
    public function testValueInvalid(): void
    {
        $transform = $this->getTransform('get_filtered_validated', [
          'source' => 'source',
          'field' => 'example_source_field',
          'validator' => 'text_bic',
        ]);
        $result = $transform->transform([
          'example_source_field' => '12345678901',
        ]);

        // Make sure it stays an array
        static::assertNull($result);

        $errors = $transform->getErrors();
        static::assertNotEmpty($errors);
        static::assertCount(1, $errors);
        static::assertEquals('VALIDATION.VALUE_NOT_A_BIC', $errors[0]['__CODE']);
    }

    /**
     * Testing transforms for Errors
     * @throws ReflectionException
     * @throws exception
     */
    public function testValueMissingValidator(): void
    {
        $this->expectException(exception::class);

        $this->getTransform('get_filtered_validated', [
          'source' => 'source',
          'field' => 'example_source_field',
        ]);
    }
}
