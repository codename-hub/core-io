<?php
namespace codename\core\tests\process\target\model;

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

}
