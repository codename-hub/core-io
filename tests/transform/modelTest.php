<?php
namespace codename\core\io\tests\transform;

class modelTest extends abstractTransformTest
{
  /**
   * [protected description]
   * @var bool
   */
  protected static $initialized = false;

  /**
   * @inheritDoc
   */
  public static function tearDownAfterClass(): void
  {
    parent::tearDownAfterClass();
    static::$initialized = false;
  }

  /**
   * @inheritDoc
   */
  protected function tearDown(): void
  {
    $this->getModel('transformmodel')
      ->addFilter('transformmodel_id', 0, '>')
      ->delete();
  }

  /**
   * @inheritDoc
   */
  protected function setUp(): void
  {
    $app = static::createApp();

    // Additional overrides to get a more complete app lifecycle
    // and allow static global app::getModel() to work correctly
    $app->__setApp('transformmodeltest');
    $app->__setVendor('codename');
    $app->__setNamespace('\\codename\\core\\io\\tests\\transform');

    $app->getAppstack();

    // avoid re-init
    if(static::$initialized) {
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
            // 'database_file' => 'testmodel.sqlite',
            'database_file' => ':memory:',
          ],
        ],
        'cache' => [
          'default' => [
            'driver' => 'memory'
          ]
        ],
        'filesystem' =>[
          'local' => [
            'driver' => 'local',
          ]
        ],
        'log' => [
          'default' => [
            'driver' => 'system',
            'data' => [
              'name' => 'dummy'
            ]
          ]
        ],
      ]
    ]);

    static::createModel('transformtest', 'transformmodel', [
      'field' => [
        'transformmodel_id',
        'transformmodel_created',
        'transformmodel_modified',
        'transformmodel_text',
        'transformmodel_integer',
      ],
      'primary' => [
        'transformmodel_id'
      ],
      'datatype' => [
        'transformmodel_id'       => 'number_natural',
        'transformmodel_created'  => 'text_timestamp',
        'transformmodel_modified' => 'text_timestamp',
        'transformmodel_text'     => 'text',
        'transformmodel_integer'  => 'number_natural',
      ],
      'connection' => 'default'
    ], function($schema, $model, $config) {
      return new \codename\core\io\tests\transform\model\transformmodel([]);
    });

    static::architect('transformmodeltest', 'codename', 'test');
  }

  /**
   * Creates sample test data (on need!)
   */
  protected function createSampleTestData(): void {
    $datasets = [
      [
        'transformmodel_text'    => 'foo',
        'transformmodel_integer' => 111,
      ],
      [
        'transformmodel_text'    => 'bar',
        'transformmodel_integer' => 222,
      ],
      [
        'transformmodel_text'    => 'baz',
        'transformmodel_integer' => null,
      ],
      [
        'transformmodel_text'    => 'qux',
        'transformmodel_integer' => 333,
      ],
    ];
    $model = $this->getModel('transformmodel');
    foreach ($datasets as $dataset) {
      $model->save($dataset);
    }
  }

  /**
   * Tests model_save in dry-run
   */
  public function testModelSaveDryRun(): void {
    $pseudoPipeline = new \codename\core\io\pipeline(null, []);
    $pseudoPipeline->setDryRun(true);

    $transform = $this->getTransform('model_save', [
      'model' => 'transformmodel',
      'data' => [
        'source'    => 'source',
        'field'     => 'model_data',
      ]
    ]);
    $transform->setPipelineInstance($pseudoPipeline);
    $result = $transform->transform([
      'model_data'  => [
        'transformmodel_text'     => 'abc',
        'transformmodel_integer'  => 123,
      ]
    ]);
    $this->assertEquals('dry-run', $result);
  }

  /**
   * Tests model_save in NON-dry-run (writing data)
   */
  public function testModelSave(): void {
    $pseudoPipeline = new \codename\core\io\pipeline(null, []);
    $pseudoPipeline->setDryRun(false);

    $transform = $this->getTransform('model_save', [
      'model' => 'transformmodel',
      'data' => [
        'source'    => 'source',
        'field'     => 'model_data',
      ]
    ]);
    $transform->setPipelineInstance($pseudoPipeline);
    $result = $transform->transform([
      'model_data'  => [
        'transformmodel_text'     => 'abc',
        'transformmodel_integer'  => 123,
      ]
    ]);
    $this->assertNotEquals('dry-run', $result);
    $this->assertGreaterThan(0, $result);

    $model = $this->getModel('transformmodel');
    $dataset = $model->load($result);
    $this->assertEquals('abc', $dataset['transformmodel_text']);
    $this->assertEquals(123, $dataset['transformmodel_integer']);
  }

  /**
   * Tests model_save with PKey (editing data)
   */
  public function testModelSaveWithPKey(): void {
    $this->createSampleTestData();

    $pseudoPipeline = new \codename\core\io\pipeline(null, []);
    $pseudoPipeline->setDryRun(false);

    $transform = $this->getTransform('model_save', [
      'model' => 'transformmodel',
      'data' => [
        'source'    => 'source',
        'field'     => 'model_data',
      ]
    ]);
    $transform->setPipelineInstance($pseudoPipeline);
    $result = $transform->transform([
      'model_data'  => [
        'transformmodel_id'       => 2,
        'transformmodel_text'     => 'abc',
        'transformmodel_integer'  => 123,
      ]
    ]);

    $this->assertEquals(2, $result, print_r($result, true));

    $dataset = $this->getModel('transformmodel')
      ->hideAllFields()
      ->addField('transformmodel_id')
      ->addField('transformmodel_text')
      ->addField('transformmodel_integer')
      ->load(2);
    $this->assertEquals([
      'transformmodel_id'       => 2,
      'transformmodel_text'     => 'abc',
      'transformmodel_integer'  => 123,
    ], $dataset);
  }

  /**
   * [testModelSaveOnetime description]
   */
  public function testModelSaveOnetime(): void {
    $pseudoPipeline = new \codename\core\io\pipeline(null, []);
    $pseudoPipeline->setDryRun(false);

    $transform = $this->getTransform('model_save_onetime', [
      'model' => 'transformmodel',
      'data' => [
        'source'    => 'source',
        'field'     => 'model_data',
      ]
    ]);
    $transform->setPipelineInstance($pseudoPipeline);
    $result = $transform->transform([
      'model_data'  => [
        'transformmodel_text'     => 'abc',
        'transformmodel_integer'  => 123,
      ]
    ]);
    $this->assertNotEquals('dry-run', $result);
    $this->assertGreaterThan(0, $result);

    $model = $this->getModel('transformmodel');
    $dataset = $model->load($result);
    $this->assertEquals('abc', $dataset['transformmodel_text']);
    $this->assertEquals(123, $dataset['transformmodel_integer']);

    //
    // Reset the same transform
    // and make sure we don't save another entry
    // as this is a onetime transform
    //
    $transform->reset();
    $otherResult = $transform->transform([
      'model_data'  => [
        'transformmodel_text'     => 'new',
        'transformmodel_integer'  => 999,
      ]
    ]);

    $this->assertEquals($result, $otherResult);
  }

  /**
  * [testModelSaveUniqueInvalidConfig description]
  */
  public function testModelSaveUniqueInvalidConfig(): void {
    $this->expectException(\codename\core\exception::class);
    $this->expectExceptionMessage('EXCEPTION_TRANSFORM_MODEL_SAVE_UNIQUE_INVALID');
    $transform = $this->getTransform('model_save_unique', [
      'model' => 'transformmodel',
      'unique_by_fields' => [],
      'data' => [
        'source'    => 'source',
        'field'     => 'model_data',
      ]
    ]);
  }

  /**
   * [testModelSaveUniqueDryRun description]
   */
  public function testModelSaveUniqueDryRun(): void {
    $this->testModelSaveUnique(true);
  }

  /**
   * [testModelSaveUnique description]
   * @param bool $dryRun [description]
   */
  public function testModelSaveUnique($dryRun = false): void {
    $pseudoPipeline = new \codename\core\io\pipeline(null, []);
    $pseudoPipeline->setDryRun($dryRun);

    $transform = $this->getTransform('model_save_unique', [
      'model' => 'transformmodel',
      'unique_by_fields' => ['transformmodel_text'],
      'data' => [
        'source'    => 'source',
        'field'     => 'model_data',
      ]
    ]);
    $transform->setPipelineInstance($pseudoPipeline);
    $result = $transform->transform([
      'model_data'  => [
        'transformmodel_text'     => 'abc',
        'transformmodel_integer'  => 123,
      ]
    ]);

    if(!$dryRun) {
      $this->assertNotEquals('dry-run', $result);
      $this->assertGreaterThan(0, $result);

      $model = $this->getModel('transformmodel');
      $dataset = $model->load($result);
      $this->assertEquals('abc', $dataset['transformmodel_text']);
      $this->assertEquals(123, $dataset['transformmodel_integer']);
    }

    $transform->reset();
    $otherResult = $transform->transform([
      'model_data'  => [
        'transformmodel_text'     => 'abc',
        'transformmodel_integer'  => 999,
      ]
    ]);
    $this->assertEquals($result, $otherResult);

    $transform->reset();
    $otherResult = $transform->transform([
      'model_data'  => [
        'transformmodel_text'     => 'def',
        'transformmodel_integer'  => 234,
      ]
    ]);
    $this->assertNotEquals($result, $otherResult);
  }

  /**
   * [testModelMapSingle description]
   */
  public function testModelMapSingle(): void {
    $this->createSampleTestData();

    //
    // static value / constant
    //
    $transform = $this->getTransform('model_map_single', [
      'model'   => 'transformmodel',
      'map'     => 'transformmodel_integer',
      'filter'  => [
        [ 'field' => 'transformmodel_text', 'operator' => '=', 'value' => 'bar' ]
      ]
    ]);
    $result = $transform->transform([]);
    $this->assertEquals(222, $result);

    //
    // nonexisting, not required
    //
    $transform = $this->getTransform('model_map_single', [
      'model'   => 'transformmodel',
      'map'     => 'transformmodel_integer',
      'filter'  => [
        [ 'field' => 'transformmodel_text', 'operator' => '=', 'value' => 'nonexisting' ]
      ]
    ]);
    $result = $transform->transform([]);
    $this->assertEquals(null, $result);
    $this->assertEmpty($transform->getErrors());

    //
    // nonexisting, required
    //
    $transform = $this->getTransform('model_map_single', [
      'model'   => 'transformmodel',
      'required'=> true,
      'map'     => 'transformmodel_integer',
      'filter'  => [
        [ 'field' => 'transformmodel_text', 'operator' => '=', 'value' => 'nonexisting' ]
      ]
    ]);
    $result = $transform->transform([]);
    $this->assertEquals(null, $result);
    $this->assertNotEmpty($transform->getErrors());
  }

  /**
   * [testModelMapSingleOnetime description]
   */
  public function testModelMapSingleOnetime(): void {
    $this->createSampleTestData();

    //
    // static value / constant
    //
    $transform = $this->getTransform('model_map_single_onetime', [
      'model'   => 'transformmodel',
      'map'     => 'transformmodel_integer',
      'filter'  => [
        [ 'field' => 'transformmodel_text', 'operator' => '=', 'value' => 'bar' ]
      ]
    ]);
    $result = $transform->transform([]);
    $this->assertEquals(222, $result);

    $transform->reset();

    $otherResult = $transform->transform([]);
    $this->assertEquals($result, $otherResult);

  }

  /**
   * [testModelResultAll description]
   */
  public function testModelResultAll(): void {
    $this->createSampleTestData();

    //
    // static value / constant
    //
    $transform = $this->getTransform('model_result_all', [
      'model'   => 'transformmodel',
      'filter'  => [
        [ 'field' => 'transformmodel_text', 'operator' => '=', 'value' => ['foo', 'qux'] ]
      ]
    ]);
    $result = $transform->transform([]);
    $this->assertCount(2, $result);

    //
    // static value / constant with grouping
    //
    $transform = $this->getTransform('model_result_all', [
      'model'   => 'transformmodel',
      'filter'  => [
        [ 'field' => 'transformmodel_text', 'operator' => '=', 'value' => ['foo', 'qux'] ]
      ],
      'group'   => [
        'transformmodel_id'
      ]
    ]);
    $result = $transform->transform([]);
    $this->assertCount(2, $result);

    //
    // static value / constant with calculated_fields and aggregate_filter
    //
    $transform = $this->getTransform('model_result_all', [
      'model'             => 'transformmodel',
      'filter'            => [
        [ 'field' => 'transformmodel_integer', 'operator' => '!=', 'value' => null ]
      ],
      'filtercollection'  => [
        'example' => [
          'filters'         => [
            [ 'field' => 'transformmodel_integer', 'operator' => '=', 'value' => 111 ],
            [ 'field' => 'transformmodel_integer', 'operator' => '=', 'value' => [ 'source' => 'source', 'field' => 'source_key1' ] ]
          ],
          'group_operator'  => 'AND',
          'conjunction'     => 'AND',
        ],
      ],
      'calculated_fields' => [
        [ 'field' => 'textIntegerOne', 'calculation' => 'SUM(transformmodel_integer)' ],
        [ 'field' => 'textIntegerTwo', 'calculation' => 'SUM(transformmodel_integer)' ]
      ],
      'aggregate_filter' => [
        [ 'field' => 'textIntegerOne', 'operator' => '=', 'value' => 111 ],
        [ 'field' => 'textIntegerTwo', 'operator' => '=', 'value' => [ 'source' => 'source', 'field' => 'source_key1' ] ]
      ],
      'group'   => [
        'transformmodel_id'
      ]
    ]);
    $result = $transform->transform([
      'source_key1' => 111
    ]);

    $this->assertCount(1, $result);
    $this->assertEquals(111, $result[0]['textIntegerOne']);
    $this->assertEquals(111, $result[0]['textIntegerTwo']);

    //
    // static value / constant, no matches
    //
    $transform = $this->getTransform('model_result_all', [
      'required' => true,
      'model'   => 'transformmodel',
      'filter'  => [
        [ 'field' => 'transformmodel_text', 'operator' => '=', 'value' => 'bla' ]
      ]
    ]);
    $result = $transform->transform([]);
    $this->assertIsArray($result);
    $this->assertCount(0, $result);

    // model_result_all does NOT track any errors
    // related to the result and requiredness
    $this->assertEmpty($transform->getErrors());
  }

  /**
   * [testModelResultAllOnetime description]
   */
  public function testModelResultAllOnetime(): void {
    $this->createSampleTestData();

    $transform = $this->getTransform('model_result_all_onetime', [
      'model'   => 'transformmodel',
      'filter'  => [
        [
          'field' => 'transformmodel_text',
          'operator' => '=',
          'value' => [
            'source' => 'source',
            'field' => 'source_key1'
          ]
        ]
      ]
    ]);
    $result = $transform->transform([
      'source_key1' => ['foo', 'bar']
    ]);
    $this->assertCount(2, $result);

    // reset the transform
    // this should not clear its internal cache
    // as this is a onetime transform
    $transform->reset();

    $otherResult = $transform->transform([
      'source_key1' => ['baz', 'qux']
    ]);
    $this->assertEquals($result, $otherResult);
  }

  /**
   * [testModelResultOne description]
   */
  public function testModelResultOne(): void {
    $this->createSampleTestData();

    //
    // static value / constant
    //
    $transform = $this->getTransform('model_result_one', [
      'model'   => 'transformmodel',
      'filter'  => [
        [ 'field' => 'transformmodel_text', 'operator' => '=', 'value' => 'qux' ]
      ]
    ]);
    $result = $transform->transform([]);
    $this->assertEquals(333, $result['transformmodel_integer']);

    //
    // static value / constant, ambiguous
    //
    $transform = $this->getTransform('model_result_one', [
      'model'   => 'transformmodel',
      'filter'  => [
        [ 'field' => 'transformmodel_text', 'operator' => '=', 'value' => ['qux', 'foo'] ]
      ]
    ]);
    $result = $transform->transform([]);
    $this->assertEquals(null, $result, 'Expect null result on ambiguous result');

    //
    // static value / constant, nonexisting
    //
    $transform = $this->getTransform('model_result_one', [
      'model'   => 'transformmodel',
      'filter'  => [
        [ 'field' => 'transformmodel_text', 'operator' => '=', 'value' => 'bla' ]
      ]
    ]);
    $result = $transform->transform([]);
    $this->assertEquals(null, $result, 'Expect null result on nonexisting result');

    //
    // Required flag testing
    //
    $transform = $this->getTransform('model_result_one', [
      'required' => true,
      'model'   => 'transformmodel',
      'filter'  => [
        [ 'field' => 'transformmodel_text', 'operator' => '=', 'value' => 'bla' ]
      ]
    ]);
    $result = $transform->transform([]);
    $this->assertEquals(null, $result, 'Expect null result on nonexisting result');
    $this->assertNotEmpty($transform->getErrors());

    //
    // dynamic value / constant
    //
    $transform = $this->getTransform('model_result_one', [
      'model'   => 'transformmodel',
      'filter'  => [
        [
          'field' => 'transformmodel_text',
          'operator' => '=',
          'value' => [
            'source' => 'source',
            'field' => 'source_key1'
          ]
        ]
      ]
    ]);
    $result = $transform->transform([
      'source_key1' => 'foo'
    ]);
    $this->assertEquals(111, $result['transformmodel_integer']);
  }

  /**
   * [testModelResultOneOnetime description]
   */
  public function testModelResultOneOnetime(): void {
    $this->createSampleTestData();

    $transform = $this->getTransform('model_result_one_onetime', [
      'model'   => 'transformmodel',
      'filter'  => [
        [
          'field' => 'transformmodel_text',
          'operator' => '=',
          'value' => [
            'source' => 'source',
            'field' => 'source_key1'
          ]
        ]
      ]
    ]);
    $result = $transform->transform([
      'source_key1' => 'foo'
    ]);
    $this->assertEquals(111, $result['transformmodel_integer']);

    // 'reset' data - must not change
    // as this is a onetime transform
    $transform->reset();

    $result = $transform->transform([
      'source_key1' => 'qux'
    ]);
    $this->assertEquals(111, $result['transformmodel_integer']);
  }

}
