<?php

namespace codename\core\io\transform\calculate;

use codename\core\exception;
use codename\core\io\transform\calculate;

/**
 * [multiply description]
 */
class multiply extends calculate
{
    /**
     * calculation precision
     * @var int
     */
    protected int $precision;

    /**
     * {@inheritDoc}
     */
    public function __construct(array $config)
    {
        parent::__construct($config);

        $this->precision = array_key_exists('precision', $this->config) ? $this->config['precision'] : 15;
    }

    /**
     * {@inheritDoc}
     * @param mixed $value
     * @return mixed
     * @throws exception
     */
    public function internalTransform(mixed $value): mixed
    {
        $v = 1;

        foreach ($this->config['factors'] as $factor) {
            if (!is_array($factor)) {
                $v = bcmul($v, $factor, $this->precision);
            } else {
                $v = bcmul($v, $this->getValue($factor['source'] ?? 'source', $factor['field'], $value), $this->precision);
            }
        }

        return $v;
    }

    /**
     * {@inheritDoc}
     */
    public function getSpecification(): array
    {
        $sources = [];
        foreach ($this->config['factors'] as $factor) {
            if (!is_array($factor)) {
                // bare value
                continue;
            }
            $sources[] = "{$factor['source']}.{$factor['field']}";
        }

        return [
          'type' => 'transform',
          'source' => $sources,
        ];
    }
}
