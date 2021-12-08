<?php
namespace codename\core\io\datasource;

use codename\core\exception;

/**
 * A datasource that consists of two or more datasources
 * to be joined upon defined keys.
 * Optionally, the adjacent datasource may be indexed based on key configuration
 */
class joined extends \codename\core\io\datasource
{
  /**
   * underyling datasources
   * @var \codename\core\io\datasource[]
   */
  protected $datasources = [];

  /**
   * [__construct description]
   * @param \codename\core\io\datasource[]   $datasources
   * @param array             $config  [description]
   */
  public function __construct($datasources, array $config = array()) {
    // make an array of files, if it's ONE file.
    if(!is_array($datasources)) {
      throw new exception('JOINED_DATASOURCE_NEEDS_MULTIPLE_INPUT_DATASOURCES', exception::$ERRORLEVEL_ERROR);
    }

    $this->setConfig($config);

    foreach($datasources as $key => $ds) {
      // We require instances of datasources here
      if(!($ds instanceof \codename\core\io\datasource)) {
        throw new exception('INVALID_DATASOURCE_INSTANCE_GIVEN', exception::$ERRORLEVEL_ERROR);
      }

      // datasources may be keyed/named
      $this->datasources[$key] = $ds;

      // first entry represents the main datasource
      if($this->mainDatasourceKey === null) {
        $this->mainDatasourceKey = $key;
      }
    }

    // handle join configs, if defined
    if($this->config->get('join')) {
      foreach($this->config->get('join') as $joinIndex => $join) {
        if($join['index'] ?? false) {
          // create index for this datasource
          $ds = $this->datasources[$join['join_datasource']];
          foreach($ds as $d) {
            $indexHashValue = $d[$join['join_field']] ?? null;
            if($indexHashValue) {
              $this->indexes[$joinIndex][$indexHashValue][] = $d;
            }
          }
        }
      }
    }
  }

  /**
   * In-memory index
   * @var array
   */
  protected $indexes = [];

  /**
   * [protected description]
   * @var string|int
   */
  protected $mainDatasourceKey = null;

  /**
   * [protected description]
   * @var \codename\core\config
   */
  protected $config = null;

  /**
   * @inheritDoc
   */
  public function setConfig(array $config) {
    $this->config = new \codename\core\config($config);
  }

  /**
   * @inheritDoc
   */
  public function current()
  {
    return $this->current[$this->currentJoinResultIndex] ?? null;
  }

  /**
   * current array item itself
   * @var array
   */
  protected $current;

  /**
   * [protected description]
   * @var int
   */
  protected $currentJoinResultIndex = 0;

  /**
   * [protected description]
   * @var int
   */
  protected $currentJoinResultCount = null;

  /**
   * @inheritDoc
   */
  public function next()
  {
    if($this->currentJoinResultCount !== null) {
      $this->currentJoinResultIndex++;
    }


    if(($this->currentJoinResultCount !== null) && ($this->currentJoinResultIndex < $this->currentJoinResultCount)) {
      $this->index++;
      return;
    }
    $this->datasources[$this->mainDatasourceKey]->next();

    if($this->datasources[$this->mainDatasourceKey]->valid()) {
      $this->handleCurrent();
    } else {
      $this->current = false;
    }
  }

  /**
   * handles data of the current entry
   */
  protected function handleCurrent(): void {
    $current = [ $this->datasources[$this->mainDatasourceKey]->current() ];

    // Perform joins based on the main datasource first
    $handleDatasources = [ $this->mainDatasourceKey ];

    $dsAvailable = true;
    $offset = count($handleDatasources) - 1;

    while($dsAvailable) {
      $dsAvailable = false;

      $dses = [];

      foreach($handleDatasources as $idx => $dsIdentifier) {

        // skip already handled datasources
        if($idx < $offset) {
          continue;
        }

        $offset++;

        // new datasource identifiers that have become available by joining
        $newDatasourceIdentifiers = $this->performJoins($dsIdentifier, $current);

        if(count($newDatasourceIdentifiers) === 0) {
          continue;
        } else {
          $dses = array_merge($dses, $newDatasourceIdentifiers);
        }
      }

      $diffDatasourceIdentifiers = array_diff($dses, $handleDatasources);

      // new DSes have become available
      if(count($diffDatasourceIdentifiers) > 0) {
        $handleDatasources = array_merge($handleDatasources, $diffDatasourceIdentifiers);
        $dsAvailable = true;
      }
    }

    $this->currentJoinResultCount = count($current);
    $this->currentJoinResultIndex = 0;
    $this->index++;
    $this->current = $current;
  }

  /**
   * internally joins the various datasources
   * @param  string|int   $baseDatasourceIdentifier
   * @param  array        &$current
   * @return array        an array of handled datasource identifiers
   */
  protected function performJoins($baseDatasourceIdentifier, array &$current): array {
    $handledDatasources = [];
    $joinedResult = [];
    foreach($this->config->get('join') as $joinIndex => $join) {
      if($join['base_datasource'] == $baseDatasourceIdentifier) {

        $baseField = $join['base_field'];
        $joinField = $join['join_field'];

        foreach($current as $c) {
          if(($c[$baseField] ?? null) === null) {
            continue;
          }

          if($join['index'] ?? false) {
            // indexed key/column
            $indexValues = $this->indexes[$joinIndex][$c[$baseField]] ?? null;
            if($indexValues) {
              foreach($indexValues as $d) {
                $joinedResult[] = array_merge($c, $d);
                // NOTE: dataset multiplication due to join ambiguity possible right here.
              }
            }
          } else {
            // only joins based on the main datasource key
            $ds = $this->datasources[$join['join_datasource']];

            // Unindexed variant
            foreach($ds as $d) {
              // TODO NULL handling
              if($d[$joinField] == $c[$baseField]) {
                $joinedResult[] = array_merge($c, $d);
                // NOTE: dataset multiplication due to join ambiguity possible right here.
              }
            }
          }
        }

        // we might have had no join matches
        // therefore, pass original datasets
        if(count($joinedResult) > 0) {
          $current = $joinedResult;
          $joinedResult = [];
        }

        $handledDatasources[] = $join['join_datasource'];
      }
    }
    return $handledDatasources;
  }

  /**
   * the current position
   * @var int
   */
  protected $index;

  /**
   * @inheritDoc
   */
  public function key()
  {
    return $this->index;
  }

  /**
   * @inheritDoc
   */
  public function valid()
  {
    return ($this->current !== FALSE);
  }

  /**
   * @inheritDoc
   */
  public function rewind()
  {
    $this->datasources[$this->mainDatasourceKey]->rewind();
    $this->index = 0;
    $this->currentJoinResultIndex = 0;
    $this->currentJoinResultCount = null;
    $this->handleCurrent();
  }

  /**
   * @inheritDoc
   */
  public function currentProgressPosition(): int
  {
    return $this->index;
  }

  /**
   * @inheritDoc
   */
  public function currentProgressLimit(): int
  {
    return 0; // Cannot be estimated
  }
}
