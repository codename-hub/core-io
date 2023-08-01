<?php

namespace codename\core\io\tests\datasource\model;

use codename\core\test\sqlModel;

/**
 * SQL Base model leveraging the new model servicing modules
 * and enables freely defining and loading model configs
 */
class datasourceentry extends sqlModel
{
    /**
     * {@inheritDoc}
     */
    public function __construct(array $modeldata = [])
    {
        parent::__construct('datasourcetest', 'datasourceentry', [
          'field' => [
            'datasourceentry_id',
            'datasourceentry_created',
            'datasourceentry_modified',
            'datasourceentry_text',
            'datasourceentry_integer',
          ],
          'primary' => [
            'datasourceentry_id',
          ],
          'datatype' => [
            'datasourceentry_id' => 'number_natural',
            'datasourceentry_created' => 'text_timestamp',
            'datasourceentry_modified' => 'text_timestamp',
            'datasourceentry_text' => 'text',
            'datasourceentry_integer' => 'number_natural',
          ],
          'connection' => 'default',
        ]);
    }
}
