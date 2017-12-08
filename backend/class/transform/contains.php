<?php
namespace codename\core\io\transform;

use codename\core\exception;

/**
 * [contains description]
 */
class contains extends \codename\core\io\transform {


  /**
   * @inheritDoc
   */
  public function __construct(array $config)
  {
    parent::__construct($config);
    $this->item = $config['item'];
    $this->collection = $config['collection'];
  }

  /**
   * @inheritDoc
   */
  public function internalTransform($value)
  {
    // NOTE: fallback to source, if none defined
    if(is_array($this->item)) {
      $itemValue = $this->getValue($this->item['source'], $this->item['field'], $value);
    } else {
      $itemValue = $this->item;
    }

    if(is_array($this->collection)) {
      $collectionValue = $this->getValue($this->collection['source'], $this->collection['field'], $value);
    } else {
      throw new exception('TRANSFORM_CONTAINS_COLLECTION_MUST_BE_SOURCE_FIELD_CONFIG', exception::$ERRORLEVEL_ERROR);
    }

    return in_array($itemValue, $collectionValue);
  }

  /**
   * @inheritDoc
   */
  public function getSpecification(): array
  {
    return [
      'type' => 'transform',
      // TODO: implement transform as a source!
      'source' => [ "{$this->item['source']}.{$this->item['field']}", "{$this->collection['source']}.{$this->collection['field']}" ]
    ];
  }

}
