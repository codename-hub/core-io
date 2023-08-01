<?php

namespace codename\core\io\tests\transform;

use codename\core\exception;
use codename\core\io\pipeline;
use codename\core\io\tests\transform\model\tjsample;
use codename\core\io\tests\transform\model\transformmodel;
use codename\core\test\overrideableApp;
use ReflectionException;

class modelTest extends abstractTransformTest
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
     * Tests model_save in dry-run
     * @throws ReflectionException
     * @throws exception
     */
    public function testModelSaveDryRun(): void
    {
        $pseudoPipeline = new pipeline(null, []);
        $pseudoPipeline->setDryRun();

        $transform = $this->getTransform('model_save', [
          'model' => 'transformmodel',
          'data' => [
            'source' => 'source',
            'field' => 'model_data',
          ],
        ]);
        $transform->setPipelineInstance($pseudoPipeline);
        $result = $transform->transform([
          'model_data' => [
            'transformmodel_text' => 'abc',
            'transformmodel_integer' => 123,
          ],
        ]);
        static::assertEquals('dry-run', $result);
    }

    /**
     * Tests model_save in NON-dry-run (writing data)
     * @throws ReflectionException
     * @throws exception
     */
    public function testModelSave(): void
    {
        $pseudoPipeline = new pipeline(null, []);
        $pseudoPipeline->setDryRun(false);

        $transform = $this->getTransform('model_save', [
          'model' => 'transformmodel',
          'data' => [
            'source' => 'source',
            'field' => 'model_data',
          ],
        ]);
        $transform->setPipelineInstance($pseudoPipeline);
        $result = $transform->transform([
          'model_data' => [
            'transformmodel_text' => 'abc',
            'transformmodel_integer' => 123,
          ],
        ]);
        static::assertNotEquals('dry-run', $result);
        static::assertGreaterThan(0, $result);

        $model = $this->getModel('transformmodel');
        $dataset = $model->load($result);
        static::assertEquals('abc', $dataset['transformmodel_text']);
        static::assertEquals(123, $dataset['transformmodel_integer']);
    }

    /**
     * Tests model_save with PKey (editing data)
     * @throws ReflectionException
     * @throws exception
     */
    public function testModelSaveWithPKey(): void
    {
        $this->createSampleTestData();

        // Pre-define target id to operate on.
        $targetId = $this->getModel('transformmodel')
          ->addFilter('transformmodel_text', 'bar')
          ->search()->getResult()[0]['transformmodel_id'];
        static::assertNotNull($targetId);

        $pseudoPipeline = new pipeline(null, []);
        $pseudoPipeline->setDryRun(false);

        $transform = $this->getTransform('model_save', [
          'model' => 'transformmodel',
          'data' => [
            'source' => 'source',
            'field' => 'model_data',
          ],
        ]);
        $transform->setPipelineInstance($pseudoPipeline);
        $result = $transform->transform([
          'model_data' => [
            'transformmodel_id' => $targetId,
            'transformmodel_text' => 'abc',
            'transformmodel_integer' => 123,
          ],
        ]);

        static::assertEquals($targetId, $result, print_r($result, true));

        $dataset = $this->getModel('transformmodel')
          ->hideAllFields()
          ->addField('transformmodel_id')
          ->addField('transformmodel_text')
          ->addField('transformmodel_integer')
          ->load($targetId);
        static::assertEquals([
          'transformmodel_id' => $targetId,
          'transformmodel_text' => 'abc',
          'transformmodel_integer' => 123,
        ], $dataset);
    }

    /**
     * Creates sample test data (on need!)
     * @throws ReflectionException
     * @throws exception
     */
    protected function createSampleTestData(): void
    {
        $datasets = [
          [
            'transformmodel_text' => 'foo',
            'transformmodel_integer' => 111,
          ],
          [
            'transformmodel_text' => 'bar',
            'transformmodel_integer' => 222,
          ],
          [
            'transformmodel_text' => 'baz',
            'transformmodel_integer' => null,
          ],
          [
            'transformmodel_text' => 'qux',
            'transformmodel_integer' => 333,
          ],
        ];
        $model = $this->getModel('transformmodel');
        foreach ($datasets as $dataset) {
            $model->save($dataset);
        }
    }

    /**
     * [testModelSaveOnetime description]
     * @throws ReflectionException
     * @throws exception
     */
    public function testModelSaveOnetime(): void
    {
        $pseudoPipeline = new pipeline(null, []);
        $pseudoPipeline->setDryRun(false);

        $transform = $this->getTransform('model_save_onetime', [
          'model' => 'transformmodel',
          'data' => [
            'source' => 'source',
            'field' => 'model_data',
          ],
        ]);
        $transform->setPipelineInstance($pseudoPipeline);
        $result = $transform->transform([
          'model_data' => [
            'transformmodel_text' => 'abc',
            'transformmodel_integer' => 123,
          ],
        ]);
        static::assertNotEquals('dry-run', $result);
        static::assertGreaterThan(0, $result);

        $model = $this->getModel('transformmodel');
        $dataset = $model->load($result);
        static::assertEquals('abc', $dataset['transformmodel_text']);
        static::assertEquals(123, $dataset['transformmodel_integer']);

        //
        // Reset the same transform
        // and make sure we don't save another entry
        // as this is a onetime transform
        //
        $transform->reset();
        $otherResult = $transform->transform([
          'model_data' => [
            'transformmodel_text' => 'new',
            'transformmodel_integer' => 999,
          ],
        ]);

        static::assertEquals($result, $otherResult);
    }

    /**
     * [testModelSaveUniqueInvalidConfig description]
     * @throws ReflectionException
     * @throws exception
     */
    public function testModelSaveUniqueInvalidConfig(): void
    {
        $this->expectException(exception::class);
        $this->expectExceptionMessage('EXCEPTION_TRANSFORM_MODEL_SAVE_UNIQUE_INVALID');
        $this->getTransform('model_save_unique', [
          'model' => 'transformmodel',
          'unique_by_fields' => [],
          'data' => [
            'source' => 'source',
            'field' => 'model_data',
          ],
        ]);
    }

    /**
     * [testModelSaveUniqueDryRun description]
     * @throws ReflectionException
     * @throws exception
     */
    public function testModelSaveUniqueDryRun(): void
    {
        $this->testModelSaveUnique(true);
    }

    /**
     * [testModelSaveUnique description]
     * @param bool $dryRun [description]
     * @throws ReflectionException
     * @throws exception
     */
    public function testModelSaveUnique(bool $dryRun = false): void
    {
        $pseudoPipeline = new pipeline(null, []);
        $pseudoPipeline->setDryRun($dryRun);

        $transform = $this->getTransform('model_save_unique', [
          'model' => 'transformmodel',
          'unique_by_fields' => ['transformmodel_text'],
          'data' => [
            'source' => 'source',
            'field' => 'model_data',
          ],
        ]);
        $transform->setPipelineInstance($pseudoPipeline);
        $result = $transform->transform([
          'model_data' => [
            'transformmodel_text' => 'abc',
            'transformmodel_integer' => 123,
          ],
        ]);

        if (!$dryRun) {
            static::assertNotEquals('dry-run', $result);
            static::assertGreaterThan(0, $result);

            $model = $this->getModel('transformmodel');
            $dataset = $model->load($result);
            static::assertEquals('abc', $dataset['transformmodel_text']);
            static::assertEquals(123, $dataset['transformmodel_integer']);
        }

        $transform->reset();
        $otherResult = $transform->transform([
          'model_data' => [
            'transformmodel_text' => 'abc',
            'transformmodel_integer' => 999,
          ],
        ]);
        static::assertEquals($result, $otherResult);

        $transform->reset();
        $otherResult = $transform->transform([
          'model_data' => [
            'transformmodel_text' => 'def',
            'transformmodel_integer' => 234,
          ],
        ]);
        static::assertNotEquals($result, $otherResult);
    }

    /**
     * [testModelMapSingle description]
     * @throws ReflectionException
     * @throws exception
     */
    public function testModelMapSingle(): void
    {
        $this->createSampleTestData();

        //
        // static value / constant
        //
        $transform = $this->getTransform('model_map_single', [
          'model' => 'transformmodel',
          'map' => 'transformmodel_integer',
          'filter' => [
            ['field' => 'transformmodel_text', 'operator' => '=', 'value' => 'bar'],
          ],
        ]);
        $result = $transform->transform([]);
        static::assertEquals(222, $result);

        //
        // nonexisting, not required
        //
        $transform = $this->getTransform('model_map_single', [
          'model' => 'transformmodel',
          'map' => 'transformmodel_integer',
          'filter' => [
            ['field' => 'transformmodel_text', 'operator' => '=', 'value' => 'nonexisting'],
          ],
        ]);
        $result = $transform->transform([]);
        static::assertEquals(null, $result);
        static::assertEmpty($transform->getErrors());

        //
        // nonexisting, required
        //
        $transform = $this->getTransform('model_map_single', [
          'model' => 'transformmodel',
          'required' => true,
          'map' => 'transformmodel_integer',
          'filter' => [
            ['field' => 'transformmodel_text', 'operator' => '=', 'value' => 'nonexisting'],
          ],
        ]);
        $result = $transform->transform([]);
        static::assertEquals(null, $result);
        static::assertNotEmpty($transform->getErrors());
    }

    /**
     * [testModelMapSingleOnetime description]
     * @throws ReflectionException
     * @throws exception
     */
    public function testModelMapSingleOnetime(): void
    {
        $this->createSampleTestData();

        //
        // static value / constant
        //
        $transform = $this->getTransform('model_map_single_onetime', [
          'model' => 'transformmodel',
          'map' => 'transformmodel_integer',
          'filter' => [
            ['field' => 'transformmodel_text', 'operator' => '=', 'value' => 'bar'],
          ],
        ]);
        $result = $transform->transform([]);
        static::assertEquals(222, $result);

        $transform->reset();

        $otherResult = $transform->transform([]);
        static::assertEquals($result, $otherResult);
    }

    /**
     * [testModelResultAll description]
     * @throws ReflectionException
     * @throws exception
     */
    public function testModelResultAll(): void
    {
        $this->createSampleTestData();

        //
        // static value / constant
        //
        $transform = $this->getTransform('model_result_all', [
          'model' => 'transformmodel',
          'filter' => [
            ['field' => 'transformmodel_text', 'operator' => '=', 'value' => ['foo', 'qux']],
          ],
        ]);
        $result = $transform->transform([]);
        static::assertCount(2, $result);

        //
        // static value / constant with grouping
        //
        $transform = $this->getTransform('model_result_all', [
          'model' => 'transformmodel',
          'filter' => [
            ['field' => 'transformmodel_text', 'operator' => '=', 'value' => ['foo', 'qux']],
          ],
          'group' => [
            'transformmodel_id',
          ],
        ]);
        $result = $transform->transform([]);
        static::assertCount(2, $result);

        //
        // static value / constant with calculated_fields and aggregate_filter
        //
        $transform = $this->getTransform('model_result_all', [
          'model' => 'transformmodel',
          'filter' => [
            ['field' => 'transformmodel_integer', 'operator' => '!=', 'value' => null],
          ],
          'filtercollection' => [
            'example' => [
              'filters' => [
                ['field' => 'transformmodel_integer', 'operator' => '=', 'value' => 111],
                ['field' => 'transformmodel_integer', 'operator' => '=', 'value' => ['source' => 'source', 'field' => 'source_key1']],
              ],
              'group_operator' => 'AND',
              'conjunction' => 'AND',
            ],
          ],
          'calculated_fields' => [
            ['field' => 'textIntegerOne', 'calculation' => 'SUM(transformmodel_integer)'],
            ['field' => 'textIntegerTwo', 'calculation' => 'SUM(transformmodel_integer)'],
          ],
          'aggregate_filter' => [
            ['field' => 'textIntegerOne', 'operator' => '=', 'value' => 111],
            ['field' => 'textIntegerTwo', 'operator' => '=', 'value' => ['source' => 'source', 'field' => 'source_key1']],
          ],
          'group' => [
            'transformmodel_id',
          ],
        ]);
        $result = $transform->transform([
          'source_key1' => 111,
        ]);

        static::assertCount(1, $result);
        static::assertEquals(111, $result[0]['textIntegerOne']);
        static::assertEquals(111, $result[0]['textIntegerTwo']);

        //
        // static value / constant, no matches
        //
        $transform = $this->getTransform('model_result_all', [
          'required' => true,
          'model' => 'transformmodel',
          'filter' => [
            ['field' => 'transformmodel_text', 'operator' => '=', 'value' => 'bla'],
          ],
        ]);
        $result = $transform->transform([]);
        static::assertIsArray($result);
        static::assertCount(0, $result);

        // model_result_all does NOT track any errors
        // related to the result and requiredness
        static::assertEmpty($transform->getErrors());
    }

    /**
     * [testModelResultAllNull description]
     * @throws ReflectionException
     * @throws exception
     */
    public function testModelResultAllNull(): void
    {
        $this->createSampleTestData();

        //
        // static value / constant with allow_null
        //
        $transform = $this->getTransform('model_result_all', [
          'model' => 'transformmodel',
          'filter' => [
            ['field' => 'transformmodel_text', 'operator' => '!=', 'value' => ['source' => 'source', 'field' => 'source_key1', 'allow_null' => true]],
          ],
        ]);
        $result = $transform->transform([]);
        static::assertCount(4, $result, print_r($result, true));

        //
        // static value / constant with null and required
        //
        $transform = $this->getTransform('model_result_all', [
          'required' => true,
          'model' => 'transformmodel',
          'filter' => [
            ['field' => 'transformmodel_text', 'operator' => '=', 'value' => ['source' => 'source', 'field' => 'source_key1']],
          ],
        ]);
        $result = $transform->transform([]);
        static::assertNull($result);

        $errors = $transform->getErrors();
        static::assertNotEmpty($errors);
        static::assertCount(1, $errors);
        static::assertEquals('VALUE_NULL', $errors[0]['__IDENTIFIER']);
        static::assertEquals('TRANSFORM.0', $errors[0]['__CODE']);

        //
        // static value / constant with null and required
        //
        $transform = $this->getTransform('model_result_all', [
          'required' => true,
          'model' => 'transformmodel',
          'calculated_fields' => [
            ['field' => 'textIntegerOne', 'calculation' => 'SUM(transformmodel_integer)'],
          ],
          'aggregate_filter' => [
            ['field' => 'textIntegerOne', 'operator' => '=', 'value' => ['source' => 'source', 'field' => 'source_key1']],
          ],
          'group' => [
            'transformmodel_id',
          ],
        ]);
        $result = $transform->transform([]);
        static::assertNull($result);

        $errors = $transform->getErrors();
        static::assertNotEmpty($errors);
        static::assertCount(1, $errors);
        static::assertEquals('VALUE_NULL', $errors[0]['__IDENTIFIER']);
        static::assertEquals('TRANSFORM.0', $errors[0]['__CODE']);

        //
        // static value / constant with null and required
        //
        $transform = $this->getTransform('model_result_all', [
          'required' => true,
          'model' => 'transformmodel',
          'filtercollection' => [
            'example' => [
              'filters' => [
                ['field' => 'transformmodel_integer', 'operator' => '=', 'value' => ['source' => 'source', 'field' => 'source_key1']],
              ],
              'group_operator' => 'AND',
              'conjunction' => 'AND',
            ],
          ],
        ]);
        $result = $transform->transform([]);
        static::assertCount(1, $result);

        $errors = $transform->getErrors();
        static::assertNotEmpty($errors);
        static::assertCount(1, $errors);
        static::assertEquals('VALUE_NULL', $errors[0]['__IDENTIFIER']);
        static::assertEquals('TRANSFORM.0', $errors[0]['__CODE']);
    }

    /**
     * [testModelResultAllOnetime description]
     * @throws ReflectionException
     * @throws exception
     */
    public function testModelResultAllOnetime(): void
    {
        $this->createSampleTestData();

        $transform = $this->getTransform('model_result_all_onetime', [
          'model' => 'transformmodel',
          'filter' => [
            [
              'field' => 'transformmodel_text',
              'operator' => '=',
              'value' => [
                'source' => 'source',
                'field' => 'source_key1',
              ],
            ],
          ],
        ]);
        $result = $transform->transform([
          'source_key1' => ['foo', 'bar'],
        ]);
        static::assertCount(2, $result);

        // reset the transform
        // this should not clear its internal cache
        // as this is a onetime transform
        $transform->reset();

        $otherResult = $transform->transform([
          'source_key1' => ['baz', 'qux'],
        ]);
        static::assertEquals($result, $otherResult);
    }

    /**
     * Tests for allow_null to be disabled by default
     * in a transform_model_* filter
     * @throws ReflectionException
     * @throws exception
     */
    public function testModelFilterAllowNullDefaultDisabled(): void
    {
        $this->createSampleTestData();
        $transform = $this->getTransform('model_result_all', [
          'model' => 'transformmodel',
          'filter' => [
            [
              'field' => 'transformmodel_integer',
              'operator' => '=',
              'value' => [
                'source' => 'source',
                'field' => 'filter_value',
              ],
            ],
          ],
        ]);
        $result = $transform->transform([
          'filter_value' => null,
        ]);
        static::assertNull($result);
        // required is implicitly false, errorstack must be empty
        static::assertEmpty($transform->getErrors());
    }

    /**
     * [testModelFilterAllowNullDefaultDisabledRequired description]
     * @throws ReflectionException
     * @throws exception
     */
    public function testModelFilterAllowNullDefaultDisabledRequired(): void
    {
        $this->createSampleTestData();
        $transform = $this->getTransform('model_result_all', [
          'model' => 'transformmodel',
          'filter' => [
            [
              'field' => 'transformmodel_integer',
              'operator' => '=',
              'value' => [
                'source' => 'source',
                'field' => 'filter_value',
              ],
            ],
          ],
          'required' => true,
        ]);
        $result = $transform->transform([
          'filter_value' => null,
        ]);
        static::assertNull($result);
        // required is implicitly false, errorstack must be empty
        static::assertNotEmpty($transform->getErrors());
    }

    /**
     * [testModelFilterAllowNullWorks description]
     * @throws ReflectionException
     * @throws exception
     */
    public function testModelFilterAllowNullWorks(): void
    {
        $this->createSampleTestData();
        $transform = $this->getTransform('model_result_all', [
          'model' => 'transformmodel',
          'filter' => [
            [
              'field' => 'transformmodel_integer',
              'operator' => '=',
              'value' => [
                'source' => 'source',
                'field' => 'filter_value',
                'allow_null' => true,
              ],
            ],
          ],
        ]);
        $result = $transform->transform([
          'filter_value' => null,
        ]);
        static::assertCount(1, $result);
        static::assertEmpty($transform->getErrors());
    }

    /**
     * Tests basic query using a simple joined model.
     * @throws ReflectionException
     * @throws exception
     */
    public function testModelResultOneJoined(): void
    {
        $this->createSampleTestData();
        $this->createJoinableSampleTestData();

        //
        // static value / constant
        //
        $transform = $this->getTransform('model_result_one', [
          'model' => 'transformmodel',
          'join' => [
            ['model' => 'tjsample'],
          ],
          'filter' => [
            ['field' => 'transformmodel_text', 'operator' => '=', 'value' => 'qux'],
          ],
        ]);
        $result = $transform->transform([]);
        static::assertEquals(333, $result['transformmodel_integer']);
        static::assertEquals($result['transformmodel_text'] . '-join', $result['tjsample_text']);
    }

    /**
     * Additionally creates joinable datasets in model tjsample
     * @throws ReflectionException
     * @throws exception
     */
    protected function createJoinableSampleTestData(): void
    {
        //
        // create joinable counterparts in tjsample
        //
        $model = $this->getModel('transformmodel');
        $res = $model->search()->getResult();

        // make sure there's data to join
        static::assertNotEmpty($res);

        $tjSample = $this->getModel('tjsample');
        foreach ($res as $r) {
            $tjSample->save([
              'tjsample_text' => $r['transformmodel_text'] . '-join',
              'tjsample_transformmodel_id' => $r[$model->getPrimaryKey()],
            ]);
        }
    }

    /**
     * [testModelResultOne description]
     * @throws ReflectionException
     * @throws exception
     */
    public function testModelResultOne(): void
    {
        $this->createSampleTestData();

        //
        // static value / constant
        //
        $transform = $this->getTransform('model_result_one', [
          'model' => 'transformmodel',
          'filter' => [
            ['field' => 'transformmodel_text', 'operator' => '=', 'value' => 'qux'],
          ],
        ]);
        $result = $transform->transform([]);
        static::assertEquals(333, $result['transformmodel_integer']);

        //
        // static value / constant, ambiguous
        //
        $transform = $this->getTransform('model_result_one', [
          'model' => 'transformmodel',
          'filter' => [
            ['field' => 'transformmodel_text', 'operator' => '=', 'value' => ['qux', 'foo']],
          ],
        ]);
        $result = $transform->transform([]);
        static::assertEquals(null, $result, 'Expect null result on ambiguous result');

        //
        // static value / constant, nonexisting
        //
        $transform = $this->getTransform('model_result_one', [
          'model' => 'transformmodel',
          'filter' => [
            ['field' => 'transformmodel_text', 'operator' => '=', 'value' => 'bla'],
          ],
        ]);
        $result = $transform->transform([]);
        static::assertEquals(null, $result, 'Expect null result on nonexisting result');

        //
        // Required flag testing
        //
        $transform = $this->getTransform('model_result_one', [
          'required' => true,
          'model' => 'transformmodel',
          'filter' => [
            ['field' => 'transformmodel_text', 'operator' => '=', 'value' => 'bla'],
          ],
        ]);
        $result = $transform->transform([]);
        static::assertEquals(null, $result, 'Expect null result on nonexisting result');
        static::assertNotEmpty($transform->getErrors());

        //
        // dynamic value / constant
        //
        $transform = $this->getTransform('model_result_one', [
          'model' => 'transformmodel',
          'filter' => [
            [
              'field' => 'transformmodel_text',
              'operator' => '=',
              'value' => [
                'source' => 'source',
                'field' => 'source_key1',
              ],
            ],
          ],
        ]);
        $result = $transform->transform([
          'source_key1' => 'foo',
        ]);
        static::assertEquals(111, $result['transformmodel_integer']);
    }

    /**
     * [testModelResultOneOnetime description]
     * @throws ReflectionException
     * @throws exception
     */
    public function testModelResultOneOnetime(): void
    {
        $this->createSampleTestData();

        $transform = $this->getTransform('model_result_one_onetime', [
          'model' => 'transformmodel',
          'filter' => [
            [
              'field' => 'transformmodel_text',
              'operator' => '=',
              'value' => [
                'source' => 'source',
                'field' => 'source_key1',
              ],
            ],
          ],
        ]);
        $result = $transform->transform([
          'source_key1' => 'foo',
        ]);
        static::assertEquals(111, $result['transformmodel_integer']);

        // 'reset' data - must not change
        // as this is a onetime transform
        $transform->reset();

        $result = $transform->transform([
          'source_key1' => 'qux',
        ]);
        static::assertEquals(111, $result['transformmodel_integer']);
    }

    /**
     * Test Spec output (simple case)
     * @throws ReflectionException
     * @throws exception
     */
    public function testSpecification(): void
    {
        $transform = $this->getTransform('model_result_all', [
          'model' => 'transformmodel',
          'filter' => [
            ['field' => 'transformmodel_integer', 'operator' => '!=', 'value' => null],
            ['field' => 'transformmodel_integer', 'operator' => '!=', 'value' => ['source' => 'source', 'field' => 'source_key1']],
          ],
          'filtercollection' => [
            'example' => [
              'filters' => [
                ['field' => 'transformmodel_integer', 'operator' => '=', 'value' => 111],
                ['field' => 'transformmodel_integer', 'operator' => '=', 'value' => ['source' => 'source', 'field' => 'source_key1']],
              ],
              'group_operator' => 'AND',
              'conjunction' => 'AND',
            ],
          ],
        ]);

        static::assertEquals(
            [
              'type' => 'transform',
              'source' => [
                'model.transformmodel',
                'source.source_key1',
                'source.source_key1',
              ],
            ],
            $transform->getSpecification()
        );
    }

    /**
     * {@inheritDoc}
     * @throws ReflectionException
     * @throws exception
     */
    protected function tearDown(): void
    {
        $this->getModel('tjsample')
          ->addFilter('tjsample_id', 0, '>')
          ->delete();

        $this->getModel('transformmodel')
          ->addFilter('transformmodel_id', 0, '>')
          ->delete();
    }

    /**
     * {@inheritDoc}
     */
    protected function setUp(): void
    {
        $app = static::createApp();

        // Don't forget to inject core-io
        overrideableApp::__injectApp([
          'vendor' => 'codename',
          'app' => 'core-io',
          'namespace' => '\\codename\\core\\io',
        ]);

        // Additional overrides to get a more complete app lifecycle
        // and allow static global app::getModel() to work correctly
        $app::__setApp('transformmodeltest');
        $app::__setVendor('codename');
        $app::__setNamespace('\\codename\\core\\io\\tests\\transform');

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
                  // 'database_file' => 'testmodel.sqlite',
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

        static::createModel('transformtest', 'transformmodel', [
          'field' => [
            'transformmodel_id',
            'transformmodel_created',
            'transformmodel_modified',
            'transformmodel_text',
            'transformmodel_integer',
          ],
          'primary' => [
            'transformmodel_id',
          ],
          'datatype' => [
            'transformmodel_id' => 'number_natural',
            'transformmodel_created' => 'text_timestamp',
            'transformmodel_modified' => 'text_timestamp',
            'transformmodel_text' => 'text',
            'transformmodel_integer' => 'number_natural',
          ],
          'connection' => 'default',
        ], function ($schema, $model, $config) {
            return new transformmodel([]);
        });

        static::createModel('transformtest', 'tjsample', [
          'field' => [
            'tjsample_id',
            'tjsample_created',
            'tjsample_modified',
            'tjsample_transformmodel_id',
            'tjsample_text',
            'tjsample_integer',
          ],
          'primary' => [
            'tjsample_id',
          ],
          'foreign' => [
            'tjsample_transformmodel_id' => [
              'schema' => 'transformtest',
              'model' => 'transformmodel',
              'key' => 'transformmodel_id',
            ],
          ],
          'datatype' => [
            'tjsample_id' => 'number_natural',
            'tjsample_created' => 'text_timestamp',
            'tjsample_modified' => 'text_timestamp',
            'tjsample_transformmodel_id' => 'number_natural',
            'tjsample_text' => 'text',
            'tjsample_integer' => 'number_natural',
          ],
          'connection' => 'default',
        ], function ($schema, $model, $config) {
            return new tjsample([]);
        });

        static::architect('transformmodeltest', 'codename', 'test');
    }
}
