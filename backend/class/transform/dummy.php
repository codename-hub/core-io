<?php

namespace codename\core\io\transform;

use codename\core\exception;
use codename\core\io\transform;
use LogicException;

/**
 * dummy transform to access internal pipeline data
 */
class dummy extends transform
{
    /**
     * returns a value from inside the pipeline
     * @param string $sourceType [description]
     * @param $field
     * @param $value
     * @return mixed [type]             [description]
     * @throws exception
     */
    public function getInternalPipelineValue(string $sourceType, $field, $value): mixed
    {
        return $this->getValue($sourceType, $field, $value);
    }

    /**
     * {@inheritDoc}
     */
    public function internalTransform(mixed $value): mixed
    {
        throw new LogicException('Not implemented and shouldn\'t be');
    }

    /**
     * {@inheritDoc}
     */
    public function getSpecification(): array
    {
        throw new LogicException('Not implemented and shouldn\'t be');
    }
}
