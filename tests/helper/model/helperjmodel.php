<?php

namespace codename\core\io\tests\helper\model;

use codename\core\test\sqlModel;

/**
 * SQL Base model leveraging the new model servicing modules
 * and enables freely defining and loading model configs
 * BTW: helperjmodel means "helpermodel join sample"
 */
class helperjmodel extends sqlModel
{
    /**
     * {@inheritDoc}
     */
    public function __construct(array $modeldata = [])
    {
        parent::__construct('helpertest', 'helperjmodel', [
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
            'helperjmodel_virtual',
          ],
          'primary' => [
            'helperjmodel_id',
          ],
          'foreign' => [
            'helperjmodel_helpermodel_id' => [
              'schema' => 'helpertest',
              'model' => 'helpermodel',
              'key' => 'helpermodel_id',
            ],
          ],
          'datatype' => [
            'helperjmodel_id' => 'number_natural',
            'helperjmodel_created' => 'text_timestamp',
            'helperjmodel_modified' => 'text_timestamp',
            'helperjmodel_helpermodel_id' => 'number_natural',
            'helperjmodel_text' => 'text',
            'helperjmodel_text_date' => 'text_date',
            'helperjmodel_structure' => 'structure',
            'helperjmodel_integer' => 'number_natural',
            'helperjmodel_number' => 'number',
            'helperjmodel_boolean' => 'boolean',
            'helperjmodel_virtual' => 'virtual',
          ],
          'connection' => 'default',
        ]);
    }
}
