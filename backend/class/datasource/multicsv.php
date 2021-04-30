<?php
namespace codename\core\io\datasource;
use \codename\core\exception;

/**
 * wraps multiple CSV files seamlessly
 */
class multicsv extends \codename\core\io\datasource
{
  /**
   * underyling csv datasources
   * @var \codename\core\io\datasource\csv[]
   */
  protected $datasources = [];

  /**
   * current datasource/file index
   * @var int
   */
  protected $fileindex = 0;

  /**
   * headed setting for all csv's
   * @var bool
   */
  protected $headed = true;

  /**
   * delimiter for all csv's
   * @var string
   */
  protected $delimiter = ';';

  /**
   * [__construct description]
   * @param string[]|string  $files         [filepath array]
   * @param array             $config       [config]
   */
  public function __construct($files, array $config = array())
  {
    // make an array of files, if it's ONE file.
    if(!is_array($files)) {
      $files = [ $files ];
    }

    $this->setConfig($config);

    $i = 0;
    foreach($files as $file)
    {
      // CHANGED 2021-04-30: we now passthrough the full config to nested datasources
      $subconfig = $this->config->get();
      $this->datasources[$i] = new \codename\core\io\datasource\csv($file, $subconfig);
      $i++;
    }

    $this->fileindex = 0;
  }

  /**
   * [protected description]
   * @var \codename\core\config
   */
  protected $config = null;

  /**
   * @inheritDoc
   */
  public function setConfig(array $config)
  {
    // CHANGED 2021-04-30: we now passthrough the full config to nested datasources
    // and fallback configs to default + store them in a member variable
    $this->delimiter = $config['delimiter'] = $config['delimiter'] ?? ';';
    $this->headed = $config['headed'] = $config['headed'] ?? true;
    $this->config = new \codename\core\config($config);

    foreach($this->datasources as $datasource) {
      // CHANGED 2021-04-30: we now passthrough the full config to nested datasources
      $subconfig = $subconfig = $this->config->get();
      $datasource->setConfig($subconfig);
    }
  }

  /**
   * @inheritDoc
   */
  public function current()
  {
    return $this->datasources[$this->fileindex]->current();
  }

  /**
   * @inheritDoc
   */
  public function next()
  {
    $this->datasources[$this->fileindex]->next();
    if(!$this->datasources[$this->fileindex]->valid()) {
      if ($this->fileindex < (count($this->datasources)-1)) {
        // move on to next datasource
        $this->fileindex++;
      } else {
        // end?
      }
    } else {
    }
    $this->overallKey++;
  }

  /**
   * [protected description]
   * @var int
   */
  protected $overallKey = 0;

  /**
   * @inheritDoc
   */
  public function key()
  {
    return $this->overallKey; // $this->datasources[$this->fileindex]->key();
  }

  /**
   * @inheritDoc
   */
  public function valid()
  {
    return ($this->datasources[$this->fileindex]->current() !== false);
  }

  /**
   * @inheritDoc
   */
  public function rewind()
  {
    $this->fileindex = 0;
    foreach($this->datasources as $ds) {
      $ds->rewind();
    }
    $this->overallKey = 0;
    $this->positions = [];
  }

  /**
   * [protected description]
   * @var int[]
   */
  protected $positions = [];

  /**
   * @inheritDoc
   */
  public function currentProgressPosition(): int
  {
    $this->positions[$this->fileindex] = $this->datasources[$this->fileindex]->currentProgressPosition();
    return array_sum($this->positions);
  }

  /**
   * just save the progress limits of the datasources
   * to make sure we don't cause IO-intensive operations somehow
   * @var int|null
   */
  protected $cachedProgressLimit = null;

  /**
   * @inheritDoc
   */
  public function currentProgressLimit(): int
  {
    if(!$this->cachedProgressLimit) {
      $count = 0;
      foreach($this->datasources as $datasource) {
        $count += $datasource->currentProgressLimit();
      }
      $this->cachedProgressLimit = $count;
    }
    return $this->cachedProgressLimit;
  }
}
