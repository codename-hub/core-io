<?php
namespace codename\core\io\tests\transform\compare\model;

/**
 * SQL Base model leveraging the new model servicing modules
 * and enables freely defining and loading model configs
 */
class holidays extends \codename\core\test\sqlModel {
  /**
   * @inheritDoc
   */
  public function __CONSTRUCT(array $modeldata = array())
  {
    parent::__CONSTRUCT('transformtest', 'holidays', [
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
        'holidays_id'
      ],
      'datatype' => [
        'holidays_id'                        => 'number_natural',
        'holidays_created'                   => 'text_timestamp',
        'holidays_modified'                  => 'text_timestamp',
        'holidays_country'                   => 'text',
        'holidays_orderitemcommission_type'  => 'text',
        'holidays_check_date'                => 'text_timestamp',
        // 'holidays_day_type'                  => 'number_natural',
        // 'holidays_type'                      => 'boolean',
        // 'holidays_type_name'                 => 'text',
        'holidays_can_change'                => 'boolean',
      ],
      'connection' => 'default'
    ]);
  }
}
