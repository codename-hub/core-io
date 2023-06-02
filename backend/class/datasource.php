<?php

namespace codename\core\io;

use Iterator;

/**
 * datasource base class
 */
abstract class datasource implements Iterator, progressInterface
{
    /**
     * (re-)configure the datasource
     * @param array $config [datasource config array]
     */
    abstract public function setConfig(array $config);
}
