<?php

namespace codename\core\io\transform\math;

use codename\core\exception;
use codename\core\io\transform;

class round extends transform
{
    /**
     * calculation precision
     * @var int
     */
    protected int $precision;

    /**
     * round mode (PHP const)
     * @var int
     */
    protected int $mode;

    /**
     * {@inheritDoc}
     * @param array $config
     * @throws exception
     */
    public function __construct(array $config)
    {
        parent::__construct($config);

        //
        // By default, we round to the next integer.
        // in general, this is pow(10, -precision)
        // e.g.
        // precision 1  => 10^(-1)    => 0.1
        // precision 2  => 10^(-2)    => 0.01
        // precision 0  => 10^0       => 1 (stripping off fraction - but will stay a float!)
        // precision -1 => 10^(-(-1)) => 10
        //
        $this->precision = $this->config['precision'] ?? 0;
        $modeString = $this->config['mode'] ?? null;
        if (!$modeString) {
            $this->mode = PHP_ROUND_HALF_UP;
        } else {
            $this->mode = match ($modeString) {
                'half_up', 'financial' => PHP_ROUND_HALF_UP,
                'half_down' => PHP_ROUND_HALF_DOWN,
                'half_even', 'symmetric' => PHP_ROUND_HALF_EVEN,
                'half_odd' => PHP_ROUND_HALF_ODD,
                default => throw new exception('INVALID_ROUND_MODE', exception::$ERRORLEVEL_ERROR, $modeString),
            };
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
        $v = $this->getValue($this->config['source'] ?? 'source', $this->config['field'], $value);
        return round($v, $this->precision, $this->mode);
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
