<?php
namespace codename\core\io\tests\datasource;

use codename\core\tests\base;

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
    ]);

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
}
