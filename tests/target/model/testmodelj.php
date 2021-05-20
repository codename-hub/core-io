<?php
namespace codename\core\io\tests\target\model;

/**
 * SQL Base model leveraging the new model servicing modules
 * and enables freely defining and loading model configs
 */
class testmodelj extends \codename\core\tests\sqlModel {
  /**
   * @inheritDoc
   */
  public function __CONSTRUCT(array $modeldata = array())
  {
    parent::__CONSTRUCT('targettest', 'testmodelj', static::$staticConfig);
  }

  /**
   * static configuration
   * for usage in unit tests
   * @var array
   */
  public static $staticConfig = [
    'field' => [
      'testmodelj_id',
      'testmodelj_created',
      'testmodelj_modified',
      'testmodelj_testmodel_id',
      'testmodelj_testmodel',
      'testmodelj_text',
    ],
    'primary' => [
      'testmodelj_id'
    ],
    'children' => [
      'testmodelj_testmodel' => [
        'type' => 'foreign',
        'field' => 'testmodelj_testmodel_id',
      ]
    ],
    'foreign' => [
      'testmodelj_testmodel_id' => [
        'schema'  => 'targettest',
        'model'   => 'testmodel',
        'key'     => 'testmodel_id'
      ],
    ],
    'datatype' => [
      'testmodelj_id'           => 'number_natural',
      'testmodelj_created'      => 'text_timestamp',
      'testmodelj_modified'     => 'text_timestamp',
      'testmodelj_testmodel_id' => 'number_natural',
      'testmodelj_testmodel'    => 'virtual',
      'testmodelj_text'         => 'text',
    ],
    'connection' => 'default'
  ];
}
