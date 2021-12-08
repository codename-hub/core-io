<?php
namespace codename\core\io\tests\datasource;

class testJoined extends \PHPUnit\Framework\TestCase
{
  /**
   * Tests joining two CSVs based on a simple key
   * @testWith  [ false ]
   *            [ true ]
   */
  public function testSimpleJoinedCsv(bool $indexesEnabled): void {
    $joinedDatasource = new \codename\core\io\datasource\joined(
      [
        new \codename\core\io\datasource\csv(
          __DIR__.'/joined1_base1.csv',
          [
            'delimiter' => ';',
          ]
        ),
        new \codename\core\io\datasource\csv(
          __DIR__.'/joined1_join1.csv',
          [
            'delimiter' => ';',
          ]
        )
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
          ]
        ]
      ]
    );

    $res = [];
    foreach($joinedDatasource as $d) {
      $res[] = $d;
    }

    $this->assertCount(6, $res);

    // At the time of writing, missing values are not applied to join result datasets
    // therefore, array_column will return less values in those cases (stipping out unset values)
    $this->assertEquals(['ABC','DEF','GHI','ABC', /*null,*/ 'GHI'], array_column($res, 'name'));
  }

  /**
   * Test the same with explicit names for the datasources
   * @testWith  [ false ]
   *            [ true ]
   */
  public function testSimpleJoinedCsvWithNamedDatasources(bool $indexesEnabled): void {
    $joinedDatasource = new \codename\core\io\datasource\joined(
      [
        'foo' => new \codename\core\io\datasource\csv(
          __DIR__.'/joined1_base1.csv',
          [
            'delimiter' => ';',
          ]
        ),
        'bar' => new \codename\core\io\datasource\csv(
          __DIR__.'/joined1_join1.csv',
          [
            'delimiter' => ';',
          ]
        )
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
          ]
        ]
      ]
    );

    $res = [];
    foreach($joinedDatasource as $d) {
      $res[] = $d;
    }

    $this->assertCount(6, $res);

    // At the time of writing, missing values are not applied to join result datasets
    // therefore, array_column will return less values in those cases (stipping out unset values)
    $this->assertEquals(['ABC','DEF','GHI','ABC', /*null,*/ 'GHI'], array_column($res, 'name'));
  }

  /**
   * [testMultipleJoinedCsvWithNamedDatasources description]
   * @testWith  [ false ]
   *            [ true ]
   */
  public function testMultipleJoinedCsvWithNamedDatasources(bool $indexesEnabled): void {
    $joinedDatasource = new \codename\core\io\datasource\joined(
      [
        'foo' => new \codename\core\io\datasource\csv(
          __DIR__.'/joined1_base1.csv',
          [
            'delimiter' => ';',
          ]
        ),
        'bar' => new \codename\core\io\datasource\csv(
          __DIR__.'/joined1_join1.csv',
          [
            'delimiter' => ';',
          ]
        ),
        'baz' => new \codename\core\io\datasource\csv(
          __DIR__.'/joined1_join2.csv',
          [
            'delimiter' => ';',
          ]
        )
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
        ]
      ]
    );

    $res = [];
    foreach($joinedDatasource as $d) {
      $res[] = $d;
    }

    $this->assertCount(6, $res);

    // At the time of writing, missing values are not applied to join result datasets
    // therefore, array_column will return less values in those cases (stipping out unset values)
    $this->assertEquals(['ABC','DEF','GHI','ABC', /*null,*/ 'GHI'], array_column($res, 'name'));
    $this->assertEquals(['BBB','BBB','CCC','BBB', /*null,*/ 'CCC' ], array_column($res, 'value'));
  }

  /**
   * @testWith  [ false ]
   *            [ true ]
   * @param bool $indexesEnabled  [description]
   */
  public function testMultipleJoinedCsvWithNamedDatasourcesAndAmbiguities(bool $indexesEnabled): void {
    $joinedDatasource = new \codename\core\io\datasource\joined(
      [
        'foo' => new \codename\core\io\datasource\csv(
          __DIR__.'/joined1_base1.csv',
          [
            'delimiter' => ';',
          ]
        ),
        'bar' => new \codename\core\io\datasource\csv(
          __DIR__.'/joined1_join1.csv',
          [
            'delimiter' => ';',
          ]
        ),
        'baz' => new \codename\core\io\datasource\csv(
          __DIR__.'/joined1_join2.csv',
          [
            'delimiter' => ';',
          ]
        ),
        'qux' => new \codename\core\io\datasource\csv(
          __DIR__.'/joined1_join3.csv',
          [
            'delimiter' => ';',
          ]
        )
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
        ]
      ]
    );

    $res = [];
    foreach($joinedDatasource as $i => $d) {
      $res[] = $d;
    }

    $this->assertCount(8, $res);

    $this->assertEquals(['A','A','B','C','D','D','E','F'], array_column($res, 'col1'));
    $this->assertEquals(['A1','A2','B1','C1','D1','D2','F1'], array_column($res, 'join3_value'));
  }
}
