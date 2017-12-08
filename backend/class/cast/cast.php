<?php namespace codename\core\io;
use codename\core\value;

abstract class cast
{
    /**
     * [protected description]
     * @var value
     */
    protected $source;

    /**
     * [protected description]
     * @var value
     */
    protected $target;

    abstract function cast();
}
