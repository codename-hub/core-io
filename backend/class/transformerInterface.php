<?php

namespace codename\core\io;

interface transformerInterface
{
    /**
     * [getTransformInstance description]
     * @param string $name [description]
     * @return transform       [description]
     */
    public function getTransformInstance(string $name): transform;
}
