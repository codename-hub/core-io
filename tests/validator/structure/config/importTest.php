<?php

namespace codename\core\io\tests\validator\structure\config;

use codename\core\io\validator\structure\config\import;
use codename\core\test\base;

/**
 * I will test the import validator
 * @package codename\core
 * @since 2016-11-02
 */
class importTest extends base
{
    /**
     * Testing validators for Errors
     * @return void
     */
    public function testValueMissingArrKeys(): void
    {
        $validator = new import();
        $errors = $validator->validate([]);

        static::assertNotEmpty($errors);
        static::assertCount(2, $errors);
        static::assertEquals('VALIDATION.ARRAY_MISSING_KEY', $errors[0]['__CODE']);
        static::assertEquals('VALIDATION.ARRAY_MISSING_KEY', $errors[1]['__CODE']);
    }

    /**
     * Testing validators for Errors
     * @return void
     */
    public function testValueInvalidKeySource(): void
    {
        $validator = new import();
        $errors = $validator->validate([
          'source' => '',
          'target' => [],
        ]);

        static::assertNotEmpty($errors);
    }

    /**
     * Testing validators for Errors
     * @return void
     */
    public function testValueInvalidKeyTarget(): void
    {
        $validator = new import();
        $errors = $validator->validate([
          'source' => [],
          'target' => '',
        ]);

        static::assertNotEmpty($errors);
    }

    /**
     * Testing validators for Errors
     * @return void
     */
    public function testValueInvalidKeyTransform(): void
    {
        $validator = new import();
        $errors = $validator->validate([
          'source' => [],
          'target' => [],
          'transform' => 'example',
        ]);

        static::assertNotEmpty($errors);
    }

    /**
     * Testing validators for Errors
     * @return void
     */
    public function testValueValid(): void
    {
        $validator = new import();
        $errors = $validator->validate([
          'source' => [],
          'target' => [],
        ]);

        static::assertEmpty($errors);
    }
}
