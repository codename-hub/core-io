<?php

namespace codename\core\io\transform\model\map;

use codename\core\exception;
use codename\core\io\transform\model\map;
use ReflectionException;

/**
 * [single description]
 */
class single extends map
{
    /**
     * {@inheritDoc}
     * @param mixed $value
     * @return mixed
     * @throws ReflectionException
     * @throws exception
     */
    public function internalTransform(mixed $value): mixed
    {
        $this->model->saveLastQuery = true;
        $result = $this->doQuery($value);

        if ($result && (count($result) === 1)) {
            return $result[0][$this->config['map']]; // return a specific key's value
        } elseif (isset($this->config['required']) && $this->config['required']) {
            $this->errorstack->addError($this->config['map'], 'MAP_ERROR', [
              'config' => $this->config,
              'value' => $value,
            ]);
        }
        return null;
    }
}
