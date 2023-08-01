<?php

namespace codename\core\io\transform\model\map\single;

use codename\core\io\transform\model\map\single;

class onetime extends single
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
