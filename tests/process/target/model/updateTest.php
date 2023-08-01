<?php

namespace codename\core\io\tests\process\target\model;

use codename\core\exception;
use codename\core\io\datasource\arraydata;
use codename\core\io\pipeline;
use codename\core\io\process\target\model\update;
use LogicException;
use ReflectionException;

/**
 * [updateTest description]
 */
class updateTest extends abstractProcessTargetModelTest
{
    /**
     * [testProcessUpdateWithConstantValueFilterAndConstantData description]
     * @throws ReflectionException
     * @throws exception
     */
    public function testProcessUpdateWithConstantValueFilterAndConstantData(): void
    {
        $model = $this->getModel('processmodel');
        $this->createTestData();

        // make sure the data is there
        static::assertEquals(3, $model->getCount());

        $pipeline = new pipeline(null, [
          'preprocess' => [
            'update_with_constant_filter' => [
              'type' => 'target_model_update',
              'config' => [
                'target' => 'test',
                'filter' => [
                  ['field' => 'processmodel_text', 'operator' => '=', 'value' => 'second'], // Constant value
                ],
                'data' => [
                  'processmodel_text' => 'updated',
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

        $res = $model->addFilter('processmodel_text', 'updated')->search()->getResult();
        static::assertCount(1, $res);
    }

    /**
     * [testProcessUpdateWithConstantValueFilterAndOptionData description]
     * @throws ReflectionException
     * @throws exception
     */
    public function testProcessUpdateWithConstantValueFilterAndOptionData(): void
    {
        $model = $this->getModel('processmodel');
        $this->createTestData();

        // make sure the data is there
        static::assertEquals(3, $model->getCount());

        $pipeline = new pipeline(null, [
          'preprocess' => [
            'update_with_constant_filter' => [
              'type' => 'target_model_update',
              'config' => [
                'target' => 'test',
                'filter' => [
                  ['field' => 'processmodel_text', 'operator' => '=', 'value' => 'second'], // Constant value
                ],
                'data' => [
                  'processmodel_text' => ['source' => 'option', 'field' => 'updated'],
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
        $pipeline->setOptions([
          'updated' => 'updated',
        ]);

        $datasource = new arraydata();
        $datasource->setData([]);
        $pipeline->setDatasource($datasource);

        // this might/should fail?
        $pipeline->setDryRun(false);
        $pipeline->run();

        $res = $model->addFilter('processmodel_text', 'updated')->search()->getResult();
        static::assertCount(1, $res);
    }

    /**
     * [testProcessUpdateWithTransformValueFilterAndTransformData description]
     * @throws ReflectionException
     * @throws exception
     */
    public function testProcessUpdateWithTransformValueFilterAndTransformData(): void
    {
        $model = $this->getModel('processmodel');
        $this->createTestData();

        // make sure the data is there
        static::assertEquals(3, $model->getCount());

        $pipeline = new pipeline(null, [
          'preprocess' => [
            'update_with_transform_filter' => [
              'type' => 'target_model_update',
              'config' => [
                'target' => 'test',
                'filter' => [
                  ['field' => 'processmodel_text', 'operator' => '=', 'value' => ['source' => 'transform', 'field' => 'first']], // transform value
                ],
                'data' => [
                  'processmodel_text' => ['source' => 'transform', 'field' => 'updated'],
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
            'updated' => [
              'type' => 'value',
              'config' => [
                'value' => 'updated',
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

        $res = $model->addFilter('processmodel_text', 'updated')->search()->getResult();
        static::assertCount(1, $res);
    }

    /**
     * [testProcessUpdateWithUnsupportedTarget description]
     * @throws ReflectionException
     * @throws exception
     */
    public function testProcessUpdateWithUnsupportedTarget(): void
    {
        $this->expectException(exception::class);
        $this->expectExceptionMessage('EXCEPTION_CORE_IO_PROCESS_TARGET_MODEL_UNSUPPORTED');

        $pipeline = new pipeline(null, [
          'preprocess' => [
            'update_with_transform_filter' => [
              'type' => 'target_model_update',
              'config' => [
                'target' => 'test',
                'filter' => [
                  ['field' => 'processmodel_text', 'operator' => '=', 'value' => ['source' => 'transform', 'field' => 'first']], // transform value
                ],
                'data' => [
                  'processmodel_text' => ['source' => 'transform', 'field' => 'updated'],
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
            'updated' => [
              'type' => 'value',
              'config' => [
                'value' => 'updated',
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
    public function testProcessUpdateWithoutFiltersFails(): void
    {
        $model = $this->getModel('processmodel');
        $this->createTestData();

        // make sure the data is there
        static::assertEquals(3, $model->getCount());

        // Must fail, due to model mechanics
        $this->expectException(exception::class);
        $this->expectExceptionMessage('EXCEPTION_MODEL_SCHEMATIC_SQL_UPDATE_NO_FILTERS_DEFINED');

        $pipeline = new pipeline(null, [
          'preprocess' => [
            'update_unfiltered' => [
              'type' => 'target_model_update',
              'config' => [
                'target' => 'test',
                'filter' => [], // NOTE: TODO: NO filter-key must fail?
                'data' => [
                  'processmodel_text' => 'updated',
                ],
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
        $process = new helper_process_target_model_update([]);

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

        $process = new update([]);
        $process->getSpecification();
    }
}

class helper_process_target_model_update extends update
{
    /**
     * [isDryRun description]
     * @return bool [description]
     */
    public function isDryRun(): bool
    {
        return parent::isDryRun();
    }
}
