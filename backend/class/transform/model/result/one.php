<?php

namespace codename\core\io\transform\model\result;

use codename\core\exception;
use codename\core\io\transform\model\result;
use ReflectionException;

/**
 * [one description]
 */
class one extends result
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
            return $result[0]; // return the result row
        } elseif (isset($this->config['required']) && $this->config['required']) {
            $this->errorstack->addError('model_result_one', 'RESULT_ERROR', [
              'config' => $this->config,
              'value' => $value,
            ]);
        }
        return null;
    }
}
