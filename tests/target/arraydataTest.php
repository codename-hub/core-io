<?php

namespace codename\core\io\tests\target;

use codename\core\io\target\arraydata;
use Exception;
use PHPUnit\Framework\TestCase;

/**
 * [arraydataTest description]
 */
class arraydataTest extends TestCase
{
    /**
     * [testArraydataGeneral description]
     */
    public function testArraydataGeneral(): void
    {
        $target = new arraydata('general_example', []);

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

        static::assertTrue(true);
    }
}
