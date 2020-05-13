<?php
namespace codename\core\io\transform\model\result;

/**
 * [one description]
 */
class one extends \codename\core\io\transform\model\result {

  /**
   * @inheritDoc
   */
  public function internalTransform($value)
  {
    $this->model->saveLastQuery = true;
    $result = $this->doQuery($value);

    // $this->debugInfo = [
    //   'query' => $this->model->getLastQuery(),
    //   'result' => $result
    // ];

    if($result && (count($result) === 1)) {
      return $result[0]; // return the result row
    } else {
      if(isset($this->config['required']) && $this->config['required']) {
          $this->errorstack->addError('model_result_one', 'RESULT_ERROR', [
          'config' => $this->config,
          'value' => $value
        ]);
      }
    }
    return null;
  }

}
