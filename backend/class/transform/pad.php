<?php

namespace codename\core\io\transform;

use codename\core\io\transform;

abstract class pad extends transform
{
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
