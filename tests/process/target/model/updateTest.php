<?php
namespace codename\core\tests\process\target\model;

/**
 * [updateTest description]
 */
class updateTest extends abstractProcessTargetModelTest {

  /**
   * [testProcessDeleteWithoutFiltersFails description]
   */
  public function testProcessUpdateWithConstantValueFilterAndConstantData(): void {
    $model = $this->getModel('processmodel');
    $this->createTestData();

    // make sure the data is there
    $this->assertEquals(3, $model->getCount());

    $pipeline = new \codename\core\io\pipeline(null, [
      'preprocess' => [
        'update_with_constant_filter' => [
          'type' => 'target_model_update',
          'config' => [
            'target' => 'test',
            'filter' => [
              [ 'field' => 'processmodel_text', 'operator' => '=', 'value' => 'second' ] // Constant value
            ],
            'data' => [
              'processmodel_text' => 'updated'
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

    $res = $model->addFilter('processmodel_text', 'updated')->search()->getResult();
    $this->assertCount(1, $res);
  }

  /**
   * Model-Deletion calls without any filters
   * must fail due to model mechanics
   */
  public function testProcessUpdateWithoutFiltersFails(): void {
    $model = $this->getModel('processmodel');
    $this->createTestData();

    // make sure the data is there
    $this->assertEquals(3, $model->getCount());

    // Must fail, due to model mechanics
    $this->expectException(\codename\core\exception::class);
    $this->expectExceptionMessage('EXCEPTION_MODEL_SCHEMATIC_SQL_UPDATE_NO_FILTERS_DEFINED');

    $pipeline = new \codename\core\io\pipeline(null, [
      'preprocess' => [
        'update_unfiltered' => [
          'type' => 'target_model_update',
          'config' => [
            'target' => 'test',
            'filter' => [], // NOTE: TODO: NO filter-key must fail?
            'data' => [
              'processmodel_text' => 'updated'
            ]
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
