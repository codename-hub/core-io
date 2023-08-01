<?php

namespace codename\core\io\tests\pipeline;

use codename\core\errorstack;
use codename\core\io\datasource\arraydata;
use codename\core\io\pipeline;
use codename\core\transaction;
use Exception;
use ReflectionException;

/**
 * [pipelineModelTargetTest description]
 */
class pipelineModelTargetTest extends abstractPipelineTest
{
    /**
     * [testPipelineAlreadyOpenTransactionWillFail description]
     * @throws ReflectionException
     * @throws \codename\core\exception
     */
    public function testPipelineAlreadyOpenTransactionWillFail(): void
    {
        $model = $this->getModel('pipelinemodel');
        $transaction = new transaction('test', [$model]);
        $transaction->start();

        $failed = false;
        try {
            $this->testPipelineWriteToTargetModel();
        } catch (Exception $e) {
            $failed = true;

            // still complete/end the transaction
            // otherwise other tests might fail
            $transaction->end();

            static::assertEquals('EXCEPTION_PIPELINE_BEGINTRANSACTIONS_ALREADY_ACTIVE_TRANSACTION', $e->getMessage());
        }


        // We expect this to not complete
        if (!$failed) {
            static::fail('Expected exception not thrown - must fail!');
        }
    }

    /**
     * [testPipelineWriteToTargetModel description]
     * @throws ReflectionException
     * @throws \codename\core\exception
     */
    public function testPipelineWriteToTargetModel(): void
    {
        $model = $this->getModel('pipelinemodel');
        static::assertEquals(0, $model->getCount());

        $pipeline = new pipeline(null, [
          'source' => [
            'type' => 'arraydata',
          ],
          'transform' => [

          ],
          'target' => [
            'test_model' => [
              'type' => 'model',
              'model' => 'pipelinemodel',
              'mapping' => [
                'pipelinemodel_text' => ['type' => 'source', 'field' => 'value'],
              ],
            ],
          ],
        ]);

        $datasource = new arraydata();
        $datasource->setData([
          ['value' => 'one'],
          ['value' => 'two'],
          ['value' => 'three'],
        ]);

        $pipeline->setDatasource($datasource);
        $pipeline->setDryRun(false);
        $pipeline->setErrorstackEnabled();
        $pipeline->run();

        static::assertEquals(3, $model->getCount());

        $errorstack = $pipeline->getErrorstack();
        static::assertEmpty($errorstack->getErrors());
        static::assertInstanceOf(errorstack::class, $errorstack);
    }

    /**
     * [testPipelinePreviewRollback description]
     * @throws ReflectionException
     * @throws \codename\core\exception
     */
    public function testPipelinePreviewRollback(): void
    {
        $model = $this->getModel('pipelinemodel');
        static::assertEquals(0, $model->getCount());

        $pipeline = new pipeline(null, [
          'source' => [
            'type' => 'arraydata',
          ],
          'transform' => [

          ],
          'target' => [
            'test_model' => [
              'type' => 'model',
              'model' => 'pipelinemodel',
              'mapping' => [
                'pipelinemodel_text' => ['type' => 'source', 'field' => 'value'],
              ],
            ],
          ],
        ]);

        $datasource = new arraydata();
        $datasource->setData([
          ['value' => 'one'],
          ['value' => 'two'],
          ['value' => 'three'],
        ]);

        $pipeline->setDatasource($datasource);

        // Non-dryrun, but preview (rollback at end)
        $pipeline->setDryRun(false);
        $pipeline->setOptions(['preview' => true]);

        $pipeline->run();

        static::assertEquals(0, $model->getCount());
    }
}
