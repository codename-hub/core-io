<?php

namespace codename\core\io\tests\target;

use codename\core\exception;
use codename\core\io\target\virtual;
use codename\core\io\tests\target\model\testmodel;
use codename\core\model;
use codename\core\test\base;
use ReflectionException;

/**
 * [virtualTest description]
 */
class virtualTest extends base
{
    /**
     * [protected description]
     * @var bool
     */
    protected static bool $initialized = false;

    /**
     * {@inheritDoc}
     */
    public static function tearDownAfterClass(): void
    {
        parent::tearDownAfterClass();
        static::$initialized = false;
    }

    /**
     * @return void
     * @throws ReflectionException
     * @throws exception
     */
    public function testVirtualGeneral(): void
    {
        $target = new virtual('general_example', [
          'model' => 'testmodel',
        ]);

        // set data
        $result = $target->store([
          'testmodel_text' => 'data',
        ]);
        static::assertTrue($result);

        // get data
        $result = $target->getVirtualStoreData();
        static::assertEquals([
          ['testmodel_text' => 'data'],
        ], $result);

        // check finish
        try {
            $target->finish();
        } catch (\Exception) {
            static::fail();
        }

        // check set virtual store
        try {
            $target->setVirtualStoreEnabled(true);
        } catch (\Exception) {
            static::fail();
        }

        // check virtual store state
        static::assertTrue($target->getVirtualStoreEnabled());
    }

    /**
     * [testVirtualModel description]
     * @throws ReflectionException
     * @throws exception
     */
    public function testVirtualModel(): void
    {
        $target = new virtual('model_example', [
          'model' => 'testmodel',
        ]);

        $model = $target->getModel();
        static::assertInstanceOf(model::class, $model);
    }

    /**
     * [testVirtualModel description]
     * @throws ReflectionException
     * @throws exception
     */
    public function testVirtualWrongModel(): void
    {
        $this->expectException(exception::class);
        $this->expectExceptionMessage('EXCEPTION_GETMODEL_MODELNOTFOUND');

        new virtual('wrong_model_example', [
          'model' => 'example',
        ]);
    }

    /**
     * [testVirtualFinishedError description]
     * @throws ReflectionException
     * @throws exception
     */
    public function testVirtualFinishedError(): void
    {
        $this->expectException(exception::class);
        $this->expectExceptionMessage('EXCEPTION_CORE_IO_TARGET_VIRTUAL_ALREADY_FINISHED');

        $target = new virtual('finished_error', [
          'model' => 'testmodel',
        ]);

        // set finish
        try {
            $target->finish();
        } catch (\Exception) {
            static::fail();
        }

        // set data
        $target->store([
          'testmodel_text' => 'data',
        ]);
    }

    /**
     * {@inheritDoc}
     * @throws ReflectionException
     * @throws exception
     */
    protected function tearDown(): void
    {
        $this->getModel('testmodel')
          ->addFilter('testmodel_id', 0, '>')
          ->delete();
    }

    /**
     * {@inheritDoc}
     * @throws ReflectionException
     * @throws exception
     */
    protected function setUp(): void
    {
        $app = static::createApp();

        // Additional overrides to get a more complete app lifecycle
        // and allow static global app::getModel() to work correctly
        $app::__setApp('targettest');
        $app::__setVendor('codename');
        $app::__setNamespace('\\codename\\core\\io\\tests\\target');

        $app::getAppstack();

        // avoid re-init
        if (static::$initialized) {
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
                'driver' => 'memory',
              ],
            ],
            'filesystem' => [
              'local' => [
                'driver' => 'local',
              ],
            ],
            'log' => [
              'default' => [
                'driver' => 'system',
                'data' => [
                  'name' => 'dummy',
                ],
              ],
            ],
          ],
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
            'testmodel_id',
          ],
          'unique' => [
            'testmodel_unique_single',
            ['testmodel_unique_multi1', 'testmodel_unique_multi2'],
          ],
          'options' => [
            'testmodel_unique_single' => [
              'length' => 16,
            ],
            'testmodel_unique_multi1' => [
              'length' => 16,
            ],
            'testmodel_unique_multi2' => [
              'length' => 16,
            ],
          ],
          'datatype' => [
            'testmodel_id' => 'number_natural',
            'testmodel_created' => 'text_timestamp',
            'testmodel_modified' => 'text_timestamp',
            'testmodel_text' => 'text',
            'testmodel_unique_single' => 'text',
            'testmodel_unique_multi1' => 'text',
            'testmodel_unique_multi2' => 'text',
          ],
          'connection' => 'default',
        ], function ($schema, $model, $config) {
            return new testmodel([]);
        });

        static::architect('targettest', 'codename', 'test');
    }
}
