<?php

namespace codename\core\io\tests\datasource;

use codename\core\exception;
use codename\core\io\datasource\multicsv;
use PHPUnit\Framework\TestCase;

class multicsvTest extends TestCase
{
    /**
     * tests general function of the multicsv datasource
     * @return void [type] [description]
     * @throws exception
     */
    public function testMulticsvGeneral(): void
    {
        $datasource = new multicsv(__DIR__ . "/" . 'testmulticsv1.csv');

        static::assertEquals('0', $datasource->currentProgressPosition());

        static::assertEquals('68', $datasource->currentProgressLimit());

        $datasource->setConfig([
          'delimiter' => ';',
          'headed' => true,
        ]);
    }

    /**
     * tests key stability of the multicsv datasource
     * @return void
     * @throws exception
     */
    public function testDatasourceMulticsvKeys(): void
    {
        $datasource = new multicsv([
          __DIR__ . "/" . 'testmulticsv1.csv',
          __DIR__ . "/" . 'testmulticsv2.csv',
          __DIR__ . "/" . 'testmulticsv3.csv',
        ], [
          'delimiter' => ',',
        ]);

        $datasource->rewind();

        // keep track of keys we've iterated over
        $keysIterated = [];
        $keyExpected = 0;

        $i = 0;
        foreach ($datasource as $key => $dataset) {
            static::assertFalse(in_array($key, $keysIterated), "Assert index/key '$key' hasn't been iterated over yet (keysIterated: " . implode(',', $keysIterated) . ").");
            static::assertEquals($keyExpected, $key, "Assert a stable and linear key progression from 0...n in a +1 manner.");
            $keyExpected++;
            $i++;
            $keysIterated[] = $key;
        }

        //
        // make sure we have iterated NINE times
        //
        static::assertEquals(9, $i, "Asset we've iterated over the complete datasource");
    }


    /**
     * "head1","head2"
     * "l1_d1","l1_d2"
     * "l2_d1","l2_d2"
     * "l3_d1","l3_d2"
     *
     * @return void testing the next function
     * @throws exception
     */
    public function testMultiDataSourceNext(): void
    {
        $datasource = new multicsv([
          __DIR__ . "/" . 'testmulticsv1.csv',
          __DIR__ . "/" . 'testmulticsv2.csv',
          __DIR__ . "/" . 'testmulticsv3.csv',
        ], [
          'delimiter' => ',',
        ]);

        $datasource->rewind();

        $i = 0;
        foreach ($datasource as $dataset) {
            $i++;

            static::assertEquals("l{$i}_d1", $dataset['head1']);
            static::assertEquals("l{$i}_d2", $dataset['head2']);
        }

        //
        // make sure we have iterated NINE times
        //
        static::assertEquals(9, $i);
    }
}
