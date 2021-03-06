<?php
namespace codename\core\io\transform\model\save;

use codename\core\exception;

/**
 * Calls save() on a model one time per unique dataset
 * and returns the last inserted id (which might be cached)
 * won't be called again for the whole import loop
 */
class unique extends \codename\core\io\transform\model\save {

  /**
   * @inheritDoc
   */
  public function __construct(array $config)
  {
    parent::__construct($config);
    $this->uniqueByFields = $config['unique_by_fields'] ?? null;

    if(!$this->uniqueByFields || count($this->uniqueByFields) === 0) {
      throw new exception('EXCEPTION_TRANSFORM_MODEL_SAVE_UNIQUE_INVALID', exception::$ERRORLEVEL_ERROR, $this->uniqueByFields);
    }
  }

  /**
   * fields (keys) used to determine uniqueness
   * @var string[]
   */
  protected $uniqueByFields = null;

  /**
   * [protected description]
   * @var array
   */
  protected $cachedIds = [];

  /**
   * @inheritDoc
   */
  protected function doSave(array $data)
  {
    $path = [];
    foreach($this->uniqueByFields as $field) {
      // NOTE: what to do on NULL value? does it really work?
      $path[] = $data[$field];
    }
    $key = implode('___', $path);

    if(!($this->cachedIds[$key] ?? false)) {
      $this->cachedIds[$key] = parent::doSave($data);

      if($this->isDryRun()) {
        // append while dryrun, to distinguish values
        $this->cachedIds[$key] .= '-'.count($this->cachedIds);
      }
    }

    return $this->cachedIds[$key];
  }

}
