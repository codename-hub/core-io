<?php

namespace codename\core\io\transform\model\save;

use codename\core\io\transform\model\save;

/**
 * Calls save() on a model one time
 * and returns the last inserted id
 * won't be called again for the whole import loop
 */
class onetime extends save
{
    /**
     * for this class, we just override the cache reset method
     * to prevent calling internalTransform over and over again.
     *
     * Instead, when we used the save_onetime transform a single time
     * it won't do its internal job again.
     *
     * This may be used for tracking import jobs or so.
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
