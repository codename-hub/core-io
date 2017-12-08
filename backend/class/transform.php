<?php namespace codename\core\io;

use codename\core\exception;
use \codename\core\errorstack;

/**
 * transform base class
 */
abstract class transform
{
  /**
   * [protected description]
   * @var array
   */
  protected $config;

  /**
   * [protected description]
   * @var errorstack
   */
  protected $errorstack;

  /**
   * @param array $config
   */
  public function __construct(array $config)
  {
    $this->errorstack = new errorstack('TRANSFORM');
    $this->config = $config;
    $this->debug = $this->config['debug'] ?? false;
  }

  /**
   * [getErrors description]
   * @return array [description]
   */
  public function getErrors() : array {
    return $this->errorstack->getErrors();
  }

  /**
   * [resetErrors description]
   */
  public function resetErrors () {
    $this->errorstack->reset();
  }

  /**
   * [private description]
   * @var \codename\core\io\pipeline
   */
  protected $pipelineInstance = null;

  /**
   * [isDryRun description]
   * @return bool [description]
   */
  protected function isDryRun() : bool {
    return $this->pipelineInstance->getDryRun();
  }

  /**
   * [setPipelineInstance description]
   * @deprecated [vector] [description]
   * @param  \codename\core\io\pipeline $instance [description]
   */
  public function setPipelineInstance(\codename\core\io\pipeline $instance) {
    $this->pipelineInstance = $instance;
  }

  /**
   * [private description]
   * @var \codename\core\io\transformerInterface
   */
  protected $transformerInstance = null;

  /**
   * [setTransformerInstance description]
   * @param \codename\core\io\transformerInterface $instance [description]
   */
  public function setTransformerInstance(\codename\core\io\transformerInterface $instance) {
    $this->transformerInstance = $instance;
  }

  /**
   * cache hash
   * @var string
   */
  public $cacheHash = null;

  /**
   * cached value
   * @var mixed
   */
  public $cacheValue = null;

  /**
   * [protected description]
   * @var [type]
   */
  protected $cached = false;

  /**
   * sets cache from current value
   * and the instance's config
   * @param mixed $parameters [input parameters - e.g. the current value]
   * @param mixed $cacheValue [the to-be-cached value]
   */
  protected function setCacheValue($parameters, $cacheValue) {
    //$this->cacheHash = $this->getCacheHash($parameters);
    $this->cacheValue = $cacheValue;
    $this->cached = true;
  }

  /**
   * [isCached description]
   * @param  [type] $parameters [description]
   * @return bool               [description]
   */
  protected function isCached($parameters) : bool {
    // return $this->cacheHash !== null ? false : $this->getCacheHash($parameters) == $this->cacheHash;
    return $this->cached;
  }

  /**
   * [getCacheHash description]
   * @param  [type] $parameters [description]
   * @return [type]             [description]
   */
  protected function getCacheHash($parameters) {
    return serialize($parameters);
  }

  /**
   * [reset description]
   */
  public function reset() {
    $this->resetErrors();
    $this->resetCache();
  }

  /**
   * resets the cache
   */
  public function resetCache() {
    $this->cacheHash = null;
    $this->cacheValue = null;
    $this->cached = false;

    if($this->debug) {
      $this->durationMeasured = null;
    }
  }

  /**
   * [transform description]
   * @param mixed       $value    [input value]
   * @return mixed|null [transform result]
   */
  public function transform($value) {
    if($this->isCached($value)) {
      // use cached transform result
      return $this->cacheValue;
    } else {

      if($this->debug || ($this->pipelineInstance && $this->pipelineInstance->debug)) {
        $start = microtime(true);
      }

      //
      // perform the real transform
      //
      $transformResult = $this->internalTransform($value);

      if($this->debug || ($this->pipelineInstance && $this->pipelineInstance->debug)) {
        $end = microtime(true);
        $this->durationMeasured = ($end-$start);
        $this->durationsMeasured[] = $this->durationMeasured;
      }

      //
      // fill instance cache
      //
      $this->setCacheValue($value, $transformResult);

      return $transformResult;
    }
  }

  /**
   * debug info:
   * measure duration for the transform itself
   * @var [type]
   */
  public $durationMeasured = null;

  /**
   * catch multiple durations measured
   * @var float[]
   */
  public $durationsMeasured = [];

  /**
   * debug info, optional
   * @var mixed
   */
  public $debugInfo = null;

  /**
   * debug mode
   * @var bool
   */
  protected $debug = false;

  /**
   * internal transformation
   * override this method to implement
   * the 'real' transform
   *
   * @param mixed       $value    [input value]
   * @return mixed|null [transform result]
   */
  abstract public function internalTransform($value);

  /**
   * retrieve another transform's value
   *
   * @param  string  $name  [name of transform in definition]
   * @param  mixed   $value [current value]
   * @return mixed          [description]
   */
  protected function getTransformValue(string $name, $value) {
    return $this->transformerInstance->getTransformInstance($name)->transform($value);
  }

  /**
   * returns a value from a source (either source, the transform or else)
   * name is required
   * as well as the 'value', which is the current item value we're iterating over/the main parameter
   *
   * @param  string $sourceType [source type]
   * @param  string|array $field  [field name]
   * @param  mixed  $value  [main parameter]
   * @return mixed
   */
  protected function getValue(string $sourceType, $field, $value) {
    if($sourceType == 'source') {
      return $value[$field] ?? null;
    } else if($sourceType == 'source_deep') {
      return \codename\core\io\helper\deepaccess::get($value, $field);
    } else if($sourceType == 'transform') {
      return $this->getTransformValue($field, $value);
    } else if($sourceType == 'transform_deep') {
      $transformField = $field[0];
      $path = array_slice($field, 1);
      $transformed = $this->getTransformValue($transformField, $value);
      if(count($path) > 0) {
        return \codename\core\io\helper\deepaccess::get($transformed, $path);
      } else {
        // only one object path item specified - transform name itself
        return $transformed;
      }
    } else if($sourceType == 'option') {
      // a value from the options
      return $this->pipelineInstance->getOption($field);
    } else if($sourceType == 'constant') {
      if(is_array($field)) {
        return \codename\core\io\helper\deepaccess::get($this->pipelineInstance->getConfig()->get('constants'), $field);
      } else {
        return $this->pipelineInstance->getConfig()->get('constants>'.$field);
      }
    } else if($sourceType == 'erroneous') {
      // a value from the error handling
      throw new \LogicException('Not implemented');
    } else {
      // Error case. Unknown/Invalid source type
      throw new exception('EXCEPTION_TRANSFORM_GETVALUE_INVALID_SOURCE_TYPE', exception::$ERRORLEVEL_ERROR);
    }
  }

  /**
   * returns the specification for this transform
   * @return array
   */
  public abstract function getSpecification() : array;

}
 ?>
