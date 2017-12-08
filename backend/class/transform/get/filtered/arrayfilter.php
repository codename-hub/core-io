<?php
namespace codename\core\io\transform\get\filtered;

use \codename\core\io\helper\deepaccess;

/**
 * getter for a filtered array
 */
class arrayfilter extends \codename\core\io\transform\get\filtered {

  /**
   * @inheritDoc
   */
  public function internalTransform($value)
  {
    $v = $this->getValue($this->config['source'], $this->config['field'], $value);
    $path = $this->config['path'] ?? null;

    $filtered = array_filter($v, function($element) use ($path, $value) {

      // we may retrieve an element's sub-key, if path is defined
      $elementValue = $path ? deepaccess::get($element, $path) : $element;

      $res = true;
      // apply filter
      foreach($this->config['filter'] as $filter) {
        $filterValue = is_array($filter['value']) ? $this->getValue($filter['value']['source'], $filter['value']['field'], $value) : $filter['value'];
        switch ($filter['operator']) {
          case '=':
            $res &= ($filterValue == $elementValue);
            break;
          case '!=':
            $res &= ($filterValue != $elementValue);
            break;
          default:
            // TODO: Error - undefined/wrong spec?
            break;
        }
        if(!$res) {
          break;
        }
      }

      return $res;
    });

    // Allow [] => null conversion
    if($this->config['null_if_empty'] ?? false) {
      if(count($filtered) === 0) {
        return null;
      }
    }
    // return only the array values
    if($this->config['force_array'] ?? false) {
      return array_values($filtered);
    }
    return $filtered;
  }
}
