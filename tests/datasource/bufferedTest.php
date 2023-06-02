<?php

namespace codename\core\io\tests\datasource;

use codename\core\exception;
use codename\core\io\datasource\arraydata;
use codename\core\io\datasource\buffered;
use codename\core\io\datasource\csv;
use PHPUnit\Framework\TestCase;

/**
 * [testBuffered description]
 */
class bufferedTest extends TestCase
{
    /**
     * [testBufferSizeTooLowException description]
     * @return void [type] [description]
     * @throws exception
     */
    public function testBufferSizeTooLowException(): void
    {
        $this->expectException(exception::class);
        $this->expectExceptionMessage('EXCEPTION_DATASOURCE_BUFFERED_BUFFERSIZE_TOO_LOW');
        $source = new arraydata();
        new buffered($source, 0);
    }

    /**
     * [testSmallBufferOnEmptySource description]
     * @return void [type] [description]
     * @throws exception
     */
    public function testSmallBufferOnEmptySource(): void
    {
        $source = new arraydata();
        $source->setData([]);
        $buffered = new buffered($source, 1);

        $r = [];
        foreach ($buffered as $b) {
            $r[] = $b;
        }

        $buffered->rewind();

        static::assertEquals(0, $source->currentProgressLimit());
        static::assertEquals(0, $buffered->currentProgressLimit());
        static::assertEmpty($r);
    }

    /**
     * [testBufferReadingWithMultipleBufferSizes description]
     * @throws exception
     */
    public function testBufferReadingWithMultipleBufferSizes(): void
    {
        $source = new arraydata();
        $source->setData([
          1,
          2,
          3,
          4,
          5,
          6,
          7,
          8,
        ]);

        for ($bufferSize = 1; $bufferSize <= 16; $bufferSize++) {
            $buffered = new buffered($source, $bufferSize);
            $r = [];
            foreach ($buffered as $b) {
                $r[] = $b;
            }
            static::assertEquals([1, 2, 3, 4, 5, 6, 7, 8], $r);
        }
    }

    /**
     * [testBufferReadingWithMultipleBufferSizesDynamic description]
     * @throws exception
     */
    public function testBufferReadingWithMultipleBufferSizesDynamic(): void
    {
        $source = new arraydata();
        $source->setData([
          1,
          2,
          3,
          4,
          5,
          6,
          7,
          8,
        ]);

        static::assertEquals(8, $source->currentProgressLimit());

        $buffered = new buffered($source, 999);

        for ($bufferSize = 1; $bufferSize <= 16; $bufferSize++) {
            // Modify buffer size of the only instance
            $buffered->setBufferSize($bufferSize);
            $buffered->rewind();

            static::assertLessThanOrEqual($bufferSize, $buffered->getBuffer()->count());

            $r = [];
            $cnt = 0;
            foreach ($buffered as $b) {
                $r[] = $b;
                static::assertEquals($cnt, $buffered->currentProgressPosition());
                static::assertEquals($cnt, $buffered->key());
                $cnt++;
            }

            static::assertEquals([1, 2, 3, 4, 5, 6, 7, 8], $r);
        }
    }

    /**
     * [testSetConfigPassthrough description]
     * @throws exception
     */
    public function testSetConfigPassthrough(): void
    {
        $source = new csv(
            __DIR__ . "/" . 'testcsv2.csv',
            [
              'autodetect_utf8_bom' => true,
              'skip_empty_rows' => true,
              'skip_rows' => 11,      // wrong setting
              'encoding' => ['from' => 'UTF-8', 'to' => 'UTF-8'],
            ]
        );

        $buffered = new buffered($source, 999);

        $buffered->setConfig([
          'autodetect_utf8_bom' => true,
          'skip_empty_rows' => true,
          'skip_rows' => 1,       // right setting
          'encoding' => ['from' => 'UTF-8', 'to' => 'UTF-8'],
        ]);

        $i = 0;
        foreach ($buffered as $dataset) {
            $i++;
            static::assertEquals("l{$i}_d1", $dataset['head0']);
            static::assertEquals("l{$i}_d2", $dataset['head1']);
        }

        static::assertEquals(3, $i);
    }
}
