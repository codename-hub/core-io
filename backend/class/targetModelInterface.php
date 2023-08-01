<?php

namespace codename\core\io;

use codename\core\model;

/**
 * defines an interface to access an underlying model instance
 */
interface targetModelInterface
{
    /**
     * @return model
     */
    public function getModel(): model;
}
