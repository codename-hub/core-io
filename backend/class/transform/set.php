<?php

namespace codename\core\io\transform;

use codename\core\io\transform;
use LogicException;

class set extends transform
{
    /**
     * {@inheritDoc}
     */
    public function internalTransform(mixed $value): mixed
    {
        throw new LogicException('Not implemented'); // TODO
    }

    /**
     * {@inheritDoc}
     */
    public function getSpecification(): array
    {
        return [];
    }
}
