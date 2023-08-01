<?php

namespace codename\core\io\tests\transform\get;

use codename\core\exception;
use codename\core\io\pipeline;
use codename\core\io\tests\transform\abstractTransformTest;
use ReflectionException;

class arrayvalueTest extends abstractTransformTest
{
    /**
     * Testing transforms for Errors
     * @throws ReflectionException
     * @throws exception
     */
    public function testValueValidCase1(): void
    {
        $transform = $this->getTransform('get_arrayvalue', [
          'source' => 'source',
          'field' => 'example_source_field',
          'index' => 'example',
        ]);
        $result = $transform->transform([
          'example_source_field' => [
            'example' => true,
          ],
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
        $transform = $this->getTransform('get_arrayvalue', [
          'source' => 'source',
          'field' => 'example_source_field',
          'index' => [
            'source' => 'source',
            'field' => 'example_index_field',
          ],
        ]);
        $result = $transform->transform([
          'example_index_field' => 'example',
          'example_source_field' => [
            'example' => true,
          ],
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
        $transform = $this->getTransform('get_arrayvalue', [
          'source' => 'option',
          'field' => 'example_source_field',
          'index' => [
            'source' => 'source',
            'field' => 'example_index_field',
          ],
        ]);

        $pipeline = new pipeline(null, []);
        $pipeline->setOptions([
          'example_source_field' => [
            'example' => true,
          ],
        ]);
        $transform->setPipelineInstance($pipeline);

        $result = $transform->transform([
          'example_index_field' => 'example',
        ]);

        // Make sure it stays an array
        static::assertTrue($result);
    }

    /**
     * Testing transforms for Errors
     * @throws ReflectionException
     * @throws exception
     */
    public function testValueMissingValueCase1(): void
    {
        $transform = $this->getTransform('get_arrayvalue', [
          'source' => 'source',
          'field' => 'example_source_field',
          'index' => 'example_index',
          'required' => true,
        ]);
        $result = $transform->transform([
          'example_source_field' => [
            'example' => 'test',
          ],
        ]);
        // Make sure it stays an array
        static::assertNull($result, print_r($result, true));

        $errors = $transform->getErrors();
        static::assertNotEmpty($errors);
        static::assertCount(1, $errors);
        static::assertEquals('GET_ARRAYVALUE_MISSING', $errors[0]['__IDENTIFIER']);
        static::assertEquals('TRANSFORM.0', $errors[0]['__CODE']);
    }

    /**
     * Testing transforms for Errors
     * @throws ReflectionException
     * @throws exception
     */
    public function testValueMissingValueCase2(): void
    {
        $transform = $this->getTransform('get_arrayvalue', [
          'source' => 'source',
          'field' => 'example_source_field',
          'index' => [
            'source' => 'source',
            'field' => 'example_index_field',
          ],
          'required' => true,
        ]);
        $result = $transform->transform([
          'example_index' => 'example_index',
          'example_source_field' => [
            'example' => 'test',
          ],
        ]);
        // Make sure it stays an array
        static::assertNull($result, print_r($result, true));

        $errors = $transform->getErrors();
        static::assertNotEmpty($errors);
        static::assertCount(1, $errors);
        static::assertEquals('GET_ARRAYVALUE_MISSING', $errors[0]['__IDENTIFIER']);
        static::assertEquals('TRANSFORM.0', $errors[0]['__CODE']);
    }

    /**
     * Test Spec output (simple case)
     * @throws ReflectionException
     * @throws exception
     */
    public function testSpecification(): void
    {
        $transform = $this->getTransform('get_arrayvalue', [
          'source' => 'source',
          'field' => 'example_source_field',
          'index' => 'example',
        ]);
        static::assertEquals(
            [
              'type' => 'transform',
              'source' => ['source.example'],
            ],
            $transform->getSpecification()
        );
    }
}
