<?php

namespace codename\core\io\tests\transform;

use codename\core\exception;
use codename\core\io\transform\deepaccess;
use LogicException;
use ReflectionException;

class deepaccessTest extends abstractTransformTest
{
    /**
     * Testing transforms for Errors
     * @throws ReflectionException
     * @throws exception
     */
    public function testConstructValid(): void
    {
        $transform = $this->getTransform('deepaccess', [
          'source' => 'source',
          'field' => 'example_source_field',
          'path' => [],
        ]);

        static::assertInstanceOf(deepaccess::class, $transform);
    }

    /**
     * Testing transforms for Errors
     * @throws ReflectionException
     * @throws exception
     */
    public function testValueIsNull(): void
    {
        $transform = $this->getTransform('deepaccess', [
          'source' => 'source',
            // 'field'     => 'example_source_field',
          'required' => true,
        ]);
        $result = $transform->transform([
          'example_source_field',
        ]);
        // Make sure it stays an array
        static::assertEquals(null, $result);
    }

    /**
     * Testing transforms for Errors
     * @throws ReflectionException
     * @throws exception
     */
    public function testValueValid(): void
    {
        $transform = $this->getTransform('deepaccess', [
          'source' => 'source',
          'field' => 'example_source_field',
        ]);
        $result = $transform->transform([
          'example_source_field' => 'test',
        ]);
        static::assertEmpty($result);
    }

    /**
     * Test Spec output (simple case)
     * @throws ReflectionException
     * @throws exception
     */
    public function testSpecification(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Not implemented');

        $transform = $this->getTransform('deepaccess', [
          'source' => 'source',
          'field' => 'example_source_field',
        ]);
        $transform->getSpecification();
    }
}
