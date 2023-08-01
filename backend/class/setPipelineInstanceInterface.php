<?php

namespace codename\core\io;

/**
 * an interface  to enable
 * ->setPipelineInstance() on an object
 */
interface setPipelineInstanceInterface
{
    /**
     * sets the corresponding pipeline instance
     *
     * @param pipeline $instance [description]
     */
    public function setPipelineInstance(pipeline $instance);
}
