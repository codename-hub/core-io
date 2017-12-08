<?php namespace codename\core\io;

use \codename\core\errorstack;

/**
 * abstract process (for pre- and postprocessing)
 */
abstract class process
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
    $this->errorstack = new errorstack('PROCESS');
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
  private $pipelineInstance = null;

  /**
   * [getPipelineInstance description]
   * @return \codename\core\io\pipeline [description]
   */
  protected function getPipelineInstance() : \codename\core\io\pipeline {
    return $this->pipelineInstance;
  }

  /**
   * [isDryRun description]
   * @return bool [description]
   */
  protected function isDryRun() : bool {
    return $this->pipelineInstance->getDryRun();
  }

  /**
   * [setPipelineInstance description]
   * @param  \codename\core\io\pipeline $instance [description]
   */
  public function setPipelineInstance(\codename\core\io\pipeline $instance) {
    $this->pipelineInstance = $instance;
  }

  /**
   * [run description]
   * @return [type] [description]
   */
  public abstract function run();

  /**
   * debug info:
   * measure duration for the transform itself
   * @var [type]
   */
  public $durationMeasured = null;

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
   * returns the specification for this transform
   * @return array
   */
  public abstract function getSpecification() : array;

}
 ?>
