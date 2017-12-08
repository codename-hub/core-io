<?php
namespace codename\core\io\transform\model\result;

/**
 * [all description]
 */
class all extends \codename\core\io\transform\model\result {

  /**
   * @inheritDoc
   */
  public function internalTransform($value)
  {
    $this->model->saveLastQuery = true;
    $result = $this->doQuery($value);

    $this->debugInfo = [
      'query' => $this->model->getLastQuery(),
      'result' => $result
    ];

    return $result;
  }

}
