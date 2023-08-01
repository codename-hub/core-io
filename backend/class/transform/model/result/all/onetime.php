<?php

namespace codename\core\io\transform\model\result\all;

use codename\core\io\transform\model\result\all;

class onetime extends all
{
    /**
     * override resetCache
     * to prevent cache reset
     * and keep the cached value
     * until destroyed
     *
     * {@inheritDoc}
     */
    public function resetCache(): void
    {
    }

    /**
     * {@inheritDoc}
     */
    public function resetErrors(): void
    {
    }
}
