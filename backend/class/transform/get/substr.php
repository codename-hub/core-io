<?php

namespace codename\core\io\transform\get;

use codename\core\exception;
use codename\core\io\transform\get;

/**
 * [substr description]
 */
class substr extends get
{
    /**
     * start
     * @var int
     */
    protected int $start = 0;
    /**
     * length
     * @var null|int
     */
    protected ?int $length = null;

    /**
     * {@inheritDoc}
     */
    public function __construct(array $config)
    {
        parent::__construct($config);
        $this->start = $config['start'] ?? 0;
        $this->length = $config['length'] ?? null;
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

        if ($this->length !== null) {
            return substr($v, $this->start, $this->length);
        } else {
            return substr($v, $this->start);
        }
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
