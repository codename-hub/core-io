<?php

namespace codename\core\io\tests\transform\get;

use codename\core\exception;
use codename\core\io\tests\transform\abstractTransformTest;
use DateTime;
use ReflectionException;

class currentdatetimeTest extends abstractTransformTest
{
    /**
     * Testing transforms for Errors
     * @throws ReflectionException
     * @throws exception
     */
    public function testValueValid(): void
    {
        $transform = $this->getTransform('get_currentdatetime', [
          'modify' => '+1 day',
          'format' => 'Y-m-d',
        ]);
        $result = $transform->transform([]);
        // Make sure it stays an array
        static::assertEquals((new DateTime('now'))->modify('+1 day')->format('Y-m-d'), $result);
    }

    /**
     * Test Spec output (simple case)
     * @throws ReflectionException
     * @throws exception
     */
    public function testSpecification(): void
    {
        $transform = $this->getTransform('get_currentdatetime', [
          'modify' => '+1 day',
          'format' => 'Y-m-d',
        ]);
        static::assertEquals(
            [
              'type' => 'transform',
              'source' => [],
            ],
            $transform->getSpecification()
        );
    }
}
