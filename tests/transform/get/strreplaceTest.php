<?php

namespace codename\core\io\tests\transform\get;

use codename\core\exception;
use codename\core\io\tests\transform\abstractTransformTest;
use ReflectionException;

class strreplaceTest extends abstractTransformTest
{
    /**
     * Testing transforms for Errors
     * @throws ReflectionException
     * @throws exception
     */
    public function testValueValidCase1(): void
    {
        $transform = $this->getTransform('get_strreplace', [
          'source' => 'source',
          'field' => 'example_source_field',
          'search' => [
            'source' => 'source',
            'field' => 'example_search_field',
          ],
          'replace' => [
            'source' => 'source',
            'field' => 'example_replace_field',
          ],
        ]);
        $result = $transform->transform([
          'example_source_field' => 'AbCdEfGhIjKlMnOpQrStUvWxYz',
          'example_search_field' => 'KlM',
          'example_replace_field' => 'klm',
        ]);

        // Make sure it stays an array
        static::assertEquals('AbCdEfGhIjklmnOpQrStUvWxYz', $result);
    }

    /**
     * Testing transforms for Errors
     * @throws ReflectionException
     * @throws exception
     */
    public function testValueValidCase2(): void
    {
        $transform = $this->getTransform('get_strreplace', [
          'source' => 'source',
          'field' => 'example_source_field',
          'search' => 'KlM',
          'replace' => 'klm',
          'case_insensitive' => true,
        ]);
        $result = $transform->transform([
          'example_source_field' => 'AbCdEfGhIjKlMnOpQrStUvWxYz',
        ]);

        // Make sure it stays an array
        static::assertEquals('AbCdEfGhIjklmnOpQrStUvWxYz', $result);
    }


    /**
     * Test Spec output (simple case)
     * @throws ReflectionException
     * @throws exception
     */
    public function testSpecification(): void
    {
        $transform = $this->getTransform('get_strreplace', [
          'source' => 'source',
          'field' => 'example_source_field',
          'mode' => 'lower',
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
