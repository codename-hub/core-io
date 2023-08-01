<?php

namespace codename\core\io\tests\transform\compare\model;

use codename\core\test\sqlModel;

/**
 * SQL Base model leveraging the new model servicing modules
 * and enables freely defining and loading model configs
 */
class holidays extends sqlModel
{
    /**
     * {@inheritDoc}
     */
    public function __construct(array $modeldata = [])
    {
        parent::__construct('transformtest', 'holidays', [
          'field' => [
            'holidays_id',
            'holidays_created',
            'holidays_modified',
            'holidays_country',
            'holidays_orderitemcommission_type',
            'holidays_check_date',
              // 'holidays_day_type',
              // 'holidays_type',
              // 'holidays_type_name',
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
              // 'holidays_day_type'                  => 'number_natural',
              // 'holidays_type'                      => 'boolean',
              // 'holidays_type_name'                 => 'text',
            'holidays_can_change' => 'boolean',
          ],
          'connection' => 'default',
        ]);
    }
}
