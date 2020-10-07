<?php
namespace codename\core\io\transform\get\conditioned;

class all extends \codename\core\io\transform\get\conditioned
{
  /**
   * @inheritDoc
   */
  public function internalTransform($value)
  {
    $evalResult = true;

    // apply filter
    foreach($this->config['condition'] as $condition) {
      $conditionFieldValue = $this->getValue($condition['source'], $condition['field'], $value);
      // $returnFieldValue = !is_array($condition['return']) ? $condition['return'] : ($this->getValue($condition['return']['source'], $condition['return']['field'], $value));
      $comparisonValue = !is_array($condition['value']) ? $condition['value'] : ($this->getValue($condition['value']['source'], $condition['value']['field'], $value));
      switch ($condition['operator']) {
        case '=':
          $evalResult &= ($comparisonValue == $conditionFieldValue);
          if(!$evalResult) {
            break 2;
          }
        case '!=':
          $evalResult &= ($comparisonValue != $conditionFieldValue);
          if(!$evalResult) {
            break 2;
          }
        default:
          break;
      }
    }

    if($evalResult) {
      return $this->config['return'] ?? true;
    }

    //
    // if we don't 'return' above
    // we automatically have a falsy result
    //
    if(isset($this->config['required']) && $this->config['required']) {
      $this->errorstack->addError('GET_CONDITIONED_ALL_NOMATCH', 0, [
        'config' => $this->config,
        'value' => $value
      ]);
      return null;
    }

    // no filter match
    return $this->config['default'] ?? null;
  }
}
