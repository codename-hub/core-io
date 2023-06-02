<?php

namespace codename\core\io\tests\datasource\model;

use codename\core\test\sqlModel;

/**
 * SQL Base model leveraging the new model servicing modules
 * and enables freely defining and loading model configs
 */
class datasourceentryj extends sqlModel
{
    /**
     * {@inheritDoc}
     */
    public function __construct(array $modeldata = [])
    {
        parent::__construct('datasourcetest', 'datasourceentryj', [
          'field' => [
            'datasourceentryj_id',
            'datasourceentryj_created',
            'datasourceentryj_modified',
            'datasourceentryj_datasourceentry_id',
            'datasourceentryj_text',
          ],
          'primary' => [
            'datasourceentryj_id',
          ],
          'foreign' => [
            'datasourceentryj_datasourceentry_id' => [
              'schema' => 'datasourcetest',
              'model' => 'datasourceentry',
              'key' => 'datasourceentry_id',
            ],
          ],
          'datatype' => [
            'datasourceentryj_id' => 'number_natural',
            'datasourceentryj_created' => 'text_timestamp',
            'datasourceentryj_modified' => 'text_timestamp',
            'datasourceentryj_datasourceentry_id' => 'number_natural',
            'datasourceentryj_text' => 'text',
          ],
          'connection' => 'default',
        ]);
    }
}
