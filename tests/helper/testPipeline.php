<?php
namespace codename\core\io\tests\helper;

use codename\core\test\base;
use codename\core\test\overrideableApp;

use codename\core\io\helper\pipeline;

class testPipeline extends base
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
    $this->getModel('helperjmodel')
      ->addFilter('helperjmodel_id', 0, '>')
      ->delete();

    $this->getModel('helpermodel')
      ->addFilter('helpermodel_id', 0, '>')
      ->delete();
  }

  /**
   * @inheritDoc
   */
  protected function setUp(): void
  {
    parent::setUp();
    overrideableApp::__injectApp([
      'vendor' => 'codename',
      'app' => 'core-io',
      'namespace' => '\\codename\\core\\io'
    ]);

    $app = static::createApp();

    // Additional overrides to get a more complete app lifecycle
    // and allow static global app::getModel() to work correctly
    $app->__setApp('helpermodeltest');
    $app->__setVendor('codename');
    $app->__setNamespace('\\codename\\core\\io\\tests\\helper');

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

    static::createModel('helpertest', 'helpermodel', [
      'field' => [
        'helpermodel_id',
        'helpermodel_created',
        'helpermodel_modified',
        'helpermodel_text',
      ],
      'primary' => [
        'helpermodel_id'
      ],
      'datatype' => [
        'helpermodel_id'       => 'number_natural',
        'helpermodel_created'  => 'text_timestamp',
        'helpermodel_modified' => 'text_timestamp',
        'helpermodel_text'     => 'text',
      ],
      'connection' => 'default'
    ], function($schema, $model, $config) {
      return new \codename\core\io\tests\helper\model\helpermodel([]);
    });

    static::createModel('helpertest', 'helperjmodel', [
      'field' => [
        'helperjmodel_id',
        'helperjmodel_created',
        'helperjmodel_modified',
        'helperjmodel_helpermodel_id',
        'helperjmodel_text',
        'helperjmodel_text_date',
        'helperjmodel_structure',
        'helperjmodel_integer',
        'helperjmodel_number',
        'helperjmodel_boolean',
      ],
      'primary' => [
        'helperjmodel_id'
      ],
      'foreign' => [
        'helperjmodel_helpermodel_id' => [
          'schema'  => 'helpertest',
          'model'   => 'helpermodel',
          'key'     => 'helpermodel_id'
        ],
      ],
      'datatype' => [
        'helperjmodel_id'             => 'number_natural',
        'helperjmodel_created'        => 'text_timestamp',
        'helperjmodel_modified'       => 'text_timestamp',
        'helperjmodel_helpermodel_id' => 'number_natural',
        'helperjmodel_text'           => 'text',
        'helperjmodel_text_date'      => 'text_date',
        'helperjmodel_structure'      => 'structure',
        'helperjmodel_integer'        => 'number_natural',
        'helperjmodel_number'         => 'number',
        'helperjmodel_boolean'        => 'boolean',
      ],
      'connection' => 'default'
    ], function($schema, $model, $config) {
      return new \codename\core\io\tests\helper\model\helperjmodel([]);
    });

    static::architect('helpermodeltest', 'codename', 'test');
  }

  /**
   * [testHelperPipelineBufferedFileParquet description]
   * @return [type] [description]
   */
  public function testHelperPipelineBufferedFileParquet () {

    $model = $this->getModel('helpermodel')->addModel($this->getModel('helperjmodel'));

    $example = pipeline::createModelToModelPipelineConfig($model, 'buffered_file_parquet');

    $this->assertEquals([
      'info'            => [
        'name'          => 'generated',
        'description'   => null,
      ],
      'source'          => [
        'type'          => 'model',
        'query'         => [],
      ],
      'transform'       => [
        'helpermodel_created_to_dti'  => [
          'type'    => 'convert_datetime',
          'config'  => [
            'source'        => 'source',
            'field'         => 'helpermodel_created',
            'source_format' => 'Y-m-d H:i:s',
            'target_format' => 'DateTimeImmutable',
          ],
        ],
        'helpermodel_modified_to_dti' => [
          'type'    => 'convert_datetime',
          'config'  => [
            'source'        => 'source',
            'field'         => 'helpermodel_modified',
            'source_format' => 'Y-m-d H:i:s',
            'target_format' => 'DateTimeImmutable',
          ],
        ],
        'helperjmodel_created_to_dti' => [
          'type'    => 'convert_datetime',
          'config'  => [
            'source'        => 'source',
            'field'         => 'helperjmodel_created',
            'source_format' => 'Y-m-d H:i:s',
            'target_format' => 'DateTimeImmutable',
          ],
        ],
        'helperjmodel_modified_to_dti'  => [
          'type'    => 'convert_datetime',
          'config'  => [
            'source'        => 'source',
            'field'         => 'helperjmodel_modified',
            'source_format' => 'Y-m-d H:i:s',
            'target_format' => 'DateTimeImmutable',
          ],
        ],
        'helperjmodel_text_date_to_dti'  => [
          'type'    => 'convert_datetime',
          'config'  => [
            'source'        => 'source',
            'field'         => 'helperjmodel_text_date',
            'source_format' => 'Y-m-d',
            'target_format' => 'DateTimeImmutable',
          ],
        ],
        'helperjmodel_structure_to_json'  => [
          'type'    => 'convert_json',
          'config'  => [
            'source'        => 'source',
            'field'         => 'helperjmodel_structure',
            'mode'          => 'encode',
          ],
        ],
      ],
      'target'          => [
        'generated_target_buffered_file_parquet'  => [
          'type'        => 'buffered_file_parquet',
          'buffer'      => 1,
          'buffer_size' => 10000,
          'compression' => 'gzip',
          'mapping'     => [
            'helpermodel_id'  => [
              'type'          => 'source',
              'field'         => 'helpermodel_id',
              'php_type'      => 'integer',
              'is_nullable'   => false,
            ],
            'helpermodel_created'  => [
              'type'          => 'transform',
              'field'         => 'helpermodel_created_to_dti',
              'php_type'      => 'object',
              'php_class'      => 'DateTimeImmutable',
              'datetime_format'   => 2,
              'is_nullable'   => true,
            ],
            'helpermodel_modified'  => [
              'type'          => 'transform',
              'field'         => 'helpermodel_modified_to_dti',
              'php_type'      => 'object',
              'php_class'      => 'DateTimeImmutable',
              'datetime_format'   => 2,
              'is_nullable'   => true,
            ],
            'helpermodel_text'  => [
              'type'          => 'source',
              'field'         => 'helpermodel_text',
              'php_type'      => 'string',
              'is_nullable'   => true,
            ],
            'helperjmodel_id'  => [
              'type'          => 'source',
              'field'         => 'helperjmodel_id',
              'php_type'      => 'integer',
              'is_nullable'   => true,
            ],
            'helperjmodel_created'  => [
              'type'          => 'transform',
              'field'         => 'helperjmodel_created_to_dti',
              'php_type'      => 'object',
              'php_class'      => 'DateTimeImmutable',
              'datetime_format'   => 2,
              'is_nullable'   => true,
            ],
            'helperjmodel_modified'  => [
              'type'          => 'transform',
              'field'         => 'helperjmodel_modified_to_dti',
              'php_type'      => 'object',
              'php_class'      => 'DateTimeImmutable',
              'datetime_format'   => 2,
              'is_nullable'   => true,
            ],
            'helperjmodel_helpermodel_id'  => [
              'type'          => 'source',
              'field'         => 'helperjmodel_helpermodel_id',
              'php_type'      => 'integer',
              'is_nullable'   => true,
            ],
            'helperjmodel_text'  => [
              'type'          => 'source',
              'field'         => 'helperjmodel_text',
              'php_type'      => 'string',
              'is_nullable'   => true,
            ],
            'helperjmodel_text_date'  => [
              'type'          => 'transform',
              'field'         => 'helperjmodel_text_date_to_dti',
              'php_type'      => 'object',
              'php_class'      => 'DateTimeImmutable',
              'datetime_format'   => 4,
              'is_nullable'   => true,
            ],
            'helperjmodel_structure'  => [
              'type'          => 'transform',
              'field'         => 'helperjmodel_structure_to_json',
              'php_type'      => 'string',
              'is_nullable'   => true,
            ],
            'helperjmodel_integer'  => [
              'type'          => 'source',
              'field'         => 'helperjmodel_integer',
              'php_type'      => 'integer',
              'is_nullable'   => true,
            ],
            'helperjmodel_number'  => [
              'type'          => 'source',
              'field'         => 'helperjmodel_number',
              'php_type'      => 'double',
              'is_nullable'   => true,
            ],
            'helperjmodel_boolean'  => [
              'type'          => 'source',
              'field'         => 'helperjmodel_boolean',
              'php_type'      => 'boolean',
              'is_nullable'   => true,
            ],
          ],
        ],
      ],
    ], $example->get());

  }

}
