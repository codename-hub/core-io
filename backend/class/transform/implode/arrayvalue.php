<?php

namespace codename\core\io\transform\implode;

use codename\core\exception;
use codename\core\io\transform;

/**
 * implodes a single source, which is an array for itself
 */
class arrayvalue extends transform
{
    /**
     * @var string
     */
    protected string $glue;
    /**
     * @var string
     */
    protected string $source;
    /**
     * @var string
     */
    protected string $field;

    /**
     * {@inheritDoc}
     */
    public function __construct(array $config)
    {
        parent::__construct($config);
        $this->glue = $config['glue'] ?? '';
        $this->source = $config['source'];
        $this->field = $config['field'];
    }

    /**
     * {@inheritDoc}
     * @param mixed $value
     * @return mixed
     * @throws exception
     */
    public function internalTransform(mixed $value): mixed
    {
        $arrayValue = $this->getValue($this->source, $this->field, $value);
        return implode($this->glue, $arrayValue);
    }

    /**
     * {@inheritDoc}
     */
    public function getSpecification(): array
    {
        $sources = [];
        $sources[] = "$this->source.$this->field";
        return [
          'type' => 'transform',
          'source' => $sources,
        ];
    }
}
