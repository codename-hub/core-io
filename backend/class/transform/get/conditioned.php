<?php

namespace codename\core\io\transform\get;

use codename\core\exception;
use codename\core\io\transform\get;

/**
 * return a value for specific conditions
 */
class conditioned extends get
{
    /**
     * {@inheritDoc}
     * @param mixed $value
     * @return mixed
     * @throws exception
     */
    public function internalTransform(mixed $value): mixed
    {
        // apply filter
        foreach ($this->config['condition'] as $condition) {
            $conditionFieldValue = $this->getValue($condition['source'], $condition['field'], $value);
            // $returnFieldValue = !is_array($condition['return']) ? $condition['return'] : ($this->getValue($condition['return']['source'], $condition['return']['field'], $value));
            $comparisonValue = !is_array($condition['value']) ? $condition['value'] : ($this->getValue($condition['value']['source'], $condition['value']['field'], $value));
            switch ($condition['operator']) {
                case '=':
                    if ($comparisonValue == $conditionFieldValue) {
                        return !is_array($condition['return']) ? $condition['return'] : ($this->getValue($condition['return']['source'], $condition['return']['field'], $value));
                    }
                    break;
                case '!=':
                    if ($comparisonValue != $conditionFieldValue) {
                        return !is_array($condition['return']) ? $condition['return'] : ($this->getValue($condition['return']['source'], $condition['return']['field'], $value));
                    }
                    break;
                case '>':
                    // NOTE: inverted order
                    if ($comparisonValue < $conditionFieldValue) {
                        return !is_array($condition['return']) ? $condition['return'] : ($this->getValue($condition['return']['source'], $condition['return']['field'], $value));
                    }
                    break;
                case '<':
                    // NOTE: inverted order
                    if ($comparisonValue > $conditionFieldValue) {
                        return !is_array($condition['return']) ? $condition['return'] : ($this->getValue($condition['return']['source'], $condition['return']['field'], $value));
                    }
                    break;
                default:
                    break;
            }
        }

        if (isset($this->config['required']) && $this->config['required']) {
            $this->errorstack->addError('GET_CONDITIONED_MISSING', 0, [
              'config' => $this->config,
              'value' => $value,
            ]);
            return null;
        }

        // no filter match
        //
        // NOTE: $this->config['default'] MAY be FALSE => this should be the value to return in this case (see below)
        //
        if ($this->config['default'] ?? false) {
            return !is_array($this->config['default']) ? $this->config['default'] : ($this->getValue($this->config['default']['source'], $this->config['default']['field'], $value));
        }
        // NULL-coalescing  using default value, which also may be null or not set.
        //
        // default == false   => false
        // default == null    => null
        // default undefined  => null
        //
        return $this->config['default'] ?? null;
    }

    /**
     * {@inheritDoc}
     */
    public function getSpecification(): array
    {
        $sources = [];
        foreach ($this->config['condition'] as $condition) {
            $field = is_array($condition['field']) ? implode('.', $condition['field']) : $condition['field'];
            $sources[] = "{$condition['source']}.$field";
        }

        return [
          'type' => 'transform',
          'source' => $sources,
        ];
    }
}
