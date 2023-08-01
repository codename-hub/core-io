<?php

namespace codename\core\io\tests\transform\convert;

use codename\core\exception;
use codename\core\io\tests\transform\abstractTransformTest;
use LogicException;
use ReflectionException;

class jsonTest extends abstractTransformTest
{
    /**
     * Testing transforms for Errors
     * @throws ReflectionException
     * @throws exception
     */
    public function testValueValidEncode(): void
    {
        $transform = $this->getTransform('convert_json', [
          'source' => 'source',
          'field' => 'example_source_field',
          'mode' => 'encode',
        ]);
        $result = $transform->transform([
          'example_source_field' => ['example' => true],
        ]);
        // Make sure it stays an array
        static::assertEquals(json_encode(['example' => true]), $result);
    }

    /**
     * Testing transforms for Errors
     * @throws ReflectionException
     * @throws exception
     */
    public function testValueValidDecode(): void
    {
        $transform = $this->getTransform('convert_json', [
          'source' => 'source',
          'field' => 'example_source_field',
          'mode' => 'decode',
        ]);
        $result = $transform->transform([
          'example_source_field' => json_encode(['example' => true]),
        ]);
        // Make sure it stays an array
        static::assertEquals(['example' => true], $result);
    }

    /**
     * Testing transforms for Errors
     * @throws ReflectionException
     * @throws exception
     */
    public function testValueIsNull(): void
    {
        $transform = $this->getTransform('convert_json', [
          'source' => 'source',
          'field' => 'example_source_field',
          'mode' => 'encode',
        ]);
        $result = $transform->transform([
          'example_source_field' => null,
        ]);
        // Make sure it stays an array
        static::assertNull($result);
    }

    /**
     * Testing transforms for Errors
     * @throws ReflectionException
     * @throws exception
     */
    public function testValueInvalid(): void
    {
        $this->expectException(LogicException::class);
        //
        $transform = $this->getTransform('convert_json', [
          'source' => 'source',
          'field' => 'example_source_field',
          'mode' => 'example',
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
        $transform = $this->getTransform('convert_json', [
          'source' => 'source',
          'field' => 'example_source_field',
          'mode' => 'encode',
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
