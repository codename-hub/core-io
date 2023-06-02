<?php

namespace codename\core\io\tests\transform\get;

use codename\core\io\tests\transform\abstractTransformTest;
use Exception;
use ReflectionException;

class onetimeTest extends abstractTransformTest
{
    /**
     * Testing transforms for Errors
     * @throws ReflectionException
     * @throws \codename\core\exception
     */
    public function testFunctionExistsResetCache(): void
    {
        $transform = $this->getTransform('get_onetime', [
          'source' => 'source',
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
        $transform = $this->getTransform('get_onetime', [
          'source' => 'source',
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
        $transform = $this->getTransform('get_onetime', [
          'source' => 'source',
          'field' => 'example_source_field',
        ]);
        $result = $transform->transform([
          'example_source_field' => 'example',
        ]);

        // Make sure it stays an array
        static::assertEquals('example', $result);
    }

    /**
     * Test Spec output (simple case)
     * @throws ReflectionException
     * @throws \codename\core\exception
     */
    public function testSpecification(): void
    {
        $transform = $this->getTransform('get_onetime', [
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
