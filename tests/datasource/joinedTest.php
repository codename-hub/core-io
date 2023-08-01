<?php

namespace codename\core\io\tests\datasource;

use codename\core\exception;
use codename\core\io\datasource\csv;
use codename\core\io\datasource\joined;
use PHPUnit\Framework\TestCase;

class joinedTest extends TestCase
{
    /**
     * Tests joining two CSVs based on a simple key
     * @testWith  [ false ]
     *            [ true ]
     * @param bool $indexesEnabled
     * @throws exception
     */
    public function testSimpleJoinedCsv(bool $indexesEnabled): void
    {
        $joinedDatasource = new joined(
            [
              new csv(
                  __DIR__ . '/joined1_base1.csv',
                  [
                    'delimiter' => ';',
                  ]
              ),
              new csv(
                  __DIR__ . '/joined1_join1.csv',
                  [
                    'delimiter' => ';',
                  ]
              ),
            ],
            [
                // CONFIG!
              'join' => [
                [
                  'index' => $indexesEnabled,
                  'base_datasource' => 0, // array index-based
                  'join_datasource' => 1, // array index-based
                  'base_field' => 'col2',
                  'join_field' => 'id2',
                ],
              ],
            ]
        );

        $res = [];
        foreach ($joinedDatasource as $d) {
            $res[] = $d;
        }

        static::assertCount(6, $res);

        // At the time of writing, missing values are not applied to join result datasets
        // therefore, array_column will return fewer values in those cases (stipping out unset values)
        static::assertEquals(['ABC', 'DEF', 'GHI', 'ABC', /*null,*/ 'GHI'], array_column($res, 'name'));
    }

    /**
     * Test the same with explicit names for the datasources
     * @testWith  [ false ]
     *            [ true ]
     * @param bool $indexesEnabled
     * @throws exception
     */
    public function testSimpleJoinedCsvWithNamedDatasources(bool $indexesEnabled): void
    {
        $joinedDatasource = new joined(
            [
              'foo' => new csv(
                  __DIR__ . '/joined1_base1.csv',
                  [
                    'delimiter' => ';',
                  ]
              ),
              'bar' => new csv(
                  __DIR__ . '/joined1_join1.csv',
                  [
                    'delimiter' => ';',
                  ]
              ),
            ],
            [
                // CONFIG!
              'join' => [
                [
                  'index' => $indexesEnabled,
                  'base_datasource' => 'foo', // key-based
                  'join_datasource' => 'bar', // key-based
                  'base_field' => 'col2',
                  'join_field' => 'id2',
                ],
              ],
            ]
        );

        $res = [];
        foreach ($joinedDatasource as $d) {
            $res[] = $d;
        }

        static::assertCount(6, $res);

        // At the time of writing, missing values are not applied to join result datasets
        // therefore, array_column will return fewer values in those cases (stipping out unset values)
        static::assertEquals(['ABC', 'DEF', 'GHI', 'ABC', /*null,*/ 'GHI'], array_column($res, 'name'));
    }

    /**
     * [testMultipleJoinedCsvWithNamedDatasources description]
     * @testWith  [ false ]
     *            [ true ]
     * @param bool $indexesEnabled
     * @throws exception
     */
    public function testMultipleJoinedCsvWithNamedDatasources(bool $indexesEnabled): void
    {
        $joinedDatasource = new joined(
            [
              'foo' => new csv(
                  __DIR__ . '/joined1_base1.csv',
                  [
                    'delimiter' => ';',
                  ]
              ),
              'bar' => new csv(
                  __DIR__ . '/joined1_join1.csv',
                  [
                    'delimiter' => ';',
                  ]
              ),
              'baz' => new csv(
                  __DIR__ . '/joined1_join2.csv',
                  [
                    'delimiter' => ';',
                  ]
              ),
            ],
            [
                // CONFIG!
              'join' => [
                [
                  'index' => $indexesEnabled,
                  'base_datasource' => 'foo', // key-based
                  'join_datasource' => 'bar', // key-based
                  'base_field' => 'col2',
                  'join_field' => 'id2',
                ],
                [
                  'index' => $indexesEnabled,
                  'base_datasource' => 'bar', // key-based
                  'join_datasource' => 'baz', // key-based
                  'base_field' => 'other_id',
                  'join_field' => 'join2_id',
                ],
              ],
            ]
        );

        $res = [];
        foreach ($joinedDatasource as $d) {
            $res[] = $d;
        }

        static::assertCount(6, $res);

        // At the time of writing, missing values are not applied to join result datasets
        // therefore, array_column will return fewer values in those cases (stipping out unset values)
        static::assertEquals(['ABC', 'DEF', 'GHI', 'ABC', /*null,*/ 'GHI'], array_column($res, 'name'));
        static::assertEquals(['BBB', 'BBB', 'CCC', 'BBB', /*null,*/ 'CCC'], array_column($res, 'value'));
    }

    /**
     * @testWith  [ false ]
     *            [ true ]
     * @param bool $indexesEnabled [description]
     * @throws exception
     */
    public function testMultipleJoinedCsvWithNamedDatasourcesAndAmbiguities(bool $indexesEnabled): void
    {
        $joinedDatasource = new joined(
            [
              'foo' => new csv(
                  __DIR__ . '/joined1_base1.csv',
                  [
                    'delimiter' => ';',
                  ]
              ),
              'bar' => new csv(
                  __DIR__ . '/joined1_join1.csv',
                  [
                    'delimiter' => ';',
                  ]
              ),
              'baz' => new csv(
                  __DIR__ . '/joined1_join2.csv',
                  [
                    'delimiter' => ';',
                  ]
              ),
              'qux' => new csv(
                  __DIR__ . '/joined1_join3.csv',
                  [
                    'delimiter' => ';',
                  ]
              ),
            ],
            [
                // CONFIG!
              'join' => [
                [
                  'index' => $indexesEnabled,
                  'base_datasource' => 'foo', // key-based
                  'join_datasource' => 'bar', // key-based
                  'base_field' => 'col2',
                  'join_field' => 'id2',
                ],
                [
                  'index' => $indexesEnabled,
                  'base_datasource' => 'bar', // key-based
                  'join_datasource' => 'baz', // key-based
                  'base_field' => 'other_id',
                  'join_field' => 'join2_id',
                ],
                [
                  'index' => $indexesEnabled,
                  'base_datasource' => 'foo', // key-based
                  'join_datasource' => 'qux', // key-based
                  'base_field' => 'col1',
                  'join_field' => 'join3_col1',
                ],
              ],
            ]
        );

        $res = [];
        foreach ($joinedDatasource as $d) {
            $res[] = $d;
        }

        static::assertCount(8, $res);

        static::assertEquals(['A', 'A', 'B', 'C', 'D', 'D', 'E', 'F'], array_column($res, 'col1'));
        static::assertEquals(['A1', 'A2', 'B1', 'C1', 'D1', 'D2', 'F1'], array_column($res, 'join3_value'));
    }
}
