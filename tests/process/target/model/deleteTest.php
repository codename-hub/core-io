<?php

namespace codename\core\io\tests\process\target\model;

use codename\core\exception;
use codename\core\io\datasource\arraydata;
use codename\core\io\pipeline;
use codename\core\io\process\target\model\delete;
use LogicException;
use ReflectionException;

/**
 * [deleteTest description]
 */
class deleteTest extends abstractProcessTargetModelTest
{
    /**
     * [testProcessDeleteWithoutFiltersFails description]
     * @throws ReflectionException
     * @throws exception
     */
    public function testProcessDeleteWithConstantValueFilter(): void
    {
        $model = $this->getModel('processmodel');
        $this->createTestData();

        // make sure the data is there
        static::assertEquals(3, $model->getCount());

        $pipeline = new pipeline(null, [
          'preprocess' => [
            'delete_with_constant_filter' => [
              'type' => 'target_model_delete',
              'config' => [
                'target' => 'test',
                'filter' => [
                  ['field' => 'processmodel_text', 'operator' => '=', 'value' => 'second'], // Constant value
                ],
              ],
            ],
          ],
          'source' => [
            'type' => 'arraydata',
          ],
          'transform' => [],
          'target' => [
            'test' => [
              'type' => 'model',
              'model' => 'processmodel',
            ],
          ],
        ]);

        $datasource = new arraydata();
        $datasource->setData([]);
        $pipeline->setDatasource($datasource);

        // this might/should fail?
        $pipeline->setDryRun(false);
        $pipeline->run();

        static::assertEquals(2, $model->getCount());
    }

    /**
     * [testProcessDeleteWithTransformValueFilter description]
     * @throws ReflectionException
     * @throws exception
     */
    public function testProcessDeleteWithTransformValueFilter(): void
    {
        $model = $this->getModel('processmodel');
        $this->createTestData();

        // make sure the data is there
        static::assertEquals(3, $model->getCount());

        $pipeline = new pipeline(null, [
          'preprocess' => [
            'delete_with_transform_filter' => [
              'type' => 'target_model_delete',
              'config' => [
                'target' => 'test',
                'filter' => [
                  ['field' => 'processmodel_text', 'operator' => '=', 'value' => ['source' => 'transform', 'field' => 'first']], // transform value
                ],
              ],
            ],
          ],
          'source' => [
            'type' => 'arraydata',
          ],
          'transform' => [
            'first' => [
              'type' => 'value',
              'config' => [
                'value' => 'first',
              ],
            ],
          ],
          'target' => [
            'test' => [
              'type' => 'model',
              'model' => 'processmodel',
            ],
          ],
        ]);

        $datasource = new arraydata();
        $datasource->setData([]);
        $pipeline->setDatasource($datasource);

        // this might/should fail?
        $pipeline->setDryRun(false);
        $pipeline->run();

        static::assertEquals(2, $model->getCount());
    }

    /**
     * [testProcessDeleteWithUnsupportedTarget description]
     * @throws ReflectionException
     * @throws exception
     */
    public function testProcessDeleteWithUnsupportedTarget(): void
    {
        $this->expectException(exception::class);
        $this->expectExceptionMessage('EXCEPTION_CORE_IO_PROCESS_TARGET_MODEL_UNSUPPORTED');

        $pipeline = new pipeline(null, [
          'preprocess' => [
            'delete_with_transform_filter' => [
              'type' => 'target_model_delete',
              'config' => [
                'target' => 'test',
                'filter' => [
                  ['field' => 'processmodel_text', 'operator' => '=', 'value' => ['source' => 'transform', 'field' => 'first']], // transform value
                ],
              ],
            ],
          ],
          'source' => [
            'type' => 'arraydata',
          ],
          'transform' => [
            'first' => [
              'type' => 'value',
              'config' => [
                'value' => 'first',
              ],
            ],
          ],
          'target' => [
            'test' => [
              'type' => 'arraydata',
              'mapping' => [],
            ],
          ],
        ]);

        $datasource = new arraydata();
        $datasource->setData([]);
        $pipeline->setDatasource($datasource);

        // this might/should fail?
        $pipeline->setDryRun(false);
        $pipeline->run();
    }

    /**
     * Model-Deletion calls without any filters
     * must fail due to model mechanics
     * @throws ReflectionException
     * @throws exception
     */
    public function testProcessDeleteWithoutFiltersFails(): void
    {
        $model = $this->getModel('processmodel');
        $this->createTestData();

        // make sure the data is there
        static::assertEquals(3, $model->getCount());

        // Must fail, due to model mechanics
        $this->expectException(exception::class);
        $this->expectExceptionMessage('EXCEPTION_MODEL_SCHEMATIC_SQL_DELETE_NO_FILTERS_DEFINED');

        $pipeline = new pipeline(null, [
          'preprocess' => [
            'delete_unfiltered' => [
              'type' => 'target_model_delete',
              'config' => [
                'target' => 'test',
                'filter' => [], // NOTE: TODO: NO filter-key must fail?
              ],
            ],
          ],
          'transform' => [],
          'target' => [
            'test' => [
              'type' => 'model',
              'model' => 'processmodel',
            ],
          ],
        ]);

        // this might/should fail?
        $pipeline->setDryRun(false);
        $pipeline->run();
    }

    /**
     * [testErrorsAndDryRun description]
     * @throws ReflectionException
     * @throws exception
     */
    public function testErrorsAndDryRun(): void
    {
        $process = new helper_process_target_model_delete([]);

        $process->resetErrors();
        static::assertEmpty($process->getErrors());

        $pipeline = new pipeline(null, []);
        $pipeline->setDryRun();

        $process->setPipelineInstance($pipeline);
        static::assertTrue($process->isDryRun());
    }

    /**
     * Test Spec output (simple case)
     */
    public function testSpecification(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Not implemented');

        $process = new delete([]);
        $process->getSpecification();
    }
}

class helper_process_target_model_delete extends delete
{
    /**
     * @return bool
     */
    public function isDryRun(): bool
    {
        return parent::isDryRun();
    }
}
