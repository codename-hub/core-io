<?php

namespace codename\core\io\tests\validator;

use codename\core\test\base;
use codename\core\validator\structure;

/**
 * base class for structure validators
 */
class structureTest extends base
{
    /**
     * simple non-text value test
     * @return void
     */
    public function testValueNotAArray(): void
    {
        $validator = new structure();
        $errors = $validator->validate('');

        static::assertEquals('VALIDATION.VALUE_NOT_A_ARRAY', $errors[0]['__CODE'] ?? '');
    }

    /**
     * simple non-text value test
     * @return void
     */
    public function testValueIsNull(): void
    {
        $validator = new structure();
        $errors = $validator->validate(null);

        static::assertEmpty($errors);
    }

    /**
     * simple non-text value test
     * @return void
     */
    public function testValueIsNullNotAllowed(): void
    {
        $validator = new structure(false);
        $errors = $validator->validate(null);

        static::assertNotEmpty($errors);
        static::assertCount(1, $errors);
        static::assertEquals('VALIDATION.VALUE_IS_NULL', $errors[0]['__CODE']);
    }

    /**
     * simple non-text value test
     * @return void
     */
    public function testValueIsValid(): void
    {
        $validator = new structure();
        static::assertTrue($validator->isValid(null));
    }
}
