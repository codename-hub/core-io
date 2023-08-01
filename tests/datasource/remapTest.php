<?php

namespace codename\core\io\tests\datasource;

use codename\core\io\datasource\arraydata;
use codename\core\io\datasource\remap;
use PHPUnit\Framework\TestCase;

/**
 * [testRemap description]
 */
class remapTest extends TestCase
{
    /**
     * tests general function of the remap datasource
     * @return void [type] [description]
     */
    public function testRemapGeneral(): void
    {
        $datasource = new arraydata();
        $datasource->setData($this->getTestData());
        $remappedDatasource = new remap(
            $datasource,
            [
              'remap' => [
                'oldkey1' => 'newkey1',
                'oldkey2' => 'newkey2',
                'oldkey3' => 'newkey3',
              ],
            ]
        );

        // rewind the datasources
        $remappedDatasource->rewind();

        $result = $remappedDatasource->current();
        static::assertEquals([
          'newkey1' => 'abc',
          'newkey2' => 'def',
          'newkey3' => 'ghi',
        ], $result);

        static::assertEquals('0', $remappedDatasource->currentProgressPosition());

        static::assertEquals('3', $remappedDatasource->currentProgressLimit());

        static::assertEquals('0', $remappedDatasource->key());

        static::assertTrue($remappedDatasource->valid());
    }

    /**
     * returns an array of basic test data
     * @return array
     */
    protected function getTestData(): array
    {
        return [
          [
            'oldkey1' => 'abc',
            'oldkey2' => 'def',
            'oldkey3' => 'ghi',
            'oldkey4' => ['jkl', 'mno'],
          ],
          [
            'oldkey1' => 'jkl',
            'oldkey2' => 'mno',
            'oldkey3' => 'pqr',
            'oldkey4' => ['stu', 'vwx'],
          ],
          [
            'oldkey1' => 'stu',
            'oldkey2' => 'vwx',
            'oldkey3' => 'yz',
            'oldkey4' => ['abc', 'def'],
          ],
        ];
    }

    /**
     * tests general function of the remap datasource
     * @return void [type] [description]
     */
    public function testRemapDatasource(): void
    {
        $datasource = new arraydata();
        $datasource->setData($this->getTestData());
        $remappedDatasource = new remap(
            $datasource,
            [
              'remap' => [
                'oldkey1' => 'newkey1',
                'oldkey2' => 'newkey2',
                'oldkey3' => 'newkey3',
                'oldkey4' => ['newarraykey1', 'newarraykey2'],
              ],
            ]
        );

        $comparisonDatasource = new arraydata();
        $comparisonDatasource->setData($this->getTestData());

        // rewind the datasources
        $remappedDatasource->rewind();
        $comparisonDatasource->rewind();

        // max sure we only iterate over the count of the testdata array
        $iterations = count($this->getTestData());
        $i = 0;

        while ($i < $iterations) {
            $remappedDataset = $remappedDatasource->current();
            $originalDataset = $comparisonDatasource->current();

            static::assertEquals($remappedDataset['newkey1'], $originalDataset['oldkey1']);
            static::assertEquals($remappedDataset['newkey2'], $originalDataset['oldkey2']);
            static::assertEquals($remappedDataset['newkey3'], $originalDataset['oldkey3']);

            $i++;
        }
    }

    /**
     * tests outputting a dataset based on the input
     * e.g. the remapped values are put ON TOP of the input (or replaced)
     * @return void
     */
    public function testRemapReplaceSourceInclusion(): void
    {
        $datasource = new arraydata();
        $datasource->setData($this->getTestData());

        $remappedDatasource = new remap(
            $datasource,
            [
              'remap' => [
                'oldkey1' => 'newkey1',
                'oldkey2' => 'newkey2',
                'oldkey3' => 'newkey3',
              ],
                // the relevant config key
              'replace' => true,
            ]
        );

        $originalDatasource = new arraydata();
        $originalDatasource->setData($this->getTestData());

        $tempDatasource = new arraydata();
        $tempDatasource->setData($this->getTestData());
        $normalRemappedDatasource = new remap(
            $tempDatasource,
            [
              'remap' => [
                'oldkey1' => 'newkey1',
                'oldkey2' => 'newkey2',
                'oldkey3' => 'newkey3',
              ],
            ]
        );

        // rewind the datasources
        $remappedDatasource->rewind();
        $originalDatasource->rewind();
        $normalRemappedDatasource->rewind();

        // max sure we only iterate over the count of the testdata array
        $iterations = count($this->getTestData());
        $i = 0;

        while ($i < $iterations) {
            $remappedDataset = $remappedDatasource->current();

            // original
            $originalDataset = $originalDatasource->current();

            // remapped without including the input data
            $normallyRemappedDataset = $normalRemappedDatasource->current();

            foreach ($originalDataset as $key => $value) {
                static::assertEquals($value, $remappedDataset[$key]);
            }
            foreach ($normallyRemappedDataset as $key => $value) {
                static::assertEquals($value, $remappedDataset[$key]);
            }

            // Don't forget to move the iterator
            $remappedDatasource->next();
            $originalDatasource->next();
            $normalRemappedDatasource->next();

            $i++;
        }
    }

    /**
     * tests the inclusion of original data in a subkey
     * @return void [type] [description]
     */
    public function testRemapSourceDataKey(): void
    {
        $datasource = new arraydata();
        $datasource->setData($this->getTestData());

        $sourceDataKey = 'original';

        $remappedDatasource = new remap(
            $datasource,
            [
              'remap' => [
                'oldkey1' => 'newkey1',
                'oldkey2' => 'newkey2',
                'oldkey3' => 'newkey3',
              ],
              'source_data_key' => $sourceDataKey,
            ]
        );

        $comparisonDatasource = new arraydata();
        $comparisonDatasource->setData($this->getTestData());

        // rewind the datasources
        $remappedDatasource->rewind();
        $comparisonDatasource->rewind();

        // max sure we only iterate over the count of the testdata array
        $iterations = count($this->getTestData());
        $i = 0;

        while ($i < $iterations) {
            $remappedDataset = $remappedDatasource->current();
            $originalDataset = $comparisonDatasource->current();
            static::assertEquals($remappedDataset[$sourceDataKey], $originalDataset);

            // Don't forget to move the iterator
            $remappedDatasource->next();
            $comparisonDatasource->next();

            $i++;
        }
    }
}
