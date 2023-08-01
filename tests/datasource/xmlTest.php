<?php

namespace codename\core\io\tests\datasource;

use codename\core\io\datasource\xml;
use Exception;
use PHPUnit\Framework\TestCase;

class xmlTest extends TestCase
{
    /**
     * tests general function of the xml datasource
     * @return void [type] [description]
     * @throws Exception
     */
    public function testXmlGeneral(): void
    {
        $datasource = new xml(
            __DIR__ . "/" . 'testXml1.xml',
            [
              'xpath_query' => '/example/data',
              'xpath_mapping' => [
                'field1' => 'field1',
                'field2' => 'field2',
                'field3' => 'field3',
              ],
            ]
        );

        static::assertEquals('0', $datasource->currentProgressPosition());

        static::assertEquals('0', $datasource->currentProgressLimit());

        // rewind the datasources
        $datasource->rewind();

        static::assertEquals('0', $datasource->key());
    }

    /**
     * test a simple xml file
     * @return void [type] [description]
     * @throws Exception
     */
    public function testDataSourceIsValid(): void
    {
        $datasource = new xml(
            __DIR__ . "/" . 'testXml1.xml',
            [
              'xpath_query' => '/example/data',
              'xpath_mapping' => [
                'field1' => 'field1',
                'field2' => 'field2',
                'field3' => 'field3',
              ],
            ]
        );
        $datasource->next();

        $data = $datasource->current();
        static::assertEquals('example11', $data['field1']);
        static::assertEquals('example12', $data['field2']);
        static::assertEquals('example13', $data['field3']);
    }

    /**
     * "head1","head2"
     * "l1_d1","l1_d2"
     * "l2_d1","l2_d2"
     * "l3_d1","l3_d2"
     *
     * @return void testing the next function
     * @throws Exception
     */
    public function testDataSourceNext(): void
    {
        $datasource = new xml(
            __DIR__ . "/" . 'testXml1.xml',
            [
              'xpath_query' => '/example/data',
              'xpath_mapping' => [
                'field1' => 'field1',
                'field2' => 'field2',
                'field3' => 'field3',
              ],
            ]
        );

        $i = 0;
        foreach ($datasource as $dataset) {
            $i++;
            static::assertEquals("example{$i}1", $dataset['field1']);
            static::assertEquals("example{$i}2", $dataset['field2']);
            static::assertEquals("example{$i}3", $dataset['field3']);
        }

        //
        // make sure we have iterated three times
        //
        static::assertEquals(3, $i);
    }
}
