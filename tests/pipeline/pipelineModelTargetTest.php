<?php
namespace codename\core\tests\pipeline;

use codename\core\tests\base;
use codename\core\tests\overrideableApp;

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
    $pipeline->run();

    $this->assertEquals(3, $model->getCount());
  }

}
