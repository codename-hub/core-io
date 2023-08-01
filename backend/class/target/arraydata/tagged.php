<?php

namespace codename\core\io\target\arraydata;

use codename\core\exception;
use codename\core\io\target\arraydata;
use codename\core\io\target\structureResultArrayInterface;
use codename\core\io\targetStoreTagInterface;
use codename\core\value\structure;
use ReflectionException;

/**
 * tagged array data as target
 */
class tagged extends arraydata implements targetStoreTagInterface, structureResultArrayInterface
{
    /**
     * buffered entries
     * @var structure[]
     */
    protected array $resultObjects = [];

    /**
     * {@inheritDoc}
     * @param array $data
     * @param array|null $tags
     * @return bool
     * @throws ReflectionException
     * @throws exception
     */
    public function store(array $data, ?array $tags = null): bool
    {
        if ($this->finished ?? false) {
            throw new exception('EXCEPTION_CORE_IO_TARGET_BUFFERED_ALREADY_FINISHED', exception::$ERRORLEVEL_ERROR);
        }

        if ($tags) {
            $tagsChunk = [$tags];
            $this->resultObjects[] = new \codename\core\io\value\structure\tagged($data, $tagsChunk);
        } else {
            $this->resultObjects[] = new structure($data);
        }

        return true;
    }

    /**
     * returns data stored virtually in this instance
     * @return array [description]
     */
    public function getVirtualStoreData(): array
    {
        $result = [];
        foreach ($this->resultObjects as $obj) {
            $result[] = $obj->get();
        }
        return $result;
    }

    /**
     * {@inheritDoc}
     */
    public function getStructureResultArray(): array
    {
        return $this->resultObjects;
    }
}
