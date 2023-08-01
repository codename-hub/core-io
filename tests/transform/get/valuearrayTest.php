<?php

namespace codename\core\io\tests\transform\get;

use codename\core\exception;
use codename\core\io\tests\transform\abstractTransformTest;
use ReflectionException;

class valuearrayTest extends abstractTransformTest
{
    /**
     * Testing transforms for Errors
     * @throws ReflectionException
     * @throws exception
     */
    public function testValue(): void
    {
        $transform = $this->getTransform('get_valuearray', [
          'elements' => [
            'country' => 'DE',
            'zipcode' => ['source' => 'source', 'field' => 'example_zipcode_field'],
            'city' => ['source' => 'source', 'field' => 'example_city_field'],
            'street' => ['source' => 'source', 'field' => 'example_street_field'],
            'houseno' => ['source' => 'source', 'field' => 'example_houseno_field'],
          ],
        ]);
        $result = $transform->transform([
          'example_zipcode_field' => '01067',
          'example_city_field' => 'Dresden',
          'example_street_field' => 'Adlergasse',
          'example_houseno_field' => '1',
        ]);

        // Make sure it stays an array
        static::assertEquals([
          'country' => 'DE',
          'zipcode' => '01067',
          'city' => 'Dresden',
          'street' => 'Adlergasse',
          'houseno' => '1',
        ], $result);
    }

    /**
     * Testing transforms for Errors
     * @throws ReflectionException
     * @throws exception
     */
    public function testValueRequired(): void
    {
        $transform = $this->getTransform('get_valuearray', [
          'elements' => [
            'country' => 'DE',
            'zipcode' => ['source' => 'source', 'field' => 'example_zipcode_field'],
            'city' => ['source' => 'source', 'field' => 'example_city_field'],
            'street' => ['source' => 'source', 'field' => 'example_street_field'],
            'houseno' => ['source' => 'source', 'field' => 'example_houseno_field', 'required' => true],
          ],
        ]);
        $result = $transform->transform([
          'example_zipcode_field' => '01067',
          'example_city_field' => 'Dresden',
          'example_street_field' => null,
          'example_houseno_field' => null,
        ]);

        // Make sure it stays an array
        static::assertEquals([
          'country' => 'DE',
          'zipcode' => '01067',
          'city' => 'Dresden',
        ], $result);

        $errors = $transform->getErrors();
        static::assertNotEmpty($errors);
        static::assertCount(1, $errors);
        static::assertEquals('GET_VALUEARRAY_MISSING_KEY', $errors[0]['__IDENTIFIER']);
        static::assertEquals('TRANSFORM.0', $errors[0]['__CODE']);
    }

    /**
     * [testAllowNull description]
     * @throws ReflectionException
     * @throws exception
     */
    public function testAllowNull(): void
    {
        $transform = $this->getTransform('get_valuearray', [
          'elements' => [
            'country' => 'DE',
            'zipcode' => ['source' => 'source', 'field' => 'example_zipcode_field'],
            'city' => ['source' => 'source', 'field' => 'example_city_field'],
            'street' => ['source' => 'source', 'field' => 'example_street_field', 'allow_null' => true],
            'houseno' => ['source' => 'source', 'field' => 'example_houseno_field', 'required' => true],
          ],
        ]);
        $result = $transform->transform([
          'example_zipcode_field' => '01067',
          'example_city_field' => 'Dresden',
          'example_street_field' => null, // allowed, expect passthrough of NULL value.
          'example_houseno_field' => null, // not allowed, key should not be present in result.
        ]);

        // Make sure it stays an array
        static::assertEquals([
          'country' => 'DE',
          'zipcode' => '01067',
          'city' => 'Dresden',
          'street' => null,
        ], $result);

        // We will still receive one error for one key
        $errors = $transform->getErrors();
        static::assertNotEmpty($errors);
        static::assertCount(1, $errors);
        static::assertEquals('GET_VALUEARRAY_MISSING_KEY', $errors[0]['__IDENTIFIER']);
        static::assertEquals('TRANSFORM.0', $errors[0]['__CODE']);
    }

    /**
     * Test Spec output (simple case)
     * @throws ReflectionException
     * @throws exception
     */
    public function testSpecification(): void
    {
        $transform = $this->getTransform('get_valuearray', [
          'elements' => [
            'country' => 'DE',
            'zipcode' => ['source' => 'source', 'field' => 'example_zipcode_field'],
            'city' => ['source' => 'source', 'field' => 'example_city_field'],
            'street' => ['source' => 'source', 'field' => 'example_street_field'],
            'houseno' => ['source' => 'source', 'field' => 'example_houseno_field'],
            'example' => ['source' => 'source', 'field' => ['example', 'example']],
          ],
        ]);
        static::assertEquals(
            [
              'type' => 'transform',
              'source' => [
                'zipcode' => 'source.example_zipcode_field',
                'city' => 'source.example_city_field',
                'street' => 'source.example_street_field',
                'houseno' => 'source.example_houseno_field',
                'example' => 'source.example.example',
              ],
            ],
            $transform->getSpecification()
        );
    }
}
