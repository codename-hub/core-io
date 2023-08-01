<?php

namespace codename\core\io\tests\validator\structure\config\import;

use codename\core\io\validator\structure\config\import\target;
use codename\core\test\base;

/**
 * I will test the target validator
 * @package codename\core
 * @since 2016-11-02
 */
class targetTest extends base
{
    /**
     * simple non-text value test
     * @return void
     */
    public function testValueNotAArray(): void
    {
        $validator = new target();
        $errors = $validator->validate('');

        static::assertEquals('VALIDATION.VALUE_NOT_A_ARRAY', $errors[0]['__CODE'] ?? '');
    }

    /**
     * Testing validators for Errors
     * @return void
     */
    public function testValueMissingArrKeys(): void
    {
        $validator = new target();
        $errors = $validator->validate([]);

        static::assertEmpty($errors);
    }
}
