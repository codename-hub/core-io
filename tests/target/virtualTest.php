<?php
namespace codename\core\io\tests\target;

use codename\core\test\base;

/**
 * [virtualTest description]
 */
class virtualTest extends base
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
    $this->getModel('testmodel')
      ->addFilter('testmodel_id', 0, '>')
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
    $app->__setApp('targettest');
    $app->__setVendor('codename');
    $app->__setNamespace('\\codename\\core\\io\\tests\\target');

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

    static::createModel('targettest', 'testmodel', [
      'field' => [
        'testmodel_id',
        'testmodel_created',
        'testmodel_modified',
        'testmodel_text',
        'testmodel_unique_single',
        'testmodel_unique_multi1',
        'testmodel_unique_multi2',
      ],
      'primary' => [
        'testmodel_id'
      ],
      'unique' => [
        'testmodel_unique_single',
        [ 'testmodel_unique_multi1', 'testmodel_unique_multi2' ],
      ],
      'options' => [
        'testmodel_unique_single' => [
          'length' => 16
        ],
        'testmodel_unique_multi1' => [
          'length' => 16
        ],
        'testmodel_unique_multi2' => [
          'length' => 16
        ],
      ],
      'datatype' => [
        'testmodel_id'       => 'number_natural',
        'testmodel_created'  => 'text_timestamp',
        'testmodel_modified' => 'text_timestamp',
        'testmodel_text'     => 'text',
        'testmodel_unique_single' => 'text',
        'testmodel_unique_multi1' => 'text',
        'testmodel_unique_multi2' => 'text',
      ],
      'connection' => 'default'
    ], function($schema, $model, $config) {
      return new \codename\core\io\tests\target\model\testmodel([]);
    });

    static::architect('targettest', 'codename', 'test');
  }

  /**
   * [testVirtualGeneral description]
   */
  public function testVirtualGeneral(): void {

    $target = new \codename\core\io\target\virtual('general_example', [
      'model' => 'testmodel'
    ]);

    // set data
    $result = $target->store([
      'testmodel_text' => 'data'
    ]);
    $this->assertTrue($result);

    // get data
    $result = $target->getVirtualStoreData();
    $this->assertEquals([
      [ 'testmodel_text' => 'data' ]
    ], $result);

    // check finish
    $this->assertEmpty($target->finish());

    // check set virtual store
    $this->assertEmpty($target->setVirtualStoreEnabled(true));

    // check virtual store state
    $this->assertTrue($target->getVirtualStoreEnabled());

  }

  /**
   * [testVirtualModel description]
   */
  public function testVirtualModel(): void {

    $target = new \codename\core\io\target\virtual('model_example', [
      'model' => 'testmodel'
    ]);

    $model = $target->getModel();
    $this->assertInstanceOf(\codename\core\model::class, $model);

  }

  /**
   * [testVirtualModel description]
   */
  public function testVirtualWrongModel(): void {
    $this->expectException(\codename\core\exception::class);
    $this->expectExceptionMessage('EXCEPTION_GETMODEL_MODELNOTFOUND');

    $target = new \codename\core\io\target\virtual('wrong_model_example', [
      'model' => 'example'
    ]);

  }

  /**
   * [testVirtualFinishedError description]
   */
  public function testVirtualFinishedError(): void {
    $this->expectException(\codename\core\exception::class);
    $this->expectExceptionMessage('EXCEPTION_CORE_IO_TARGET_VIRTUAL_ALREADY_FINISHED');

    $target = new \codename\core\io\target\virtual('finished_error', [
      'model' => 'testmodel'
    ]);

    // set finish
    $this->assertEmpty($target->finish());

    // set data
    $result = $target->store([
      'testmodel_text' => 'data'
    ]);

  }

}
