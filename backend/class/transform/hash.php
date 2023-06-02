<?php

namespace codename\core\io\transform;

use codename\core\exception;
use codename\core\io\transform;

/**
 * transform for hashing values
 */
class hash extends transform
{
    /**
     * {@inheritDoc}
     * @param array $config
     * @throws exception
     */
    public function __construct(array $config)
    {
        parent::__construct($config);
        if (!isset($this->config['algorithm'])) {
            throw new exception('EXCEPTION_TRANSFORM_HASH_NO_ALGORITHM_SPECIFIED', exception::$ERRORLEVEL_ERROR);
        }
    }

    /**
     * {@inheritDoc}
     * @param mixed $value
     * @return mixed
     * @throws exception
     */
    public function internalTransform(mixed $value): mixed
    {
        $v = $this->getValue($this->config['source'], $this->config['field'], $value);
        // TODO: handle errors / required state
        return hash($this->config['algorithm'], $v);
    }

    /**
     * {@inheritDoc}
     */
    public function getSpecification(): array
    {
        return [
          'type' => 'transform',
          'source' => ["{$this->config['source']}.{$this->config['field']}"],
        ];
    }
}
