<?php
namespace codename\core\io\datasource;

use codename\core\app;

/**
 * database datasource
 */
class database extends \codename\core\io\datasource
  implements \codename\core\io\setPipelineInstanceInterface
{

  /**
   * [__construct description]
   * @param array $config [description]
   */
  public function __construct(array $config = array())
  {
    $this->setConfig($config);
  }

  /**
   * pipeline instance this target is connected to (optional)
   * @var \codename\core\io\pipeline
   */
  protected $pipelineInstance = null;

  /**
   * @inheritDoc
   */
  public function setPipelineInstance(\codename\core\io\pipeline $instance)
  {
    $this->pipelineInstance = $instance;
  }

  /**
   * [protected description]
   * @var \codename\core\database
   */
  protected $database = null;

  /**
   * @inheritDoc
   */
  public function setConfig(array $config)
  {
    if($this->pipelineInstance) {
      $databaseConfig = $this->pipelineInstance->getOption('database_config') ?? [];
      // merge with config
      $config = array_replace($config, $databaseConfig);
    }
    
    if($config['driver'] ?? false) {
      $dbClass = app::getInheritedClass('database_'.$config['driver']);
      $this->database = new $dbClass($config);

      // use buffered queries when using MYSQL
      // this should reduce memory usage BY A HUGE AMOUNT!
      if ($this->database->getConnection()->getAttribute(\PDO::ATTR_DRIVER_NAME) == 'mysql') {
        $this->database->getConnection()->setAttribute(\PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, true);
      }
    }

    if($query = $config['query'] ?? false) {
      $this->setQuery($query);
    }

    if($this->offsetBuffering = $config['offset_buffering'] ?? false) {
      $this->offsetBufferSize = $config['offset_buffer_size'] ?? 100;
    }
  }

  /**
   * offset-based result buffering
   * @var bool
   */
  protected $offsetBuffering = false;

  /**
   * size of the offset buffer
   * @var int
   */
  protected $offsetBufferSize = null;

  /**
   * current SQL query used
   * @var string
   */
  protected $query = null;

  /**
   * [setQuery description]
   * @param string $sql [description]
   */
  public function setQuery(string $sql) {
    $this->query = $sql;
  }

  /**
   * [protected description]
   * @var array
   */
  protected $currentResult = false;

  /**
   * @inheritDoc
   */
  public function current()
  {
    return $this->currentResult;
  }

  /**
   * @inheritDoc
   */
  public function next()
  {
    $this->currentResult = $this->result->fetch();

    if($this->offsetBuffering) {
      if(!$this->valid()) {
        // try next (auto-offsetting)
        $this->result = $this->database->getConnection()->query($this->getQuery());
        $this->currentResult = $this->result->fetch();

        // DEBUG:
        // if($this->valid()) {
        //   $this->currentResult['___offset_buffer_jump'] = $this->rowId;
        // }
      }

      // DEBUG:
      // if($this->valid()) {
      //   $this->currentResult['___offset_buffer_rowid'] = $this->rowId;
      // }
    }

    $this->rowId++;
  }

  /**
   * @inheritDoc
   */
  public function key()
  {
    return $this->rowId;
  }

  /**
   * @inheritDoc
   */
  public function valid()
  {
    return $this->currentResult !== false;
  }

  /**
   * [protected description]
   * @var \PDOStatement
   */
  protected $result = null;

  /**
   * [protected description]
   * @var [type]
   */
  protected $rowId = null;

  /**
   * @inheritDoc
   */
  public function rewind()
  {
    $this->rowId = null;
    $this->result = $this->database->getConnection()->query($this->getQuery());
    $this->next();
  }

  /**
   * [getQuery description]
   * @return string [description]
   */
  protected function getQuery() : string {

    if($this->offsetBuffering) {
      $limit = $this->offsetBufferSize;
      $offset = ($this->rowId ?? 0);
      return $this->query . " LIMIT {$limit} OFFSET {$offset} ";
    } else {
      return $this->query;
    }
  }

  /**
   * @inheritDoc
   */
  public function currentProgressPosition() : int
  {
    return $this->rowId;
  }

  /**
   * @inheritDoc
   */
  public function currentProgressLimit() : int
  {
    return $this->result ? $this->result->rowCount() : 0;
  }


}
