<?php

namespace codename\core\io\target\xml;

use codename\core\exception;
use codename\core\io\target\textResultArrayInterface;
use codename\core\io\target\xml;
use ReflectionException;

/**
 * xml text as a target
 * "string" is a reserved keyword.
 */
class text extends xml implements textResultArrayInterface
{
    /**
     * {@inheritDoc}
     * @return array
     * @throws ReflectionException
     * @throws exception
     */
    public function getTextResultArray(): array
    {
        return array_map(function ($item) {
            return new \codename\core\value\text($item);
        }, $this->virtualXmlStore);
    }
}
