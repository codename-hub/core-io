<?php

namespace codename\core\io\transform\model\result;

use codename\core\exception;
use codename\core\io\transform\model\result;
use ReflectionException;

/**
 * [all description]
 */
class all extends result
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

        $this->debugInfo = [
          'query' => $this->model->getLastQuery(),
          'result' => $result,
        ];

        return $result;
    }
}
