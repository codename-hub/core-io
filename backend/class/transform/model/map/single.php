<?php
namespace codename\core\io\transform\model\map;

/**
 * [single description]
 */
class single extends \codename\core\io\transform\model\map {

  // protected static $counter = 0;

  /**
   * @inheritDoc
   */
  public function internalTransform($value)
  {
    $this->model->saveLastQuery = true;
    $result = $this->doQuery($value);

    if($result && (count($result) === 1)) {
      return $result[0][$this->config['map']]; // return a specific key's value
    } else {

      /*
      echo("<pre>");
      print_r([
        'result' => $result,
        'config' => $this->config,
        'value' => $value
      ]);
      echo("</pre>");
      die("no or multiple results!");
      */
      if(isset($this->config['required']) && $this->config['required']) {
        /*
        self::$counter++;

        echo("<pre>");
        print_r([
          'config' => $this->config,
          'value' => $value
        ]);
        echo("</pre>");

        if(self::$counter == 10) {
          die();
        }
        */

        $this->errorstack->addError($this->config['map'], 'MAP_ERROR', [
          'config' => $this->config,
          'value' => $value
        ]);
      }
    }
    return null;
  }

}
