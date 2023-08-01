<?php

namespace codename\core\io\tests\value\text;

use codename\core\exception;
use codename\core\io\value\text\tagged;
use PHPUnit\Framework\TestCase;
use ReflectionException;

class taggedTest extends TestCase
{
    /**
     * [testTagsIsValid description]
     * @return void [type] [description]
     * @throws ReflectionException
     * @throws exception
     */
    public function testTagsIsValid(): void
    {
        $tagged = new tagged('', [
          'example' => true,
        ]);

        static::assertEquals([
          'example' => true,
        ], $tagged->getTags());
    }
}
