<?php

namespace codename\core\io\tests;

use codename\core\exception;
use codename\core\io\datasource\arraydata;
use codename\core\io\datasource\buffered;
use codename\core\io\datasource\model;
use codename\core\io\pipeline;
use codename\core\io\tests\pipeline\model\pipelinemodel;
use codename\core\test\base;
use codename\core\test\overrideableApp;
use ReflectionException;

class pipelineTest extends base
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
     * [testPipelineGeneral description]
     * @throws ReflectionException
     * @throws exception
     */
    public function testPipelineGeneral(): void
    {
        $pipeline = new pipeline(null, [
          'example' => true,
        ]);

        //
        // check config
        //
        static::assertEquals(['example' => true], $pipeline->getConfig()->get());

        //
        // check options
        //
        static::assertNull($pipeline->getOption('example'));
        $pipeline->setOptions(['example' => true]);
        static::assertTrue($pipeline->getOption('example'));

        //
        // check dryrun
        //
        $pipeline->setDryRun();
        static::assertTrue($pipeline->getDryRun());

        //
        // check erroneous
        //
        static::assertEmpty($pipeline->getErroneousEntries());

        //
        // set default?
        //
        $pipeline->setDebug(true);
        $pipeline->setLimit(0, 10);
        $pipeline->setThrowExceptionOnErroneousData();
        $pipeline->setErrorstackEnabled();
        $pipeline->setSkipErroneous();
        $pipeline->setTrackErroneous();
    }

    /**
     * [testPipelineDatasource description]
     * @throws ReflectionException
     * @throws exception
     */
    public function testPipelineDatasource(): void
    {
        $pipeline = new pipeline(null, []);

        $datasource = new arraydata();
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
          ],
        ]);
        $pipeline->setDatasource($datasource);
        static::assertInstanceOf(arraydata::class, $pipeline->getDatasource());

        static::assertEquals(3, $pipeline->getItemCount());
        static::assertEquals(null, $pipeline->getItemIndex());
        static::assertEquals(null, $pipeline->getStoredItemCount());
    }

    /**
     * [testPipelineDatasourcePipelineInstance description]
     * @throws ReflectionException
     * @throws exception
     */
    public function testPipelineDatasourcePipelineInstance(): void
    {
        $datasource = new model();

        $pipeline = new pipeline(null, []);
        $pipeline->setDatasource($datasource);
        static::assertInstanceOf(model::class, $pipeline->getDatasource());
    }

    /**
     * [testPipelineCreateDatasourcePipelineInstance description]
     * @throws ReflectionException
     * @throws exception
     */
    public function testPipelineCreateDatasourcePipelineInstance(): void
    {
        $pipeline = new pipeline(null, [
          'source' => [
            'type' => 'model',
            'config' => [],
          ],
        ]);
        $datasource = $pipeline->createDatasource([]);
        $pipeline->setDatasource($datasource);
        static::assertInstanceOf(model::class, $pipeline->getDatasource());
    }

    /**
     * [testPipelineDatasourceBuffered description]
     * @throws ReflectionException
     * @throws exception
     */
    public function testPipelineDatasourceBuffered(): void
    {
        $pipeline = new pipeline(null, [
          'config' => [
            'datasource_buffering' => true,
            'datasource_buffer_size' => 100,
          ],
        ]);

        $datasource = new arraydata();
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
          ],
        ]);
        $pipeline->setDatasource($datasource);
        static::assertInstanceOf(buffered::class, $pipeline->getDatasource());
    }

    /**
     * [testPipelineDatasource description]
     * @throws ReflectionException
     * @throws exception
     */
    public function testPipelineDatasourceLoad(): void
    {
        $pipeline = new pipeline('tests/testPipeline.json', []);

        $datasource = new arraydata();
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
          ],
        ]);
        $pipeline->setDatasource($datasource);
        static::assertInstanceOf(arraydata::class, $pipeline->getDatasource());

        static::assertEquals(3, $pipeline->getItemCount());
        static::assertEquals(null, $pipeline->getItemIndex());
        static::assertEquals(null, $pipeline->getStoredItemCount());
    }

    /**
     * Test Spec output (simple case)
     * @throws ReflectionException
     * @throws exception
     */
    public function testSpecification(): void
    {
        $pipeline = new pipeline('tests/testPipeline2.json', []);

        static::assertEquals(
            [
              'target.example.example1' => [
                'type' => 'target.mapping',
                'source' => ['transform.example1'],
              ],
              'target.example.example2' => [
                'type' => 'target.mapping',
                'source' => ['transform.example2'],
              ],
              'target.example.example3' => [
                'type' => 'target.mapping',
                'source' => ['transform.example3'],
              ],
              'target.example' => [
                'type' => 'target',
                'source' => ['target.example.example1', 'target.example.example2', 'target.example.example3'],
              ],
              'transform.example1' => [
                'type' => 'transform',
                'source' => [],
              ],
              'transform.example2' => [
                'type' => 'transform',
                'source' => ['model.pipelinemodel'],
              ],
              'transform.example3' => [
                'type' => 'transform',
                'source' => ['source.example3'],
              ],
              'model.pipelinemodel' => [
                'type' => 'model',
              ],
              'source.example3' => [
                'type' => 'source',
              ],
              'erroneous.erroneous' => [
                'type' => 'erroneous',
              ],
              'erroneous.data' => [
                'type' => 'erroneous',
              ],
            ],
            $pipeline->getSpecification(),
            json_encode($pipeline->getSpecification())
        );
    }

    /**
     * {@inheritDoc}
     * @throws ReflectionException
     * @throws exception
     */
    protected function tearDown(): void
    {
        $this->getModel('pipelinemodel')
          ->addFilter('pipelinemodel_id', 0, '>')
          ->delete();

        overrideableApp::__setInstance('response', null);
    }

    /**
     * {@inheritDoc}
     * @throws ReflectionException
     * @throws exception
     */
    protected function setUp(): void
    {
        $app = static::createApp();

        overrideableApp::__injectApp([
          'vendor' => 'codename',
          'app' => 'core-io',
          'namespace' => '\\codename\\core\\io',
        ]);

        // Additional overrides to get a more complete app lifecycle
        // and allow static global app::getModel() to work correctly
        $app::__setApp('pipelinetest');
        $app::__setVendor('codename');
        $app::__setNamespace('\\codename\\core\\io\\tests\\pipeline');

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
                  // 'database_file' => 'pipelinemodel.sqlite',
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
            'pipelinetest',
            'pipelinemodel',
            pipelinemodel::$staticConfig,
            function ($schema, $model, $config) {
                return new pipelinemodel([]);
            }
        );

        static::architect('pipelinetest', 'codename', 'test');
    }
}
