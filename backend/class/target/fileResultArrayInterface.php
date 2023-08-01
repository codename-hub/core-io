<?php

namespace codename\core\io\target;

use codename\core\value\text\fileabsolute;

/**
 * defines the interface for targets
 * that internally produce string (text) result arrays
 */
interface fileResultArrayInterface
{
    /**
     * returns paths to files
     * @return fileabsolute[]
     */
    public function getFileResultArray(): array;
}
