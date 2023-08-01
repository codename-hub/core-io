<?php

namespace codename\core\io\tests\target;

use codename\core\exception;
use codename\core\io\target\dummy;
use PHPUnit\Framework\TestCase;

/**
 * [dummyTest description]
 */
class dummyTest extends TestCase
{
    /**
     * [testDummyGeneral description]
     * @throws exception
     */
    public function testDummyGeneral(): void
    {
        $target = new dummy('general_example', []);

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
        } catch (\Exception) {
            static::fail();
        }

        // check set virtual store
        try {
            $target->setVirtualStoreEnabled(true);
        } catch (\Exception) {
            static::fail();
        }

        // check virtual store state
        static::assertTrue($target->getVirtualStoreEnabled());
    }

    /**
     * [testDummyGeneral description]
     */
    public function testDummyFinishedError(): void
    {
        $this->expectException(exception::class);
        $this->expectExceptionMessage('EXCEPTION_CORE_IO_TARGET_VIRTUAL_ALREADY_FINISHED');

        $target = new dummy('finished_error', []);

        // set finish
        try {
            $target->finish();
        } catch (\Exception) {
            static::fail();
        }

        // set data
        $target->store([
          'example' => 'data',
        ]);
    }
}
