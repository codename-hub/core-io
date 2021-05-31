<?php
namespace codename\core\io\tests\transform\model;

/**
 * SQL Base model leveraging the new model servicing modules
 * and enables freely defining and loading model configs
 * BTW: tjsample means "transformmodel join sample"
 */
class tjsample extends \codename\core\test\sqlModel {
  /**
   * @inheritDoc
   */
  public function __CONSTRUCT(array $modeldata = array())
  {
    parent::__CONSTRUCT('transformtest', 'tjsample', [
      'field' => [
        'tjsample_id',
        'tjsample_created',
        'tjsample_modified',
        'tjsample_transformmodel_id',
        'tjsample_text',
        'tjsample_integer',
      ],
      'primary' => [
        'tjsample_id'
      ],
      'foreign' => [
        'tjsample_transformmodel_id' => [
          'schema'  => 'transformtest',
          'model'   => 'transformmodel',
          'key'     => 'transformmodel_id'
        ],
      ],
      'datatype' => [
        'tjsample_id'       => 'number_natural',
        'tjsample_created'  => 'text_timestamp',
        'tjsample_modified' => 'text_timestamp',
        'tjsample_transformmodel_id' => 'number_natural',
        'tjsample_text'     => 'text',
        'tjsample_integer'  => 'number_natural',
      ],
      'connection' => 'default'
    ]);
  }
}
