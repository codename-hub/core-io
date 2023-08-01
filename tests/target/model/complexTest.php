<?php

namespace codename\core\io\tests\target\model;

use codename\core\exception;
use codename\core\io\datasource\model;
use codename\core\io\target\model\complex;
use codename\core\test\base;
use PDOException;
use ReflectionException;

class complexTest extends base
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
    public function testGeneral(): void
    {
        $target = new complex('test', [
          'structure' => [
            'model' => 'testmodel',
            'join' => [
              [
                'model' => 'testmodelj',
                'join' => [],
              ],
            ],
          ],
            // default method fallback should be: save
        ]);

        static::assertInstanceOf(\codename\core\model::class, $target->getModel());
    }

    /**
     * @return void
     * @throws ReflectionException
     * @throws exception
     */
    public function testTargetStoreSimple(): void
    {
        $target = new complex('test', [
          'structure' => [
            'model' => 'testmodel',
            'join' => [
              [
                'model' => 'testmodelj',
                'join' => [],
              ],
            ],
          ],
            // default method fallback should be: save
        ]);

        $target->store([
          'testmodel_text' => 'simple',
        ]);

        $target->finish();

        $model = $this->getModel('testmodel');
        $res = $model
          ->addFilter('testmodel_text', 'simple')
          ->search()->getResult();
        static::assertCount(1, $res);
    }

    /**
     * @return void
     * @throws ReflectionException
     * @throws exception
     */
    public function testTargetStoreSimpleVirtual(): void
    {
        $target = new complex('test', [
          'structure' => [
            'model' => 'testmodel',
            'join' => [
              [
                'model' => 'testmodelj',
                'join' => [],
              ],
            ],
          ],
            // default method fallback should be: save
        ]);

        $target->setVirtualStoreEnabled(true);
        static::assertTrue($target->getVirtualStoreEnabled());

        $target->store([
          'testmodel_text' => 'simple',
          'testmodelj_text' => 'simple',
        ]);

        $target->finish();

        $result = $target->getVirtualStoreData();

        static::assertEquals([
          [
            'testmodel_text' => 'simple',
            'testmodelj_text' => 'simple',
          ],
        ], $result);
    }

    /**
     * @return void
     * @throws ReflectionException
     * @throws exception
     */
    public function testTargetStoreChildrenSimpleVirtual(): void
    {
        $target = new complex('test', [
          'structure' => [
            'model' => 'testmodelj',
            'join' => [
              [
                'model' => 'testmodel',
                'join' => [],
              ],
            ],
          ],
            // default method fallback should be: save
        ]);

        $target->setVirtualStoreEnabled(true);
        static::assertTrue($target->getVirtualStoreEnabled());

        $target->store([
          'testmodelj_text' => 'simple',
          'testmodelj_testmodel' => [
            'testmodel_text' => 'simple',
          ],
        ]);

        $target->finish();

        $result = $target->getVirtualStoreData();

        static::assertEquals([
          [
            'testmodelj_text' => 'simple',
            'testmodelj_testmodel' => [
              'testmodel_text' => 'simple',
            ],
          ],
        ], $result);
    }

    /**
     * @return void
     * @throws ReflectionException
     * @throws exception
     */
    public function testTargetReplaceStoreSimpleVirtual(): void
    {
        $target = new complex('test', [
          'structure' => [
            'model' => 'testmodel',
            'join' => [
              [
                'model' => 'testmodelj',
                'join' => [],
              ],
            ],
          ],
          'method' => 'replace',
        ]);

        $target->setVirtualStoreEnabled(true);
        static::assertTrue($target->getVirtualStoreEnabled());

        $target->store([
          'testmodel_id' => 1,
          'testmodel_text' => 'simple',
          'testmodelj_text' => 'simple',
        ]);

        $target->finish();

        $result = $target->getVirtualStoreData();

        static::assertEquals([
          [
            'testmodel_id' => 1,
            'testmodel_text' => 'simple',
            'testmodelj_text' => 'simple',
          ],
        ], $result);
    }

    /**
     * @return void
     * @throws ReflectionException
     * @throws exception
     */
    public function testTargetReplaceStoreSimpleWithoutUnique(): void
    {
        $target = new complex('test', [
          'structure' => [
            'model' => 'testmodelj',
            'join' => [
            ],
          ],
          'method' => 'replace',
        ]);

        $target->setVirtualStoreEnabled(true);
        static::assertTrue($target->getVirtualStoreEnabled());

        $target->store([
          'testmodelj_text' => 'simple',
        ]);

        $target->finish();

        $result = $target->getVirtualStoreData();

        static::assertEquals([
          [
            'testmodelj_text' => 'simple',
          ],
        ], $result);
    }

    /**
     * @return void
     * @throws ReflectionException
     * @throws exception
     */
    public function testTargetStoreByUniqueVirtual(): void
    {
        $target = new complex('test', [
          'structure' => [
            'model' => 'testmodel',
            'join' => [
              [
                'model' => 'testmodelj',
                'join' => [],
              ],
            ],
          ],
          'method' => 'replace',
        ]);

        $target->store([
          'testmodel_text' => 'unique_single1',
          'testmodel_unique_single' => 'UNIQUE1',
        ]);

        //
        // Get the ID just created
        // for usage, further below.
        //
        $unique1Id = $this->getModel('testmodel')
          ->addFilter('testmodel_unique_single', 'UNIQUE1')
          ->search()->getResult()[0]['testmodel_id'];
        static::assertNotNull($unique1Id);

        $target->store([
          'testmodel_text' => 'unique_multi1',
          'testmodel_unique_multi1' => 'UNIQUE_V1',
          'testmodel_unique_multi2' => 'UNIQUE_V2',
        ]);

        $target->setVirtualStoreEnabled(true);
        static::assertTrue($target->getVirtualStoreEnabled());

        $target->store([
          'testmodel_text' => 'unique_single2',
          'testmodel_unique_single' => 'UNIQUE1',
        ]);

        $target->store([
          'testmodel_text' => 'unique_multi2',
          'testmodel_unique_multi1' => 'UNIQUE_V1_',
          'testmodel_unique_multi2' => 'UNIQUE_V2_',
        ]);

        $target->finish();

        $model = $this->getModel('testmodel');

        $res = $model->search()->getResult();
        static::assertCount(2, $res);

        $result = $target->getVirtualStoreData();

        static::assertEquals([
          [
            'testmodel_text' => 'unique_single2',
            'testmodel_unique_single' => 'UNIQUE1',
            'testmodel_id' => $unique1Id,
          ],
          [
            'testmodel_text' => 'unique_multi2',
            'testmodel_unique_multi1' => 'UNIQUE_V1_',
            'testmodel_unique_multi2' => 'UNIQUE_V2_',
          ],
        ], $result);
    }

    /**
     * @return void
     * @throws ReflectionException
     * @throws exception
     */
    public function testTargetStoreByUnique(): void
    {
        $target = new complex('test', [
          'structure' => [
            'model' => 'testmodel',
            'join' => [
              [
                'model' => 'testmodelj',
                'join' => [],
              ],
            ],
          ],
          'method' => 'replace',
        ]);

        $target->store([
          'testmodel_text' => 'unique_single1',
          'testmodel_unique_single' => 'UNIQUE1',
        ]);

        $target->store([
          'testmodel_text' => 'unique_single2',
          'testmodel_unique_single' => 'UNIQUE1',
        ]);

        $target->store([
          'testmodel_text' => 'unique_multi1',
          'testmodel_unique_multi1' => 'UNIQUE_V1',
          'testmodel_unique_multi2' => 'UNIQUE_V2',
        ]);
        $target->store([
          'testmodel_text' => 'unique_multi2',
          'testmodel_unique_multi1' => 'UNIQUE_V1',
          'testmodel_unique_multi2' => 'UNIQUE_V2',
        ]);

        $target->finish();

        $model = $this->getModel('testmodel');

        $res = $model->search()->getResult();
        static::assertCount(2, $res);
    }

    /**
     * @return void
     * @throws ReflectionException
     * @throws exception
     */
    public function testTargetStoreBySingularUniqueWillFail(): void
    {
        $this->expectException(PDOException::class);

        $target = new complex('test', [
          'structure' => [
            'model' => 'testmodel',
            'join' => [
              [
                'model' => 'testmodelj',
                'join' => [],
              ],
            ],
          ],
          'method' => 'save',
        ]);

        $target->store([
          'testmodel_text' => 'unique_single1',
          'testmodel_unique_single' => 'UNIQUE1',
        ]);

        $target->store([
          'testmodel_text' => 'unique_single2',
          'testmodel_unique_single' => 'UNIQUE1',
        ]);
    }

    /**
     * @return void
     * @throws ReflectionException
     * @throws exception
     */
    public function testTargetStoreByMultipleUniqueWillFail(): void
    {
        $this->expectException(PDOException::class);

        $target = new complex('test', [
          'structure' => [
            'model' => 'testmodel',
            'join' => [
              [
                'model' => 'testmodelj',
                'join' => [],
              ],
            ],
          ],
          'method' => 'save',
        ]);

        $target->store([
          'testmodel_text' => 'unique_multi1',
          'testmodel_unique_multi1' => 'UNIQUE_V1',
          'testmodel_unique_multi2' => 'UNIQUE_V2',
        ]);
        $target->store([
          'testmodel_text' => 'unique_multi2',
          'testmodel_unique_multi1' => 'UNIQUE_V1',
          'testmodel_unique_multi2' => 'UNIQUE_V2',
        ]);
    }

    /**
     * @return void
     * @throws ReflectionException
     * @throws exception
     */
    public function testTargetStoreReadUsingDatasource(): void
    {
        $target = new complex('test', [
          'structure' => [
            'model' => 'testmodel',
            'join' => [
              [
                'model' => 'testmodelj',
                'join' => [],
              ],
            ],
          ],
          'method' => 'replace',
        ]);

        $target->store([
          'testmodel_text' => 'unique_single1',
          'testmodel_unique_single' => 'UNIQUE1',
        ]);

        $target->store([
          'testmodel_text' => 'unique_single2',
          'testmodel_unique_single' => 'UNIQUE1',
        ]);

        $target->store([
          'testmodel_text' => 'unique_multi1',
          'testmodel_unique_multi1' => 'UNIQUE_V1',
          'testmodel_unique_multi2' => 'UNIQUE_V2',
        ]);
        $target->store([
          'testmodel_text' => 'unique_multi2',
          'testmodel_unique_multi1' => 'UNIQUE_V1',
          'testmodel_unique_multi2' => 'UNIQUE_V2',
        ]);

        $datasource = new model([
          'query' => [],
          'model' => 'testmodel',
        ]);

        $res = [];
        foreach ($datasource as $r) {
            $res[] = $r;
        }

        static::assertEquals(['unique_single2', 'unique_multi2'], array_column($res, 'testmodel_text'));
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

        static::createModel(
            'targettest',
            'testmodel',
            testmodel::$staticConfig,
            function ($schema, $model, $config) {
                return new testmodel([]);
            }
        );

        static::createModel(
            'targettest',
            'testmodelj',
            testmodelj::$staticConfig,
            function ($schema, $model, $config) {
                return new testmodelj([]);
            }
        );

        static::architect('targettest', 'codename', 'test');
    }
}
