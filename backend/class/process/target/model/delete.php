<?php

namespace codename\core\io\process\target\model;

use codename\core\exception;
use codename\core\io\process\target\model;
use ReflectionException;

/**
 * [delete description]
 */
class delete extends model
{
    /**
     * {@inheritDoc}
     * @throws ReflectionException
     * @throws exception
     */
    public function run(): void
    {
        $model = $this->getModel();
        // apply filters
        foreach ($this->config['filter'] as $filter) {
            if ($filter['value'] && isset($filter['value']['source'])) {
                if ($filter['value']['source'] == 'transform') {
                    $useValue = $this->getPipelineInstance()->getTransformInstance($filter['value']['field'])->transform(null);
                } else {
                    throw new exception('EXCEPTION_CORE_IO_PROCESS_TARGET_MODEL_DELETE_UNSUPPORTED_SOURCE', exception::$ERRORLEVEL_FATAL, $filter);
                }
            } else {
                $useValue = $filter['value'];
            }
            $model->addFilter($filter['field'], $useValue, $filter['operator']);
        }
        // perform delete. may be a large query result...
        if (!$this->getPipelineInstance()->getDryRun()) {
            $model->delete();
        } else {
            // do nothing
            // app::getResponse()->setData('debug_process', $useValue);
        }
    }
}
