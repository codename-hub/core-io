<?php

namespace codename\core\io\transform\compare;

use codename\core\exception;
use codename\core\io\transform\compare;

/**
 * [isequal description]
 */
class number extends compare
{
    /**
     * operator to use
     * @var string
     */
    protected string $operator;
    /**
     * precision for BCMath to use
     * @var int
     */
    protected int $precision;

    /**
     * {@inheritDoc}
     */
    public function __construct(array $config)
    {
        parent::__construct($config);
        $this->operator = $config['operator'];
        $this->precision = $config['precision'];
    }

    /**
     * {@inheritDoc}
     * @param mixed $value
     * @return mixed
     * @throws exception
     */
    public function internalTransform(mixed $value): mixed
    {
        // NOTE: fallback to source, if none defined
        $v = $this->getValue($this->config['source'], $this->config['field'], $value);
        $v2 = is_array($this->value) ? $this->getValue($this->value['source'], $this->value['field'], $value) : $this->value;

        if ($this->operator === '=') {
            return bccomp($v, $v2, $this->precision) === 0;
        } elseif ($this->operator === '!=') {
            return bccomp($v, $v2, $this->precision) !== 0;
        } elseif ($this->operator === '>') {
            return bccomp($v, $v2, $this->precision) === 1;
        } elseif ($this->operator === '<') {
            return bccomp($v, $v2, $this->precision) === -1;
        } elseif ($this->operator === '>=') {
            return bccomp($v, $v2, $this->precision) >= 0;
        } elseif ($this->operator === '<=') {
            return bccomp($v, $v2, $this->precision) <= 0;
        } else {
            throw new exception('INVALID_OPERATOR', exception::$ERRORLEVEL_ERROR, $this->operator);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function getSpecification(): array
    {
        return [
          'type' => 'transform',
            // TODO: implement transform as a source!
          'source' => ["{$this->config['source']}.{$this->config['field']}"],
        ];
    }
}
