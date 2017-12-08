<?php
namespace codename\core\io\transform\calculate;

/**
 * NOTE:
 * This should be obsolete, as calculate_sum does the same stuff as this one here
 * except you have to specify a signed value (-, to be more exact) to subtract
 * Also, it is quite unclear what gets subtracted from what, especially:
 * Why would you need multiple subtractions in a row?
 */
class subtract extends \codename\core\io\transform\calculate {

  /**
   * the fields
   * @var [type]
   */
  protected $fields;
  /**
   * calculation precision
   * @var int
   */
  protected $precision;

  public function __construct(array $config)
  {
    parent::__construct($config);

    $this->fields = $config['fields'];
    $this->precision = $this->config['precision'] ?? 15;
  }

  /**
   * @inheritDoc
   */
  public function internalTransform($value)
  {
    //
    // NOTE:
    // bcmath uses strings to represent arbitrary precision numbers.
    //
    $sub = null;
    foreach($this->fields as $field) {
      if ($sub === null) {
        $sub = is_array($field) ? $this->getValue($field['source'], $field['field'], $value) : $field;
        continue;
      }
      if(is_array($field)) {
        // different value source (e.g. transform or source/source_deep)
        $sub = bcsub($sub, $this->getValue($field['source'], $field['field'], $value), $this->precision);
      } else {
        // constant value
        $sub = bcsub($sub, $field, $this->precision);
      }
    }
    return $sub;
  }

  /**
   * @inheritDoc
   */
  public function getSpecification(): array
  {
    $sources = [];
    foreach($this->fields as $field) {
      if(!is_array($field)) {
        // bare value
        continue;
      }
      $sources[] = "{$field['source']}.{$field['field']}";
    }
    return [
      'type' => 'transform',
      'source' => $sources
    ];
    return $sources;
  }
}
