<?php

namespace codename\core\io\transform;

use codename\core\io\transform;

/**
 * base class for comparisons ( ==, !=, >=, >, <, <= )
 */
abstract class compare extends transform
{
    /**
     * the field to compare
     */
    protected mixed $field;

    /**
     * the value to compare to
     */
    protected mixed $value;

    /**
     * {@inheritDoc}
     */
    public function __construct(array $config)
    {
        parent::__construct($config);
        $this->field = $config['field'] ?? null;
        $this->value = $config['value'] ?? null;
    }
}
