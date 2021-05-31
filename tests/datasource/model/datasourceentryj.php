<?php
namespace codename\core\io\tests\datasource\model;

/**
 * SQL Base model leveraging the new model servicing modules
 * and enables freely defining and loading model configs
 */
class datasourceentryj extends \codename\core\test\sqlModel {
  /**
   * @inheritDoc
   */
  public function __CONSTRUCT(array $modeldata = array())
  {
    parent::__CONSTRUCT('datasourcetest', 'datasourceentryj', [
      'field' => [
        'datasourceentryj_id',
        'datasourceentryj_created',
        'datasourceentryj_modified',
        'datasourceentryj_datasourceentry_id',
        'datasourceentryj_text',
      ],
      'primary' => [
        'datasourceentryj_id'
      ],
      'foreign' => [
        'datasourceentryj_datasourceentry_id' => [
          'schema'  => 'datasourcetest',
          'model'   => 'datasourceentry',
          'key'     => 'datasourceentry_id'
        ],
      ],
      'datatype' => [
        'datasourceentryj_id'                 => 'number_natural',
        'datasourceentryj_created'            => 'text_timestamp',
        'datasourceentryj_modified'           => 'text_timestamp',
        'datasourceentryj_datasourceentry_id' => 'number_natural',
        'datasourceentryj_text'               => 'text',
      ],
      'connection' => 'default'
    ]);
  }
}
