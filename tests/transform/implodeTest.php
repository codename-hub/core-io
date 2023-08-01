<?php

namespace codename\core\io\tests\transform;

use codename\core\exception;
use ReflectionException;

class implodeTest extends abstractTransformTest
{
    /**
     * Tests for default glue ('')
     * @throws ReflectionException
     * @throws exception
     */
    public function testDefaultGlue(): void
    {
        $transform = $this->getTransform('implode', [
          'fields' => [
            ['source' => 'source', 'field' => 'example_source_field1'],
            ['source' => 'source', 'field' => 'example_source_field2'],
          ],
        ]);
        $result = $transform->transform([
          'example_source_field1' => 'value1',
          'example_source_field2' => 'value2',
        ]);
        static::assertEquals('value1value2', $result);
    }

    /**
     * [testCustomGlue description]
     * @throws ReflectionException
     * @throws exception
     */
    public function testCustomGlue(): void
    {
        $transform = $this->getTransform('implode', [
          'glue' => ';',
          'fields' => [
            ['source' => 'source', 'field' => 'example_source_field1'],
            ['source' => 'source', 'field' => 'example_source_field2'],
          ],
        ]);
        $result = $transform->transform([
          'example_source_field1' => 'value1',
          'example_source_field2' => 'value2',
        ]);
        static::assertEquals('value1;value2', $result);
    }

    /**
     * Tests for providing a constant value in fields
     * but with disabled allowConstants config
     * @throws ReflectionException
     * @throws exception
     */
    public function testAllowConstantsDisabled(): void
    {
        $transform = $this->getTransform('implode', [
          'glue' => ';',
          'fields' => [
            ['source' => 'source', 'field' => 'example_source_field1'],
            '-constant-',
            ['source' => 'source', 'field' => 'example_source_field2'],
          ],
          'fallbackValue' => 'MISSING',
        ]);
        $result = $transform->transform([
          'example_source_field1' => 'value1',
          'example_source_field2' => 'value2',
        ]);
        static::assertEquals('value1;MISSING;value2', $result);
    }

    /**
     * Tests for source field data retrieval on constant value as field
     * but with disabled allowConstants
     * @throws ReflectionException
     * @throws exception
     */
    public function testAllowConstantsDisabledSourceFallback(): void
    {
        $transform = $this->getTransform('implode', [
          'glue' => ';',
          'fields' => [
            ['source' => 'source', 'field' => 'example_source_field1'],
            'example_source_field1', // existing name in source
            ['source' => 'source', 'field' => 'example_source_field2'],
          ],
          'fallbackValue' => 'MISSING',
        ]);
        $result = $transform->transform([
          'example_source_field1' => 'value1',
          'example_source_field2' => 'value2',
        ]);
        static::assertEquals('value1;value1;value2', $result);
    }

    /**
     * Tests for providing a constant value in fields
     * but with enabled allowConstants config
     * @throws ReflectionException
     * @throws exception
     */
    public function testAllowConstantsEnabled(): void
    {
        $transform = $this->getTransform('implode', [
          'glue' => ';',
          'fields' => [
            ['source' => 'source', 'field' => 'example_source_field1'],
            '-constant-',
            ['source' => 'source', 'field' => 'example_source_field2'],
          ],
          'allowConstants' => true,
          'fallbackValue' => 'MISSING',
        ]);
        $result = $transform->transform([
          'example_source_field1' => 'value1',
          'example_source_field2' => 'value2',
        ]);
        static::assertEquals('value1;-constant-;value2', $result);
    }

    /**
     * Test Spec output (simple case)
     * @throws ReflectionException
     * @throws exception
     */
    public function testSpecification(): void
    {
        $transform = $this->getTransform('implode', [
          'fields' => [
            ['source' => 'source', 'field' => 'example_source_field1'],
            ['source' => 'source', 'field' => 'example_source_field2'],
          ],
        ]);
        static::assertEquals(
            [
              'type' => 'transform',
              'source' => [
                'source.example_source_field1',
                'source.example_source_field2',
              ],
            ],
            $transform->getSpecification()
        );

        $transform = $this->getTransform('implode', [
          'fields' => [
            ['source' => 'source', 'field' => 'example_source_field1'],
            'some_implicit_source_field',
          ],
        ]);
        static::assertEquals(
            [
              'type' => 'transform',
              'source' => [
                'source.example_source_field1',
                'source.some_implicit_source_field',
              ],
            ],
            $transform->getSpecification()
        );

        $transform = $this->getTransform('implode', [
          'allowConstants' => true,
          'fields' => [
            ['source' => 'source', 'field' => 'example_source_field1'],
            'some_constant',
          ],
        ]);
        static::assertEquals(
            [
              'type' => 'transform',
              'source' => [
                'source.example_source_field1',
                'some_constant',
              ],
            ],
            $transform->getSpecification()
        );
    }
}
