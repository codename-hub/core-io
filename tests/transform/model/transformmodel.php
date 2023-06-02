<?php

namespace codename\core\io\tests\transform\model;

use codename\core\test\sqlModel;

/**
 * SQL Base model leveraging the new model servicing modules
 * and enables freely defining and loading model configs
 */
class transformmodel extends sqlModel
{
    /**
     * {@inheritDoc}
     */
    public function __construct(array $modeldata = [])
    {
        parent::__construct('transformtest', 'transformmodel', [
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
        ]);
    }
}
