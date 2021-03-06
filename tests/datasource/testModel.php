<?php
namespace codename\core\io\tests\datasource;

use codename\core\test\base;

class testModel extends base {
  /**
   * [protected description]
   * @var bool
   */
  protected static $initialized = false;

  /**
   * @inheritDoc
   */
  public static function tearDownAfterClass(): void
  {
    parent::tearDownAfterClass();
    static::$initialized = false;
  }


  /**
   * @inheritDoc
   */
  protected function tearDown(): void
  {
    $this->getModel('datasourceentry')
      ->addFilter('datasourceentry_id', 0, '>')
      ->delete();
  }

  /**
   * @inheritDoc
   */
  protected function setUp(): void
  {
    $app = static::createApp();

    // Additional overrides to get a more complete app lifecycle
    // and allow static global app::getModel() to work correctly
    $app->__setApp('datasourcetest');
    $app->__setVendor('codename');
    $app->__setNamespace('\\codename\\core\\io\\tests\\datasource');

    $app->getAppstack();

    // avoid re-init
    if(static::$initialized) {
      return;
    }

    static::$initialized = true;

    static::setEnvironmentConfig([
      'test' => [
        'database' => [
          // NOTE: by default, we do these tests using
          // pure in-memory sqlite.
          'default' => [
            'driver' => 'sqlite',
            // 'database_file' => 'testmodel.sqlite',
            'database_file' => ':memory:',
          ],
        ],
        'cache' => [
          'default' => [
            'driver' => 'memory'
          ]
        ],
        'filesystem' =>[
          'local' => [
            'driver' => 'local',
          ]
        ],
        'log' => [
          'default' => [
            'driver' => 'system',
            'data' => [
              'name' => 'dummy'
            ]
          ]
        ],
      ]
    ]);

    static::createModel('datasourcetest', 'datasourceentry', [
      'field' => [
        'datasourceentry_id',
        'datasourceentry_created',
        'datasourceentry_modified',
        'datasourceentry_text',
        'datasourceentry_integer',
      ],
      'primary' => [
        'datasourceentry_id'
      ],
      'datatype' => [
        'datasourceentry_id'       => 'number_natural',
        'datasourceentry_created'  => 'text_timestamp',
        'datasourceentry_modified' => 'text_timestamp',
        'datasourceentry_text'     => 'text',
        'datasourceentry_integer'  => 'number_natural',
      ],
      'connection' => 'default'
    ], function($schema, $model, $config) {
      return new \codename\core\io\tests\datasource\model\datasourceentry([]);
    });

    static::createModel('datasourcetest', 'datasourceentryj', [
      'field' => [
        'datasourceentryj_id',
        'datasourceentryj_created',
        'datasourceentryj_modified',
        'datasourceentryj_datasourceentry_id',
        'datasourceentryj_text',
      ],
      'primary' => [
        'datasourceentryj_id'
      ],
      'foreign' => [
        'datasourceentryj_datasourceentry_id' => [
          'schema'  => 'datasourcetest',
          'model'   => 'datasourceentry',
          'key'     => 'datasourceentry_id'
        ],
      ],
      'datatype' => [
        'datasourceentryj_id'                 => 'number_natural',
        'datasourceentryj_created'            => 'text_timestamp',
        'datasourceentryj_modified'           => 'text_timestamp',
        'datasourceentryj_datasourceentry_id' => 'number_natural',
        'datasourceentryj_text'               => 'text',
      ],
      'connection' => 'default'
    ], function($schema, $model, $config) {
      return new \codename\core\io\tests\datasource\model\datasourceentryj([]);
    });

    static::architect('datasourcetest', 'codename', 'test');
  }

  /**
   * [createTestData description]
   */
  protected function createTestData(): void {
    $datasets = [
      [
        'datasourceentry_text'    => 'foo',
        'datasourceentry_integer' => 111,
      ],
      [
        'datasourceentry_text'    => 'bar',
        'datasourceentry_integer' => 222,
      ],
      [
        'datasourceentry_text'    => 'baz',
        'datasourceentry_integer' => null,
      ],
      [
        'datasourceentry_text'    => 'qux',
        'datasourceentry_integer' => 333,
      ],
    ];
    $model = $this->getModel('datasourceentry');
    foreach ($datasets as $dataset) {
      $model->save($dataset);
    }
  }

  /**
   * [testDatasourceDoubleSetModelWillFail description]
   */
  public function testDatasourceDoubleSetModelWillFail(): void {
    $this->expectException(\codename\core\exception::class);
    $this->expectExceptionMessage('EXCEPTION_DATASOURCE_MODEL_ALREADY_SET');
    $datasource = new \codename\core\io\datasource\model();
    $model = $this->getModel('datasourceentry');
    $datasource->setModel($model);
    $datasource->setModel($model); // second call should fail
  }

  /**
   * [testSimpleDatasourceModelQuery description]
   */
  public function testSimpleDatasourceModelQuery(): void {
    $this->createTestData();
    $datasource = new \codename\core\io\datasource\model();
    $datasource->setQuery([]);
    $model = $this->getModel('datasourceentry');
    $datasource->setModel($model);
    $res = [];
    foreach($datasource as $d) {
      $res[] = $d;
    }
    $this->assertCount(4, $res);
  }

  /**
   * [testSimpleDatasourceModelQueryBuffered description]
   */
  public function testSimpleDatasourceModelQueryBuffered(): void {
    $this->createTestData();
    $datasource = new \codename\core\io\datasource\model([
      'offset_buffering'    => true,
      'offset_buffer_size'  => 2,

    ]);
    $datasource->setQuery([]);
    $model = $this->getModel('datasourceentry');
    $datasource->setModel($model);
    $this->assertCount(4, $datasource);
  }

  /**
   * [testSimpleDatasourceModelQueryBufferedLimited description]
   */
  public function testSimpleDatasourceModelQueryBufferedLimited(): void {
    $this->createTestData();
    $datasource = new \codename\core\io\datasource\model([
      'offset_buffering'    => true,
      'offset_buffer_size'  => 2,
      'offset_limit'        => 3,

    ]);
    $datasource->setQuery([]);
    $model = $this->getModel('datasourceentry');
    $datasource->setModel($model);
    $this->assertCount(3, $datasource);
  }

  /**
   * [testDatasourceModelComplexOne description]
   */
  public function testDatasourceModelComplexOne(): void {
    $this->createTestData();
    $datasource = new \codename\core\io\datasource\model([
      'model'               => 'datasourceentry',
      'join'                => [
        [
          'model'   => 'datasourceentryj',
          'fields'  => [
            'datasourceentryj_id'
          ],
        ],
      ],
      'virtualFieldResult'  => true,
      'fields'              => [
        'datasourceentry_text',
        'datasourceentry_integer',
      ],
      'filter'              => [
        [ 'field' => 'datasourceentry_integer', 'value' => null, 'operator' => '!=' ]
      ],
      'filtercollection'    => [
        [
          'filters' => [
            [ 'field' => 'datasourceentry_integer', 'value' => 111, 'operator' => '=' ],
            [ 'field' => 'datasourceentry_integer', 'value' => 222, 'operator' => '=' ],
          ],
          'group_operator' => 'OR',
          'group_name' => 'datasourceentry_integer',
        ]
      ],
      'query'               => [
        'order' => [
          [
            "field" => "datasourceentry_integer",
            "order" => "DESC"
          ]
        ]
      ],
    ]);

    $res = [];
    foreach($datasource as $d) {
      $res[] = $d;
    }
    $this->assertEquals([
      [
        'datasourceentry_text'    => 'bar',
        'datasourceentry_integer' => 222,
        'datasourceentryj_id'     => null,
      ],
      [
        'datasourceentry_text'    => 'foo',
        'datasourceentry_integer' => 111,
        'datasourceentryj_id'     => null,
      ],
    ], $res);
  }

  /**
   * [testDatasourceModelComplexOne description]
   */
  public function testDatasourceModelComplexTwo(): void {
    $this->createTestData();
    $datasource = new \codename\core\io\datasource\model([
      'model'               => 'datasourceentry',
      'virtualFieldResult'  => true,
      'fields'              => [
        'datasourceentry_text',
        'datasourceentry_integer',
      ],
      'query'               => [
        'filter'              => [
          [ 'field' => 'datasourceentry_integer', 'value' => [ 'option' => 'filter1' ], 'operator' => '!=' ]
        ],
        'filtercollection'    => [
          [
            'filters' => [
              [ 'field' => 'datasourceentry_integer', 'value' => [ 'option' => 'filtercollection1' ], 'operator' => '=' ],
              [ 'field' => 'datasourceentry_integer', 'value' => [ 'option' => 'filtercollection2' ], 'operator' => '=' ],
            ],
            'group_operator' => 'OR',
            'group_name' => 'datasourceentry_integer',
          ]
        ],
        'order' => [
          [
            "field" => "datasourceentry_integer",
            "order" => "DESC"
          ]
        ]
      ],
    ]);

    $pipline = new \codename\core\io\pipeline(null, []);
    $pipline->setOptions([
      'filter1'           => null,
      'filtercollection1' => 111,
      'filtercollection2' => 222,
    ]);

    $datasource->setPipelineInstance($pipline);

    $res = [];
    foreach($datasource as $d) {
      $res[] = $d;
    }

    $this->assertEquals(3, $datasource->currentProgressPosition());
    $this->assertEquals(2, $datasource->currentProgressLimit());
    $this->assertEquals([
      [
        'datasourceentry_text'    => 'bar',
        'datasourceentry_integer' => 222,
      ],
      [
        'datasourceentry_text'    => 'foo',
        'datasourceentry_integer' => 111,
      ],
    ], $res);
  }

}
