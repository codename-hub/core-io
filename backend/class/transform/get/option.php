<?php

namespace codename\core\io\transform\get;

use codename\core\io\transform\get;

/**
 * getter for option values
 */
class option extends get
{
    /**
     * {@inheritDoc}
     */
    public function internalTransform(mixed $value): mixed
    {
        $optionValue = $this->pipelineInstance->getOption($this->config['field']);
        if ($optionValue === null && ($this->config['required'] ?? false)) {
            $this->errorstack->addError('OPTION_VALUE_NULL', 0, [
              'config' => $this->config,
              'value' => $value,
              'option' => $this->config['field'],
            ]);
        }
        return $optionValue;
    }

    /**
     * override resetCache
     * to prevent cache reset
     * and keep the cached value
     * until destroyed
     *
     * {@inheritDoc}
     */
    public function resetCache(): void
    {
    }

    /**
     * {@inheritDoc}
     */
    public function resetErrors(): void
    {
    }

    /**
     * {@inheritDoc}
     */
    public function getSpecification(): array
    {
        return [
          'type' => 'transform',
          'source' => ["option.{$this->config['field']}"],
        ];
    }
}
