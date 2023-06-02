<?php

namespace codename\core\io\target;

use codename\core\value\text;

/**
 * defines the interface for targets
 * that internally produce string (text) result (single entry!)
 */
interface textResultInterface
{
    /**
     * [getTextResult description]
     * @return text
     */
    public function getTextResult(): text;
}
