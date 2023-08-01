<?php

namespace codename\core\io\tests\transform\get;

use codename\core\exception;
use codename\core\io\tests\transform\abstractTransformTest;
use ReflectionException;

class randomstringTest extends abstractTransformTest
{
    /**
     * Testing transforms for Errors
     * @throws ReflectionException
     * @throws exception
     */
    public function testValueValid(): void
    {
        $transform = $this->getTransform('get_randomstring', [
          'chars' => 'A',
          'length' => 10,
        ]);
        $result = $transform->transform([]);

        // Make sure it stays an array
        static::assertEquals('AAAAAAAAAA', $result);
    }

    /**
     * Test Spec output (simple case)
     * @throws ReflectionException
     * @throws exception
     */
    public function testSpecification(): void
    {
        $transform = $this->getTransform('get_randomstring', [
          'chars' => 'example_source_field',
          'length' => 10,
        ]);
        static::assertEmpty($transform->getSpecification());
    }
}
