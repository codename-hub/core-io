<?php
namespace codename\core\io\tests\pipeline\model;

/**
 * SQL Base model leveraging the new model servicing modules
 * and enables freely defining and loading model configs
 */
class pipelinemodel extends \codename\core\tests\sqlModel {
  /**
   * @inheritDoc
   */
  public function __CONSTRUCT(array $modeldata = array())
  {
    parent::__CONSTRUCT('pipelinetest', 'pipelinemodel', static::$staticConfig);
  }

  /**
   * static configuration
   * for usage in unit tests
   * @var array
   */
  public static $staticConfig = [
    'field' => [
      'pipelinemodel_id',
      'pipelinemodel_created',
      'pipelinemodel_modified',
      'pipelinemodel_text',
    ],
    'primary' => [
      'pipelinemodel_id'
    ],
    'datatype' => [
      'pipelinemodel_id'       => 'number_natural',
      'pipelinemodel_created'  => 'text_timestamp',
      'pipelinemodel_modified' => 'text_timestamp',
      'pipelinemodel_text'     => 'text',
    ],
    'connection' => 'default'
  ];
}
