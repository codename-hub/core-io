<?php

namespace codename\core\io\transform\model;

use codename\core\exception;
use codename\core\io\transform\model;

/**
 * Calls save() on a model using a given dataset
 * and returns the last inserted id
 */
class save extends model
{
    /**
     * {@inheritDoc}
     * @param mixed $value
     * @return mixed
     * @throws exception
     */
    public function internalTransform(mixed $value): mixed
    {
        $v = $this->getValue($this->config['data']['source'], $this->config['data']['field'], $value);
        return $this->doSave($v);
    }

    /**
     * performs a normalization and save() using the model
     * returns the last inserted id
     *
     * @param array $data [description]
     * @return mixed [type]       [description]
     * @throws exception
     */
    protected function doSave(array $data): mixed
    {
        $normalizedData = $this->model->normalizeData($data);

        if (!$this->isDryRun()) {
            // only save, if not a dryRun
            $this->model->save($normalizedData);
        }

        if ($pkeyValue = $normalizedData[$this->model->getPrimaryKey()] ?? null) {
            // simply return pkey value, as we're doing a save using existing PKEY value
            return $pkeyValue;
        } elseif (!$this->isDryRun()) {
            // we can only return an insert ID if we're not in a dry run (see above)
            return $this->model->lastInsertId();
        }

        return 'dry-run';
    }
}
