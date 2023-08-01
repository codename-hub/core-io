<?php

namespace codename\core\io\tests\datasource;

use codename\core\io\datasource\arraydata;
use Exception;
use PHPUnit\Framework\TestCase;

/**
 * [testArraydata description]
 */
class arraydataTest extends TestCase
{
    /**
     * tests general function of the arraydata datasource
     * @return void [type] [description]
     */
    public function testArraydata(): void
    {
        $datasource = new arraydata();
        $datasource->setData([
          [
            'oldkey1' => 'abc',
            'oldkey2' => 'def',
            'oldkey3' => 'ghi',
          ],
          [
            'oldkey1' => 'jkl',
            'oldkey2' => 'mno',
            'oldkey3' => 'pqr',
          ],
          [
            'oldkey1' => 'stu',
            'oldkey2' => 'vwx',
            'oldkey3' => 'yz',
          ],
        ]);

        try {
            $datasource->setConfig([]);
        } catch (Exception) {
            static::fail();
        }

        static::assertEquals('0', $datasource->currentProgressPosition());

        static::assertEquals('3', $datasource->currentProgressLimit());

        // rewind the datasources
        $datasource->rewind();

        static::assertEquals('0', $datasource->key());

        static::assertTrue($datasource->valid());

        // get current data
        static::assertEquals([
          'oldkey1' => 'abc',
          'oldkey2' => 'def',
          'oldkey3' => 'ghi',
        ], $datasource->current());

        $datasource->next();

        static::assertEquals([
          'oldkey1' => 'jkl',
          'oldkey2' => 'mno',
          'oldkey3' => 'pqr',
        ], $datasource->current());
    }
}
