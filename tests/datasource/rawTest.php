<?php

namespace codename\core\io\tests\datasource;

use codename\core\exception;
use codename\core\io\datasource\raw;
use PHPUnit\Framework\TestCase;

class rawTest extends TestCase
{
    /**
     * tests general function of the raw datasource
     * @return void [type] [description]
     * @throws exception
     */
    public function testRawGeneral(): void
    {
        $datasource = new raw(__DIR__ . "/" . 'testRaw1.txt');

        static::assertEquals('0', $datasource->key());

        static::assertEquals('0', $datasource->currentProgressPosition());

        static::assertEquals('52', $datasource->currentProgressLimit());

        // rewind the datasources
        $datasource->rewind();
    }

    /**
     * test a simple raw file
     * @return void [type] [description]
     * @throws exception
     */
    public function testDataSourceFileNotExists(): void
    {
        $this->expectException(exception::class);
        $this->expectExceptionMessage('FILE_COULD_NOT_BE_OPEN');
        new raw(__DIR__ . "/" . 'testRaw1_filenotexists.txt');
    }

    /**
     * test a simple raw file
     * @return void [type] [description]
     * @throws exception
     */
    public function testDataSourceInvalidConfig(): void
    {
        $this->expectException(exception::class);
        $this->expectExceptionMessage('CORE_IO_DATASOURCE_INVALID_MAP_CONFIG');

        new raw(
            __DIR__ . "/" . 'testRaw1.txt',
            [
              'format' => [
                'trim' => true,
                'convert' => [
                  'from' => 'ASCII',
                  'to' => 'UTF-8',
                ],
                'map' => [
                  'field1' => [
                    'type' => 'wrong',
                    'length' => 10,
                  ],
                ],
              ],
            ]
        );
    }

    /**
     * test a simple raw file
     * @return void [type] [description]
     * @throws exception
     */
    public function testDataSourceIsValid(): void
    {
        $datasource = new raw(
            __DIR__ . "/" . 'testRaw1.txt',
            [
              'format' => [
                'trim' => true,
                'convert' => [
                  'from' => 'ASCII',
                  'to' => 'UTF-8',
                ],
                'map' => [
                  'field1' => [
                    'type' => 'fixed',
                    'length' => 10,
                  ],
                  'field2' => [
                    'type' => 'fixed',
                    'length' => 10,
                  ],
                  'field3' => [
                    'type' => 'fixed',
                    'length' => 10,
                  ],
                ],
              ],
            ]
        );
        $datasource->next();

        static::assertNotEmpty($datasource->getRawCurrent());

        $data = $datasource->current();
        static::assertEquals('col11', $data['field1']);
        static::assertEquals('col21', $data['field2']);
        static::assertEquals('col31', $data['field3']);
    }

    /**
     * test a simple raw file
     * @return void [type] [description]
     * @throws exception
     */
    public function testDataSourceIsValidWithMappings(): void
    {
        $datasource = new raw(
            __DIR__ . "/" . 'testRaw1.txt',
            [
              'format' => [
                'trim' => true,
                'map' => [
                  'key' => [
                    'type' => 'fixed',
                    'length' => 5,
                  ],
                ],
              ],
              'mappings' => [
                'key' => [
                  'col11' => [
                    'trim' => true,
                    'map' => [
                      'field1' => [
                        'type' => 'fixed',
                        'length' => 10,
                      ],
                      'field2' => [
                        'type' => 'fixed',
                        'length' => 10,
                      ],
                      'field3' => [
                        'type' => 'fixed',
                        'length' => 10,
                      ],
                    ],
                  ],
                  'col12' => [
                    'trim' => true,
                    'convert' => [
                      'from' => 'ASCII',
                      'to' => 'UTF-8',
                    ],
                    'map' => [
                      'field1' => [
                        'type' => 'fixed',
                        'length' => 10,
                      ],
                      'field2' => [
                        'type' => 'fixed',
                        'length' => 10,
                      ],
                      'field3' => [
                        'type' => 'fixed',
                        'length' => 10,
                      ],
                    ],
                  ],
                ],
              ],
            ]
        );
        $datasource->next();

        static::assertNotEmpty($datasource->getRawCurrent());

        $data = $datasource->current();
        static::assertEquals('col11', $data['field1']);
        static::assertEquals('col21', $data['field2']);
        static::assertEquals('col31', $data['field3']);

        $datasource->next();

        $data = $datasource->current();
        static::assertEquals('col12', $data['field1']);
        static::assertEquals('col22', $data['field2']);
        static::assertEquals('col32', $data['field3']);
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
    public function testDataSourceNext(): void
    {
        $datasource = new raw(
            __DIR__ . "/" . 'testRaw1.txt',
            [
              'format' => [
                'trim' => true,
                'map' => [
                  'field1' => [
                    'type' => 'fixed',
                    'length' => 10,
                  ],
                  'field2' => [
                    'type' => 'fixed',
                    'length' => 10,
                  ],
                  'field3' => [
                    'type' => 'fixed',
                    'length' => 10,
                  ],
                ],
              ],
            ]
        );

        $i = 0;
        foreach ($datasource as $dataset) {
            $i++;
            static::assertEquals("col1$i", $dataset['field1']);
            static::assertEquals("col2$i", $dataset['field2']);
            static::assertEquals("col3$i", $dataset['field3']);
        }

        //
        // make sure we have iterated two times
        //
        static::assertEquals(2, $i);
    }
}
