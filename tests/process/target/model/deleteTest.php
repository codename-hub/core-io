<?php
namespace codename\core\io\tests\process\target\model;

/**
 * [deleteTest description]
 */
class deleteTest extends abstractProcessTargetModelTest {

  /**
   * [testProcessDeleteWithoutFiltersFails description]
   */
  public function testProcessDeleteWithConstantValueFilter(): void {
    $model = $this->getModel('processmodel');
    $this->createTestData();

    // make sure the data is there
    $this->assertEquals(3, $model->getCount());

    $pipeline = new \codename\core\io\pipeline(null, [
      'preprocess' => [
        'delete_with_constant_filter' => [
          'type' => 'target_model_delete',
          'config' => [
            'target' => 'test',
            'filter' => [
              [ 'field' => 'processmodel_text', 'operator' => '=', 'value' => 'second' ] // Constant value
            ]
          ]
        ]
      ],
      'source' => [
        'type' => 'arraydata'
      ],
      'transform' => [],
      'target' => [
        'test' => [
          'type'  => 'model',
          'model' => 'processmodel',
        ]
      ]
    ]);

    $datasource = new \codename\core\io\datasource\arraydata();
    $datasource->setData([]);
    $pipeline->setDatasource($datasource);

    // this might/should fail?
    $pipeline->setDryRun(false);
    $pipeline->run();

    $this->assertEquals(2, $model->getCount());
  }

  /**
   * [testProcessDeleteWithTransformValueFilter description]
   */
  public function testProcessDeleteWithTransformValueFilter(): void {
    $model = $this->getModel('processmodel');
    $this->createTestData();

    // make sure the data is there
    $this->assertEquals(3, $model->getCount());

    $pipeline = new \codename\core\io\pipeline(null, [
      'preprocess' => [
        'delete_with_transform_filter' => [
          'type' => 'target_model_delete',
          'config' => [
            'target' => 'test',
            'filter' => [
              [ 'field' => 'processmodel_text', 'operator' => '=', 'value' => [ 'source' => 'transform', 'field' => 'first' ] ] // transform value
            ]
          ]
        ]
      ],
      'source' => [
        'type' => 'arraydata'
      ],
      'transform' => [
        'first' => [
          'type'    => 'value',
          'config'  => [
            'value' => 'first'
          ],
        ],
      ],
      'target' => [
        'test' => [
          'type'  => 'model',
          'model' => 'processmodel',
        ]
      ]
    ]);

    $datasource = new \codename\core\io\datasource\arraydata();
    $datasource->setData([]);
    $pipeline->setDatasource($datasource);

    // this might/should fail?
    $pipeline->setDryRun(false);
    $pipeline->run();

    $this->assertEquals(2, $model->getCount());
  }

  /**
   * [testProcessDeleteWithUnsupportedTarget description]
   */
  public function testProcessDeleteWithUnsupportedTarget(): void {
    $this->expectException(\codename\core\exception::class);
    $this->expectExceptionMessage('EXCEPTION_CORE_IO_PROCESS_TARGET_MODEL_UNSUPPORTED');

    $pipeline = new \codename\core\io\pipeline(null, [
      'preprocess' => [
        'delete_with_transform_filter' => [
          'type' => 'target_model_delete',
          'config' => [
            'target' => 'test',
            'filter' => [
              [ 'field' => 'processmodel_text', 'operator' => '=', 'value' => [ 'source' => 'transform', 'field' => 'first' ] ] // transform value
            ]
          ]
        ]
      ],
      'source' => [
        'type' => 'arraydata'
      ],
      'transform' => [
        'first' => [
          'type'    => 'value',
          'config'  => [
            'value' => 'first'
          ],
        ],
      ],
      'target' => [
        'test' => [
          'type'    => 'arraydata',
          'mapping' => []
        ]
      ]
    ]);

    $datasource = new \codename\core\io\datasource\arraydata();
    $datasource->setData([]);
    $pipeline->setDatasource($datasource);

    // this might/should fail?
    $pipeline->setDryRun(false);
    $pipeline->run();
  }

  /**
   * Model-Deletion calls without any filters
   * must fail due to model mechanics
   */
  public function testProcessDeleteWithoutFiltersFails(): void {
    $model = $this->getModel('processmodel');
    $this->createTestData();

    // make sure the data is there
    $this->assertEquals(3, $model->getCount());

    // Must fail, due to model mechanics
    $this->expectException(\codename\core\exception::class);
    $this->expectExceptionMessage('EXCEPTION_MODEL_SCHEMATIC_SQL_DELETE_NO_FILTERS_DEFINED');

    $pipeline = new \codename\core\io\pipeline(null, [
      'preprocess' => [
        'delete_unfiltered' => [
          'type' => 'target_model_delete',
          'config' => [
            'target' => 'test',
            'filter' => [], // NOTE: TODO: NO filter-key must fail?
          ]
        ]
      ],
      'transform' => [],
      'target' => [
        'test' => [
          'type'  => 'model',
          'model' => 'processmodel',
        ]
      ]
    ]);

    // this might/should fail?
    $pipeline->setDryRun(false);
    $pipeline->run();
  }

  /**
   * [testErrorsAndDryRun description]
   */
  public function testErrorsAndDryRun(): void {
    $process = new helper_process_target_model_delete([]);

    $process->resetErrors();
    $this->assertEmpty($process->getErrors());

    $pipeline = new \codename\core\io\pipeline(null, []);
    $pipeline->setDryRun(true);

    $process->setPipelineInstance($pipeline);
    $this->assertTrue($process->isDryRun());

  }

  /**
   * Test Spec output (simple case)
   */
  public function testSpecification(): void {
    $this->expectException(\LogicException::class);
    $this->expectExceptionMessage('Not implemented');

    $process = new \codename\core\io\process\target\model\delete([]);
    $specification = $process->getSpecification();
  }

}

class helper_process_target_model_delete extends \codename\core\io\process\target\model\delete {

  public function isDryRun() : bool {
    return parent::isDryRun();
  }

}
