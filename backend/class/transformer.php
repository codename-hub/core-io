<?php
namespace codename\core\io;

use codename\core\app;
use codename\core\exception;
use codename\core\io\transform;

/**
 * [transformer description]
 */
class transformer implements \codename\core\io\transformerInterface {

  /**
   * @param array $transformConfigs
   */
  public function __construct(array $transformConfigs)
  {
    $this->transformConfigs = $transformConfigs;
    $this->createTransforms();
  }

  /**
   * [addTransform description]
   * @param string $name            [description]
   * @param array  $transformConfig [description]
   */
  public function addTransform(string $name, array $transformConfig) {
    $this->transforms[$name] = $this->getTransform($transformConfig);
  }

  /**
   * returns all non-internal transforms (key names)
   * @return string[] [description]
   */
  public function getAvailableTransformNames() : array {
    return array_keys(array_filter($this->transformConfigs, function($item) {
      return !isset($item['internal']) || $item['internal'] === false;
    }));
  }

  /**
   * configs of the transforms
   * @var array
   */
  protected $transformConfigs = [];

  /**
   * [protected description]
   * @var transform[]
   */
  protected $transforms = [];

  /**
   * resets and creates a new collection of named transforms
   * @return void
   */
  protected function createTransforms() {
    $this->transforms = [];
    foreach($this->transformConfigs as $name => $transform) {
      $this->transforms[$name] = $this->getTransform($transform);
    }
  }

  /**
   * [getTransform description]
   * @param  [type]    $transformconfig [description]
   * @return \codename\core\io\transform                  [description]
   */
  protected function getTransform($transformconfig) : \codename\core\io\transform {
    $class = app::getInheritedClass('transform_' . $transformconfig['type']);
    if(class_exists($class)) {
      $transform = new $class($transformconfig['config']);
      $transform->setTransformerInstance($this);
      return $transform;
    } else {
      throw new exception(self::EXCEPTION_CORE_IO_TRANSFORMER_GETTRANSFORM_MISSING_CLASS, exception::$ERRORLEVEL_ERROR, $transformconfig['type']);
    }
  }

  /**
   * get a transform instance
   * by name
   * (has to be already initialized during ->run() )
   *
   * @param  string   $name [transform name from config]
   * @return \codename\core\io\transform       [transform instance]
   */
  public function getTransformInstance(string $name) : \codename\core\io\transform {
    if(!isset($this->transforms[$name])) {
      throw new exception(self::EXCEPTION_CORE_IO_TRANSFORMER_GETTRANSFORM_NOTFOUND, exception::$ERRORLEVEL_ERROR, $name);
    }
    return $this->transforms[$name];
  }

  /**
   * [EXCEPTION_CORE_IO_TRANSFORMER_GETTRANSFORM_NOTFOUND description]
   * @var string
   */
  const EXCEPTION_CORE_IO_TRANSFORMER_GETTRANSFORM_NOTFOUND = 'EXCEPTION_CORE_IO_TRANSFORMER_GETTRANSFORM_NOTFOUND';

  /**
   * [EXCEPTION_CORE_IO_TRANSFORMER_GETTRANSFORM_MISSING_CLASS description]
   * @var string
   */
  const EXCEPTION_CORE_IO_TRANSFORMER_GETTRANSFORM_MISSING_CLASS = 'EXCEPTION_CORE_IO_TRANSFORMER_GETTRANSFORM_MISSING_CLASS';

}
