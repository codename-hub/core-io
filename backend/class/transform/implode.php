<?php
namespace codename\core\io\transform;

use codename\core\exception;

/**
 * [implode description]
 */
class implode extends \codename\core\io\transform {

  /**
   * glue
   * @var string
   */
  protected $glue = '';

  /**
   * specific fields
   * @var string[]
   */
  protected $fields = [];

  /**
   * @inheritDoc
   */
  public function __construct(array $config)
  {
    parent::__construct($config);
    $this->glue = $config['glue'] ?? '';
    $this->fields = $config['fields']; // array_flip($config['fields']) ?? [];
    $this->allowConstants = $config['allowConstants'] ?? false;
  }

  /**
   * @inheritDoc
   */
  public function internalTransform($value)
  {
    // IMPORTANT: NOTE the array_flip in the constructor

    // implode all array field (values?) that match the given fields config
    // NOTE: we may need to filter the array_values explicitly after array_intersect_key
    // return implode($this->glue, array_values(array_intersect_key($value, $this->fields)));
    $values = [];
    foreach($this->fields as $field) {
      if(is_array($field)) {
        $values[] = $this->getValue($field['source'], $field['field'], $value);
      } else {
        //
        // CHANGED/ADDED 2019-07-17
        // supply "allowConstants" : true
        // in config to enable using the bare values as array elements
        // instead of trying to retrieve them from the source ($value)
        //
        if($this->allowConstants) {
          $values[] = $field;
        } else {
          // NOTE: fallback to source = source
          $values[] = $value[$field] ?? $this->config['fallbackValue'];
        }
      }
    }
    return implode($this->glue, $values);
  }

  /**
   * @inheritDoc
   */
  public function getSpecification(): array
  {
    $sources = [];

    // TODO: add transform sources
    foreach($this->fields as $field) {
      if(is_array($field)) {
        $sources[] = "{$field['source']}.{$field['field']}";
      } else {
        $sources[] = "source.{$field}";
      }
    }

    return [
      'type' => 'transform',
      'source' => $sources
    ];
  }
}
