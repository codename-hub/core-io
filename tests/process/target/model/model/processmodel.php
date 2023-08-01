<?php

namespace codename\core\io\tests\process\target\model\model;

use codename\core\test\sqlModel;

/**
 * SQL Base model leveraging the new model servicing modules
 * and enables freely defining and loading model configs
 */
class processmodel extends sqlModel
{
    /**
     * static configuration
     * for usage in unit tests
     * @var array
     */
    public static array $staticConfig = [
      'field' => [
        'processmodel_id',
        'processmodel_created',
        'processmodel_modified',
        'processmodel_text',
      ],
      'primary' => [
        'processmodel_id',
      ],
      'datatype' => [
        'processmodel_id' => 'number_natural',
        'processmodel_created' => 'text_timestamp',
        'processmodel_modified' => 'text_timestamp',
        'processmodel_text' => 'text',
      ],
      'connection' => 'default',
    ];

    /**
     * {@inheritDoc}
     */
    public function __construct(array $modeldata = [])
    {
        parent::__construct('processtest', 'processmodel', static::$staticConfig);
    }
}
