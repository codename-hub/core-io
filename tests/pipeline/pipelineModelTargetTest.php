<?php
namespace codename\core\tests\pipeline;

/**
 * [pipelineModelTargetTest description]
 */
class pipelineModelTargetTest extends abstractPipelineTest {

  /**
   * [testPipelineWriteToTargetModel description]
   */
  public function testPipelineWriteToTargetModel(): void {

    $model = $this->getModel('pipelinemodel');
    $this->assertEquals(0, $model->getCount());

    $pipeline = new \codename\core\io\pipeline(null, [
      'source' => [
        'type' => 'arraydata'
      ],
      'transform' => [

      ],
      'target' => [
        'test_model' => [
          'type' => 'model',
          'model' => 'pipelinemodel',
          'mapping' => [
            'pipelinemodel_text' => [ 'type' => 'source', 'field' => 'value' ]
          ]
        ]
      ]
    ]);

    $datasource = new \codename\core\io\datasource\arraydata([]);
    $datasource->setData([
      [ 'value' => 'one' ],
      [ 'value' => 'two' ],
      [ 'value' => 'three' ],
    ]);

    $pipeline->setDatasource($datasource);
    $pipeline->setDryRun(false);
    $pipeline->setErrorstackEnabled(true);
    $pipeline->run();

    $this->assertEquals(3, $model->getCount());

    $errorstack = $pipeline->getErrorstack();
    $this->assertEmpty($errorstack->getErrors());
    $this->assertInstanceOf(\codename\core\errorstack::class, $errorstack);
  }

  /**
   * [testPipelineAlreadyOpenTransactionWillFail description]
   */
  public function testPipelineAlreadyOpenTransactionWillFail(): void {
    $model = $this->getModel('pipelinemodel');
    $transaction = new \codename\core\transaction('test', [ $model ]);
    $transaction->start();

    $failed = false;
    try {
      $this->testPipelineWriteToTargetModel();
    } catch (\codename\core\exception $e) {
      $failed = true;
      $this->assertEquals('EXCEPTION_PIPELINE_BEGINTRANSACTIONS_ALREADY_ACTIVE_TRANSACTION', $e->getMessage());
    }

    // still complete/end the transaction
    // otherwise other tests might fail
    $transaction->end();

    // We expect this to not complete
    if(!$failed) {
      $this->fail('Expected exception not thrown - must fail!');
    }
  }

  /**
   * [testPipelinePreviewRollback description]
   */
  public function testPipelinePreviewRollback(): void {

    $model = $this->getModel('pipelinemodel');
    $this->assertEquals(0, $model->getCount());

    $pipeline = new \codename\core\io\pipeline(null, [
      'source' => [
        'type' => 'arraydata'
      ],
      'transform' => [

      ],
      'target' => [
        'test_model' => [
          'type' => 'model',
          'model' => 'pipelinemodel',
          'mapping' => [
            'pipelinemodel_text' => [ 'type' => 'source', 'field' => 'value' ]
          ]
        ]
      ]
    ]);

    $datasource = new \codename\core\io\datasource\arraydata([]);
    $datasource->setData([
      [ 'value' => 'one' ],
      [ 'value' => 'two' ],
      [ 'value' => 'three' ],
    ]);

    $pipeline->setDatasource($datasource);

    // Non-dryrun, but preview (rollback at end)
    $pipeline->setDryRun(false);
    $pipeline->setOptions([ 'preview' => true ]);

    $pipeline->run();

    $this->assertEquals(0, $model->getCount());
  }

}
