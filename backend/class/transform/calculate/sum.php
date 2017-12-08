<?php
namespace codename\core\io\transform\calculate;

/**
 * [sum description]
 */
class sum extends \codename\core\io\transform\calculate {

  /**
   * the fields with summands
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
    $sum = 0;
    foreach($this->fields as $field) {
      if(is_array($field)) {
        // different value source (e.g. transform or source/source_deep)
        $sum = bcadd($sum, $this->getValue($field['source'], $field['field'], $value), $this->precision);
      } else {
        // constant value
        $sum = bcadd($sum, $field, $this->precision);
      }
    }
    return $sum;
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
