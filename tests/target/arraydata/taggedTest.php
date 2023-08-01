<?php

namespace codename\core\io\tests\target\arraydata;

use codename\core\io\target\arraydata\tagged;
use codename\core\value\structure;
use Exception;
use PHPUnit\Framework\TestCase;
use ReflectionException;

class taggedTest extends TestCase
{
    /**
     * [testWithTags description]
     * @throws ReflectionException
     * @throws \codename\core\exception
     */
    public function testWithTags(): void
    {
        $target = new tagged('general_example', []);

        // set data
        $result = $target->store([
          'example' => 'data',
        ], [
          'example' => true,
        ]);
        static::assertTrue($result);

        // get data
        $result = $target->getVirtualStoreData();
        static::assertEquals([
          ['example' => 'data'],
        ], $result);

        // check finish
        try {
            $target->finish();
        } catch (Exception) {
            static::fail();
        }

        // getStructureResultArray
        $result = $target->getStructureResultArray();

        static::assertCount(1, $result);
        static::assertInstanceOf(\codename\core\io\value\structure\tagged::class, $result[0]);
        static::assertEquals([
          'example' => 'data',
        ], $result[0]->get() ?? []);

        if (!($result[0] instanceof \codename\core\io\value\structure\tagged)) {
            static::fail('setup fail');
        }

        static::assertEquals([
          [
            'example' => true,
          ],
        ], $result[0]->getTags());
    }

    /**
     * [testWithoutTags description]
     * @throws ReflectionException
     * @throws \codename\core\exception
     */
    public function testWithoutTags(): void
    {
        $target = new tagged('general_example', []);

        // set data
        $result = $target->store([
          'example' => 'data',
        ]);
        static::assertTrue($result);

        // get data
        $result = $target->getVirtualStoreData();
        static::assertEquals([
          ['example' => 'data'],
        ], $result);

        // check finish
        try {
            $target->finish();
        } catch (Exception) {
            static::fail();
        }

        // getStructureResultArray
        $result = $target->getStructureResultArray();

        static::assertCount(1, $result);
        static::assertInstanceOf(structure::class, $result[0]);
        static::assertEquals([
          'example' => 'data',
        ], $result[0]->get() ?? []);
    }
}
