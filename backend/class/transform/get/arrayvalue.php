<?php

namespace codename\core\io\transform\get;

use codename\core\exception;
use codename\core\io\transform\get;

/**
 * getter for array values (via index/key)
 */
class arrayvalue extends get
{
    /**
     * [protected description]
     * @var bool
     */
    protected bool $required;
    /**
     * [protected description]
     * @var string
     */
    protected string $source;
    /**
     * [protected description]
     * @var bool
     */
    protected bool $isSource;
    /**
     * [protected description]
     * @var null|string
     */
    protected ?string $field;
    /**
     * [protected description]
     * @var bool
     */
    protected bool $indexIsArray;
    /**
     * [protected description]
     * @var array|string
     */
    protected array|string $indexValue;
    /**
     * [protected description]
     * @var null|string
     */
    protected ?string $indexSource = null;
    /**
     * [protected description]
     * @var null|string|array
     */
    protected null|string|array $indexField = null;

    /**
     * {@inheritDoc}
     */
    public function __construct(array $config)
    {
        parent::__construct($config);

        $this->source = $this->config['source'] ?? null;
        $this->isSource = $this->config['source'] == 'source';
        $this->field = $this->config['field'] ?? null;
        $this->indexIsArray = is_array($this->config['index']);
        $this->indexValue = $this->config['index'];
        $this->indexSource = $this->config['index']['source'] ?? null;
        $this->indexField = $this->config['index']['field'] ?? null;
        $this->required = isset($this->config['required']) && $this->config['required'];
    }

    /**
     * {@inheritDoc}
     * @param mixed $value
     * @return mixed
     * @throws exception
     */
    public function internalTransform(mixed $value): mixed
    {
        if ($this->isSource) {
            // special case where we need to fetch a complete array
            // and access only an index later on
            if ($this->field) {
                $v = $value[$this->field];
            } else {
                $v = $value;
            }
        } else {
            $v = $this->getValue($this->source, $this->field, $value);
        }

        if ($this->indexIsArray) {
            // dynamic index
            $index = $this->getValue($this->indexSource, $this->indexField, $value);

            if (!($v[$index] ?? false)) {
                if ($this->required) {
                    $this->errorstack->addError('GET_ARRAYVALUE_MISSING', 0, [
                      'config' => $this->config,
                      'value' => $value,
                    ]);
                }
                return null;
            }

            return $v[$index];
        } else {
            if (!($v[$this->indexValue] ?? false)) {
                if ($this->required) {
                    $this->errorstack->addError('GET_ARRAYVALUE_MISSING', 0, [
                      'config' => $this->config,
                      'value' => $value,
                    ]);
                }
                return null;
            }

            return $v[$this->indexValue];
        }
    }

    /**
     * {@inheritDoc}
     */
    public function getSpecification(): array
    {
        return [
          'type' => 'transform',
          'source' => $this->config['source'] == 'source' ? ["{$this->config['source']}.{$this->config['index']}"] : ["{$this->config['source']}.{$this->config['field']}"],
        ];
    }
}
