<?php

namespace codename\core\io\target;

use codename\core\value\text;

/**
 * defines the interface for targets
 * that internally produce string (text) result arrays
 */
interface textResultArrayInterface
{
    /**
     * [getTextResult description]
     * @return text[]
     */
    public function getTextResultArray(): array;
}
