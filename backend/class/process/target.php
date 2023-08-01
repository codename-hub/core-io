<?php

namespace codename\core\io\process;

use codename\core\exception;
use codename\core\io\process;
use LogicException;
use ReflectionException;

/**
 * [target description]
 */
abstract class target extends process
{
    /**
     * {@inheritDoc}
     */
    public function getSpecification(): array
    {
        throw new LogicException('Not implemented'); // TODO
    }

    /**
     * [getTarget description]
     * @return \codename\core\io\target [description]
     * @throws ReflectionException
     * @throws exception
     */
    protected function getTarget(): \codename\core\io\target
    {
        return $this->getPipelineInstance()->getTarget($this->config['target']);
    }
}
