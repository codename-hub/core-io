<?php
namespace codename\core\io\target;

use \codename\core\app;
use codename\core\exception;

/**
 * virtual target (doesnt save anything)
 */
class virtual extends \codename\core\io\target
  implements \codename\core\io\targetModelInterface,
  \codename\core\io\target\virtualTargetInterface  {

  /**
   * [protected description]
   * @var array
   */
  protected $virtualStore = [];

  /**
   * returns data stored virtually in this instance
   * @return array [description]
   */
  public function getVirtualStoreData() : array {
    return $this->virtualStore;
  }

  /**
   * @inheritDoc
   */
  public function setVirtualStoreEnabled(bool $state)
  {
    return;
  }

  /**
   * @inheritDoc
   */
  public function getVirtualStoreEnabled() : bool
  {
    return true;
  }

  /**
   * target model
   * @var \codename\core\model
   */
  protected $model = null;

  /**
   * store method
   * 'save' or 'replace'
   * @var string
   */
  protected $method = 'save';

  /**
   * @param string  $name
   * @param array   $config
   */
  public function __construct(string $name, array $config)
  {
    parent::__construct($name, $config);
    $this->model = app::getModel($config['model'], $config['app'] ?? '', $config['vendor'] ?? '');
    $this->method = $config['method'] ?? 'save';
  }

  /**
   * @inheritDoc
   */
  public function store(array $data) : bool
  {
    if($this->finished) {
      throw new exception('EXCEPTION_CORE_IO_TARGET_VIRTUAL_ALREADY_FINISHED', exception::$ERRORLEVEL_ERROR);
    }
    $this->virtualStore[] = $this->model->normalizeData($data);
    return true;
  }

  /**
   * [getModel description]
   * @return \codename\core\model [description]
   */
  public function getModel() : \codename\core\model{
    return $this->model;
  }

  /**
   * determines the finished status of this target
   * @var bool
   */
  protected $finished = false;

  /**
   * @inheritDoc
   */
  public function finish()
  {
    $this->finished = true;
  }

}
