<?php

namespace codename\core\io\tests\transform\get;

use codename\core\io\pipeline;
use codename\core\io\tests\transform\abstractTransformTest;
use Exception;
use ReflectionException;

class optionTest extends abstractTransformTest
{
    /**
     * Testing transforms for Errors
     * @throws ReflectionException
     * @throws \codename\core\exception
     */
    public function testFunctionExistsResetCache(): void
    {
        $transform = $this->getTransform('get_option', [
          'field' => 'example_source_field',
        ]);
        try {
            $transform->resetCache();
        } catch (Exception) {
            static::fail();
        }

        static::assertTrue(true);
    }

    /**
     * Testing transforms for Errors
     * @throws ReflectionException
     * @throws \codename\core\exception
     */
    public function testFunctionExistsResetErrors(): void
    {
        $transform = $this->getTransform('get_option', [
          'field' => 'example_source_field',
        ]);
        try {
            $transform->resetErrors();
        } catch (Exception) {
            static::fail();
        }

        static::assertTrue(true);
    }

    /**
     * Testing transforms for Errors
     * @throws ReflectionException
     * @throws \codename\core\exception
     */
    public function testValueValid(): void
    {
        $transform = $this->getTransform('get_option', [
          'field' => 'example_source_field',
        ]);

        $pipeline = new pipeline(null, []);
        $pipeline->setOptions([
          'example_source_field' => 'example',
        ]);
        $transform->setPipelineInstance($pipeline);

        $result = $transform->transform([]);

        // Make sure it stays an array
        static::assertEquals('example', $result);
    }

    /**
     * Testing transforms for Errors
     * @throws ReflectionException
     * @throws \codename\core\exception
     */
    public function testValueIsNull(): void
    {
        $transform = $this->getTransform('get_option', [
          'field' => 'example_source_field',
          'required' => true,
        ]);

        $pipeline = new pipeline(null, []);
        $pipeline->setOptions([
          'example_source_field' => null,
        ]);
        $transform->setPipelineInstance($pipeline);

        $result = $transform->transform([]);

        // Make sure it stays an array
        static::assertNull($result);

        $errors = $transform->getErrors();
        static::assertNotEmpty($errors);
        static::assertCount(1, $errors);
        static::assertEquals('OPTION_VALUE_NULL', $errors[0]['__IDENTIFIER']);
        static::assertEquals('TRANSFORM.0', $errors[0]['__CODE']);
    }

    /**
     * Test Spec output (simple case)
     * @throws ReflectionException
     * @throws \codename\core\exception
     */
    public function testSpecification(): void
    {
        $transform = $this->getTransform('get_option', [
          'field' => 'example_source_field',
        ]);
        static::assertEquals(
            [
              'type' => 'transform',
              'source' => ['option.example_source_field'],
            ],
            $transform->getSpecification()
        );
    }
}
