<?php

namespace codename\core\io\process\target;

use codename\core\exception;
use codename\core\io\process\target;
use codename\core\io\targetModelInterface;
use ReflectionException;

/**
 * [model description]
 */
abstract class model extends target
{
    /**
     * [getModel description]
     * @return \codename\core\model [description]
     * @throws ReflectionException
     * @throws exception
     */
    protected function getModel(): \codename\core\model
    {
        $target = $this->getTarget();
        if ($target instanceof targetModelInterface) {
            return $target->getModel();
        } else {
            throw new exception('EXCEPTION_CORE_IO_PROCESS_TARGET_MODEL_UNSUPPORTED', exception::$ERRORLEVEL_FATAL, $this->config);
        }
    }
}
