<?php

namespace codename\core\io\tests\value\text\fileabsolute;

use codename\core\exception;
use codename\core\io\value\text\fileabsolute\tagged;
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
        $tagged = new tagged('example', [
          'example' => true,
        ]);

        static::assertEquals([
          'example' => true,
        ], $tagged->getTags());
    }
}
