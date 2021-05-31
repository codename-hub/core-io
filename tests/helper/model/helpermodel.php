<?php
namespace codename\core\io\tests\helper\model;

/**
 * SQL Base model leveraging the new model servicing modules
 * and enables freely defining and loading model configs
 */
class helpermodel extends \codename\core\test\sqlModel {
  /**
   * @inheritDoc
   */
  public function __CONSTRUCT(array $modeldata = array())
  {
    parent::__CONSTRUCT('helpertest', 'helpermodel', [
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
    ]);
  }
}
