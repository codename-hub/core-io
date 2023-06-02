<?php

namespace codename\core\io\target;

use codename\core\value\structure;

/**
 * defines the interface for targets
 * that internally produce string (text) result arrays
 */
interface structureResultArrayInterface
{
    /**
     * returns paths to files
     * @return structure[]
     */
    public function getStructureResultArray(): array;
}
