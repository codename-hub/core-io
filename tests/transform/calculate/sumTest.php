<?php

namespace codename\core\io\tests\transform\calculate;

use codename\core\exception;
use codename\core\io\tests\transform\abstractTransformTest;
use ReflectionException;

class sumTest extends abstractTransformTest
{
    /**
     * Testing transforms for Errors
     * @throws ReflectionException
     * @throws exception
     */
    public function testValueValid(): void
    {
        $transform = $this->getTransform('calculate_sum', [
          'fields' => [
            ['source' => 'source', 'field' => 'example_source_field1'],
            1.2345,
          ],
          'precision' => 2,
        ]);
        $result = $transform->transform([
          'example_source_field1' => 1.2345,
        ]);
        // Make sure it stays an array
        static::assertEquals(bcadd(1.2345, 1.2345, 2), $result);
    }

    /**
     * Test Spec output (simple case)
     * @throws ReflectionException
     * @throws exception
     */
    public function testSpecification(): void
    {
        $transform = $this->getTransform('calculate_sum', [
          'fields' => [
            ['source' => 'source', 'field' => 'example_source_field1'],
            'example_source_field2',
          ],
          'precision' => 2,
        ]);
        static::assertEquals(
            [
              'type' => 'transform',
              'source' => ['source.example_source_field1'],
            ],
            $transform->getSpecification()
        );
    }
}
