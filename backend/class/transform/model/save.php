<?php
namespace codename\core\io\transform\model;

use codename\core\exception;

/**
 * Calls save() on a model using a given dataset
 * and returns the last inserted id
 */
class save extends \codename\core\io\transform\model {

  /**
   * @inheritDoc
   */
  public function internalTransform($value)
  {
    $v = $this->getValue($this->config['data']['source'], $this->config['data']['field'], $value);
    return $this->doSave($v);
  }

  /**
   * performs a normalization and save() using the model
   * returns the last inserted id
   *
   * @param  array  $data [description]
   * @return [type]       [description]
   */
  protected function doSave(array $data) {
    $normalizedData = $this->model->normalizeData($data);

    if(!$this->isDryRun()) {
      // only save, if not a dryRun
      $this->model->save($normalizedData);
    }

    if($pkeyValue = $normalizedData[$this->model->getPrimarykey()] ?? null) {
      // simply return pkey value, as we're doing a save using existing PKEY value
      return $pkeyValue;
    } else {
      if(!$this->isDryRun()) {
        // we can only return an insert ID if we're not in a dry run (see above)
        return $this->model->lastInsertId();
      }
    }

    return 'dry-run';
  }

}
