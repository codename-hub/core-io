<?php

namespace codename\core\io\tests\transform\pad;

use codename\core\exception;
use codename\core\io\tests\transform\abstractTransformTest;
use ReflectionException;

class rightTest extends abstractTransformTest
{
    /**
     * Testing transforms for Errors
     * @throws ReflectionException
     * @throws exception
     */
    public function testValueValid(): void
    {
        $transform = $this->getTransform('pad_right', [
          'source' => 'source',
          'field' => 'example_source_field',
          'length' => 10,
          'string' => ' ',
        ]);
        $result = $transform->transform([
          'example_source_field' => 'example',
        ]);
        static::assertEquals('example   ', $result);
    }

    /**
     * [testNullValuePadding description]
     * @throws ReflectionException
     * @throws exception
     */
    public function testNullValuePadding(): void
    {
        $transform = $this->getTransform('pad_right', [
          'source' => 'source',
          'field' => 'example_source_field',
          'length' => 10,
          'string' => ' ',
        ]);
        //
        // NOTE: at the moment, str_pad using a NULL input value
        // also pads the string, as it was an empty string.
        //
        $result = $transform->transform([
          'example_source_field' => null,
        ]);
        static::assertEquals('          ', $result);
    }

    /**
     * [testEmptyStringPadding description]
     * @throws ReflectionException
     * @throws exception
     */
    public function testEmptyStringPadding(): void
    {
        $transform = $this->getTransform('pad_right', [
          'source' => 'source',
          'field' => 'example_source_field',
          'length' => 10,
          'string' => ' ',
        ]);
        $result = $transform->transform([
          'example_source_field' => '',
        ]);
        static::assertEquals('          ', $result);
    }

    /**
     * [testValueExceedsPadLength description]
     * @throws ReflectionException
     * @throws exception
     */
    public function testValueExceedsPadLength(): void
    {
        $transform = $this->getTransform('pad_right', [
          'source' => 'source',
          'field' => 'example_source_field',
          'length' => 10,
          'string' => ' ',
        ]);
        $result = $transform->transform([
          'example_source_field' => 'exampleexample',
        ]);
        // Make sure the strings stays the same,
        // as we only pad, if there are characters missing
        static::assertEquals('exampleexample', $result);
    }

    /**
     * Test Spec output (simple case)
     * @throws ReflectionException
     * @throws exception
     */
    public function testSpecification(): void
    {
        $transform = $this->getTransform('pad_right', [
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
