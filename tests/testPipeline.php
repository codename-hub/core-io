<?php
namespace codename\core\io\tests;

use codename\core\test\base;
use codename\core\test\overrideableApp;

class testPipeline extends base
{

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
    $this->getModel('pipelinemodel')
      ->addFilter('pipelinemodel_id', 0, '>')
      ->delete();

    overrideableApp::__setInstance('response', null);
  }

  /**
   * @inheritDoc
   */
  protected function setUp(): void
  {
    $app = static::createApp();

    overrideableApp::__injectApp([
      'vendor' => 'codename',
      'app' => 'core-io',
      'namespace' => '\\codename\\core\\io'
    ]);

    // Additional overrides to get a more complete app lifecycle
    // and allow static global app::getModel() to work correctly
    $app->__setApp('pipelinetest');
    $app->__setVendor('codename');
    $app->__setNamespace('\\codename\\core\\io\\tests\\pipeline');

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
            // 'database_file' => 'pipelinemodel.sqlite',
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

    static::createModel(
      'pipelinetest', 'pipelinemodel',
      \codename\core\io\tests\pipeline\model\pipelinemodel::$staticConfig,
      function($schema, $model, $config) {
        return new \codename\core\io\tests\pipeline\model\pipelinemodel([]);
      }
    );

    static::architect('pipelinetest', 'codename', 'test');
  }

  /**
   * [testPipelineGeneral description]
   */
  public function testPipelineGeneral(): void {
    $pipline = new \codename\core\io\pipeline(null, [
      'example' => true
    ]);

    //
    // check config
    //
    $this->assertEquals([ 'example' => true ], $pipline->getConfig()->get());

    //
    // check options
    //
    $this->assertNull($pipline->getOption('example'));
    $pipline->setOptions([ 'example' => true ]);
    $this->assertTrue($pipline->getOption('example'));

    //
    // check dryrun
    //
    $pipline->setDryRun(true);
    $this->assertTrue($pipline->getDryRun());

    //
    // check erroneous
    //
    $this->assertEmpty($pipline->getErroneousEntries());

    //
    // set default?
    //
    $pipline->setDebug(true);
    $pipline->setLimit(0, 10);
    $pipline->setThrowExceptionOnErroneousData(true);
    $pipline->setErrorstackEnabled(true);
    $pipline->setSkipErroneous(true);
    $pipline->setTrackErroneous(true);
  }

  /**
   * [testPipelineDatasource description]
   */
  public function testPipelineDatasource(): void {
    $pipline = new \codename\core\io\pipeline(null, []);

    $datasource = new \codename\core\io\datasource\arraydata();
    $datasource->setData([
      [
        'key1' => 'abc',
        'key2' => 'def',
        'key3' => 'ghi',
      ],
      [
        'key1' => 'jkl',
        'key2' => 'mno',
        'key3' => 'pqr',
      ],
      [
        'key1' => 'stu',
        'key2' => 'vwx',
        'key3' => 'yz',
      ]
    ]);
    $pipline->setDatasource($datasource);
    $this->assertInstanceOf(\codename\core\io\datasource\arraydata::class, $pipline->getDatasource());

    $this->assertEquals(3, $pipline->getItemCount());
    $this->assertEquals(null, $pipline->getItemIndex());
    $this->assertEquals(null, $pipline->getStoredItemCount());

  }

  /**
   * [testPipelineDatasourcePipelineInstance description]
   */
  public function testPipelineDatasourcePipelineInstance(): void {
    $datasource = new \codename\core\io\datasource\model();

    $pipline = new \codename\core\io\pipeline(null, []);
    $pipline->setDatasource($datasource);
    $this->assertInstanceOf(\codename\core\io\datasource\model::class, $pipline->getDatasource());

  }

  /**
   * [testPipelineCreateDatasourcePipelineInstance description]
   */
  public function testPipelineCreateDatasourcePipelineInstance(): void {
    $pipline = new \codename\core\io\pipeline(null, [
      'source'    => [
        'type'    => 'model',
        'config'  => [],
      ],
    ]);
    $datasource = $pipline->createDatasource([]);
    $pipline->setDatasource($datasource);
    $this->assertInstanceOf(\codename\core\io\datasource\model::class, $pipline->getDatasource());

  }

  /**
   * [testPipelineDatasourceBuffered description]
   */
  public function testPipelineDatasourceBuffered(): void {
    $pipline = new \codename\core\io\pipeline(null, [
      'config'  => [
        'datasource_buffering'    => true,
        'datasource_buffer_size'  => 100,
      ],
    ]);

    $datasource = new \codename\core\io\datasource\arraydata();
    $datasource->setData([
      [
        'key1' => 'abc',
        'key2' => 'def',
        'key3' => 'ghi',
      ],
      [
        'key1' => 'jkl',
        'key2' => 'mno',
        'key3' => 'pqr',
      ],
      [
        'key1' => 'stu',
        'key2' => 'vwx',
        'key3' => 'yz',
      ]
    ]);
    $pipline->setDatasource($datasource);
    $this->assertInstanceOf(\codename\core\io\datasource\buffered::class, $pipline->getDatasource());

  }

  /**
   * [testPipelineDatasource description]
   */
  public function testPipelineDatasourceLoad(): void {
    $pipline = new \codename\core\io\pipeline('tests/testPipeline.json', []);

    $datasource = new \codename\core\io\datasource\arraydata();
    $datasource->setData([
      [
        'key1' => 'abc',
        'key2' => 'def',
        'key3' => 'ghi',
      ],
      [
        'key1' => 'jkl',
        'key2' => 'mno',
        'key3' => 'pqr',
      ],
      [
        'key1' => 'stu',
        'key2' => 'vwx',
        'key3' => 'yz',
      ]
    ]);
    $pipline->setDatasource($datasource);
    $this->assertInstanceOf(\codename\core\io\datasource\arraydata::class, $pipline->getDatasource());

    $this->assertEquals(3, $pipline->getItemCount());
    $this->assertEquals(null, $pipline->getItemIndex());
    $this->assertEquals(null, $pipline->getStoredItemCount());

  }

  /**
   * Test Spec output (simple case)
   */
  public function testSpecification(): void {
    $pipline = new \codename\core\io\pipeline('tests/testPipeline2.json', []);

    $this->assertEquals(
      [
        'target.example.example1' => [
          'type'    => 'target.mapping',
          'source'  => [ 'transform.example1' ],
        ],
        'target.example.example2' => [
          'type'    => 'target.mapping',
          'source'  => [ 'transform.example2' ],
        ],
        'target.example.example3' => [
          'type'    => 'target.mapping',
          'source'  => [ 'transform.example3' ],
        ],
        'target.example' => [
          'type'    => 'target',
          'source'  => [ 'target.example.example1', 'target.example.example2', 'target.example.example3' ],
        ],
        'transform.example1' => [
          'type'    => 'transform',
          'source'  => [  ],
        ],
        'transform.example2' => [
          'type'    => 'transform',
          'source'  => [ 'model.pipelinemodel' ],
        ],
        'transform.example3' => [
          'type'    => 'transform',
          'source'  => [ 'source.example3' ],
        ],
        'model.pipelinemodel' => [
          'type'    => 'model',
        ],
        'source.example3' => [
          'type'    => 'source',
        ],
        'erroneous.erroneous' => [
          'type'    => 'erroneous',
        ],
        'erroneous.data' => [
          'type'    => 'erroneous',
        ],
      ],
      $pipline->getSpecification(),
      json_encode($pipline->getSpecification())
    );
  }

}
