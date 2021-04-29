<?php
namespace codename\core\io\tests\target\model;

/**
 * SQL Base model leveraging the new model servicing modules
 * and enables freely defining and loading model configs
 */
class testmodel extends \codename\core\tests\sqlModel {
  /**
   * @inheritDoc
   */
  public function __CONSTRUCT(array $modeldata = array())
  {
    parent::__CONSTRUCT('targettest', 'testmodel', [
      'field' => [
        'testmodel_id',
        'testmodel_created',
        'testmodel_modified',
        'testmodel_text',
        'testmodel_unique_single',
        'testmodel_unique_multi1',
        'testmodel_unique_multi2',
      ],
      'primary' => [
        'testmodel_id'
      ],
      'unique' => [
        'testmodel_unique_single',
        [ 'testmodel_unique_multi1', 'testmodel_unique_multi2' ],
      ],
      'options' => [
        'testmodel_unique_single' => [
          'length' => 16
        ],
        'testmodel_unique_multi1' => [
          'length' => 16
        ],
        'testmodel_unique_multi2' => [
          'length' => 16
        ],
      ],
      'datatype' => [
        'testmodel_id'       => 'number_natural',
        'testmodel_created'  => 'text_timestamp',
        'testmodel_modified' => 'text_timestamp',
        'testmodel_text'     => 'text',
        'testmodel_unique_single' => 'text',
        'testmodel_unique_multi1' => 'text',
        'testmodel_unique_multi2' => 'text',
      ],
      'connection' => 'default'
    ]);
  }
}
