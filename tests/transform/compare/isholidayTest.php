<?php

namespace codename\core\io\tests\transform\compare;

use codename\core\exception;
use codename\core\io\tests\transform\abstractTransformTest;
use codename\core\io\tests\transform\compare\model\holidays;
use codename\core\test\overrideableApp;
use ReflectionException;

class isholidayTest extends abstractTransformTest
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
     * Testing transforms for Errors
     * @throws ReflectionException
     * @throws exception
     */
    public function testValueValid(): void
    {
        $this->getModel('holidays')->save([
          'holidays_country' => 'DE',
          'holidays_orderitemcommission_type' => 'energy',
          'holidays_check_date' => '2021-04-19',
          'holidays_can_change' => false,
        ]);

        $transform = $this->getTransform('compare_isholiday', [
          'country' => [
            'source' => 'source',
            'field' => 'example_country_field',
          ],
          'date' => [
            'source' => 'source',
            'field' => 'example_date_field',
          ],
        ]);
        $result = $transform->transform([
          'example_country_field' => 'DE',
          'example_date_field' => '2021-04-19',
        ]);
        // Make sure it stays an array
        static::assertTrue($result);
    }

    /**
     * Testing transforms for Errors
     * @throws ReflectionException
     * @throws exception
     */
    public function testValueNotValid(): void
    {
        $this->getModel('holidays')->save([
          'holidays_country' => 'DE',
          'holidays_orderitemcommission_type' => 'energy',
          'holidays_check_date' => '2021-04-19',
          'holidays_can_change' => false,
        ]);

        $transform = $this->getTransform('compare_isholiday', [
          'country' => [
            'source' => 'source',
            'field' => 'example_country_field',
          ],
          'date' => [
            'source' => 'source',
            'field' => 'example_date_field',
          ],
        ]);
        $result = $transform->transform([
          'example_country_field' => 'DE',
          'example_date_field' => '2021-04-20',
        ]);
        // Make sure it stays an array
        static::assertFalse($result);
    }

    /**
     * Test Spec output (simple case)
     * @throws ReflectionException
     * @throws exception
     */
    public function testSpecification(): void
    {
        $transform = $this->getTransform('compare_isholiday', [
          'country' => [
            'source' => 'source',
            'field' => 'example_country_field',
          ],
          'date' => [
            'source' => 'source',
            'field' => 'example_date_field',
          ],
        ]);
        static::assertEquals(
            [
              'type' => 'transform',
              'source' => ['source.example_country_field', 'source.example_date_field'],
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
        $this->getModel('holidays')
          ->addFilter('holidays_id', 0, '>')
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
        $app::__setNamespace('\\codename\\core\\io\\tests\\transform\\compare');

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

        static::createModel('transformtest', 'holidays', [
          'field' => [
            'holidays_id',
            'holidays_created',
            'holidays_modified',
            'holidays_country',
            'holidays_orderitemcommission_type',
            'holidays_check_date',
            'holidays_day_type',
            'holidays_type',
            'holidays_type_name',
            'holidays_can_change',
          ],
          'primary' => [
            'holidays_id',
          ],
          'datatype' => [
            'holidays_id' => 'number_natural',
            'holidays_created' => 'text_timestamp',
            'holidays_modified' => 'text_timestamp',
            'holidays_country' => 'text',
            'holidays_orderitemcommission_type' => 'text',
            'holidays_check_date' => 'text_timestamp',
            'holidays_day_type' => 'number_natural',
            'holidays_type' => 'boolean',
            'holidays_type_name' => 'text',
            'holidays_can_change' => 'boolean',
          ],
          'connection' => 'default',
        ], function ($schema, $model, $config) {
            return new holidays([]);
        });

        static::architect('transformmodeltest', 'codename', 'test');
    }
}
