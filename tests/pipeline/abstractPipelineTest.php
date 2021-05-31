<?php
namespace codename\core\io\tests\pipeline;

use codename\core\test\base;
use codename\core\test\overrideableApp;

abstract class abstractPipelineTest extends base {

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

    // fake response instance
    $app->__setInstance('response', new \codename\core\response\cli([]));

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
   * [createTestData description]
   */
  protected function createTestData(): void {
    $model = $this->getModel('pipelinemodel');

    $datasets = [
      [
        'pipelinemodel_text' => 'first',
      ],
      [
        'pipelinemodel_text' => 'second',
      ],
      [
        'pipelinemodel_text' => 'third',
      ],
    ];

    foreach($datasets as $dataset) {
      $model->save($dataset);
    }
  }

}
