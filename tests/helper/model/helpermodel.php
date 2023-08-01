<?php

namespace codename\core\io\tests\helper\model;

use codename\core\test\sqlModel;

/**
 * SQL Base model leveraging the new model servicing modules
 * and enables freely defining and loading model configs
 */
class helpermodel extends sqlModel
{
    /**
     * {@inheritDoc}
     */
    public function __construct(array $modeldata = [])
    {
        parent::__construct('helpertest', 'helpermodel', [
          'field' => [
            'helpermodel_id',
            'helpermodel_created',
            'helpermodel_modified',
            'helpermodel_text',
          ],
          'primary' => [
            'helpermodel_id',
          ],
          'datatype' => [
            'helpermodel_id' => 'number_natural',
            'helpermodel_created' => 'text_timestamp',
            'helpermodel_modified' => 'text_timestamp',
            'helpermodel_text' => 'text',
          ],
          'connection' => 'default',
        ]);
    }
}
