<?php
namespace codename\core\io\tests\datasource\model;

/**
 * SQL Base model leveraging the new model servicing modules
 * and enables freely defining and loading model configs
 */
class datasourceentry extends \codename\core\test\sqlModel {
  /**
   * @inheritDoc
   */
  public function __CONSTRUCT(array $modeldata = array())
  {
    parent::__CONSTRUCT('datasourcetest', 'datasourceentry', [
      'field' => [
        'datasourceentry_id',
        'datasourceentry_created',
        'datasourceentry_modified',
        'datasourceentry_text',
        'datasourceentry_integer',
      ],
      'primary' => [
        'datasourceentry_id'
      ],
      'datatype' => [
        'datasourceentry_id'       => 'number_natural',
        'datasourceentry_created'  => 'text_timestamp',
        'datasourceentry_modified' => 'text_timestamp',
        'datasourceentry_text'     => 'text',
        'datasourceentry_integer'  => 'number_natural',
      ],
      'connection' => 'default'
    ]);
  }
}
