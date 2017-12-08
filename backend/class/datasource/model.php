<?php
namespace codename\core\io\datasource;

use codename\core\app;
use codename\core\exception;

/**
 * model datasource
 */
class model extends \codename\core\io\datasource
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
   * [setModel description]
   * @param \codename\core\model $model [description]
   */
  public function setModel(\codename\core\model $model) {
    if(!$this->model) {
      $this->model = $model;
    } else {
      throw new exception('EXCEPTION_DATASOURCE_MODEL_ALREADY_SET', exception::$ERRORLEVEL_FATAL);
    }
  }

  /**
   * [protected description]
   * @var \codename\core\model
   */
  protected $model = null;

  /**
   * @inheritDoc
   */
  public function setConfig(array $config)
  {
    if($this->pipelineInstance) {
      // $databaseConfig = $this->pipelineInstance->getOption('database_config') ?? [];
      // // merge with config
      // $config = array_replace($config, $databaseConfig);
    }

    if($query = $config['query'] ?? false) {
      $this->setQuery($query);
    }

    if($this->offsetBuffering = $config['offset_buffering'] ?? false) {
      $this->offsetBufferSize = $config['offset_buffer_size'] ?? 100;
    }

    if($modelName = $config['model'] ?? null) {

      // clean connections
      $this->separateDbConnections = [];
      $this->model = $this->buildModelStructure($config);

      // $this->model = \codename\core\app::getModel($modelName);

      //
      // Crazy stuff...
      // if you're doing a model=>model import (same model)
      // you might get into the situation of querying inside a transaction
      // which leads to STRANGE stuff happending.
      // we simply overcome it here by using a separate connection. BAM!
      //
      // if($config['connection_separate'] ?? false) {
      //   // get a non-stored db connection
      //   $db = \codename\core\app::getDb($this->model->getConfig()->get('connection'), false);
      //   $this->model->setConnectionOverride($db);
      // }

      // if($fields = $config['fields'] ?? null) {
      //   $this->model->hideAllFields();
      //   foreach($fields as $field) {
      //     $this->model->addField($field);
      //   }
      // }
      //
      // if($join = $config['join'] ?? null) {
      //   foreach($join as $j) {
      //     $this->model->addModel(\codename\core\app::getModel($j['model']));
      //   }
      // }
    }
  }

  /**
   * [protected description]
   * @var [type]
   */
  protected $separateDbConnections = [];

  /**
   * [buildModelStructure description]
   * @param  array                $config [description]
   * @return \codename\core\model         [description]
   */
  protected function buildModelStructure(array $config) : \codename\core\model {
    $model = app::getModel($config['model'], $config['app'] ?? '', $config['vendor'] ?? '');

    if($config['virtualFieldResult'] ?? false) {
      $model->setVirtualFieldResult(true);
    }

    //
    // Crazy stuff...
    // if you're doing a model=>model import (same model)
    // you might get into the situation of querying inside a transaction
    // which leads to STRANGE stuff happending.
    // we simply overcome it here by using a separate connection. BAM!
    //
    if(($config['connection_separate'] ?? false) || ($this->separateDbConnections[$model->getConfig()->get('connection')] ?? false)) {
      // get a non-stored db connection
      $conn = $model->getConfig()->get('connection');
      // share separate connections, especially for larger joins.
      $db = $this->separateDbConnections[$conn] ?? $this->separateDbConnections[$conn] = \codename\core\app::getDb($conn, false);
      $model->setConnectionOverride($db);
    }

    if($fields = $config['fields'] ?? null) {
      $model->hideAllFields();
      foreach($fields as $field) {
        $model->addField($field);
      }
    }

    if($joins = $config['join'] ?? null) {
      foreach($joins as $join) {
        $joinModel = $this->buildModelStructure($join);
        if(($join['type'] ?? false) === 'collection') {
          $model->addCollectionModel($joinModel, $join['modelfield'] ?? null);
        } else {
          $model->addModel($joinModel);
        }
      }
    }

    return $model;
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
   * current query parameters being used
   * @var array
   */
  protected $query = null;

  /**
   * [setQuery description]
   * @param array $data [description]
   */
  public function setQuery(array $data) {
    $this->query = $data;
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
   * [protected description]
   * @var [type]
   */
  protected $tempRowId = 0;

  /**
   * @inheritDoc
   */
  public function next()
  {
    $this->currentResult = $this->result[$this->tempRowId] ?? false; // $this->result->fetch();

    if($this->offsetBuffering) {
      if(!$this->valid()) {
        // try next (auto-offsetting)
        $this->result = $this->executeModelQuery($this->getQuery()); // // $this->database->getConnection()->query($this->getQuery());
        $this->tempRowId = 0;
        $this->currentResult = $this->result[$this->tempRowId] ?? false; // $this->result->fetch();

        // \codename\core\app::getResponse()->setData('datasource_model_query_result', array_merge(
        //   \codename\core\app::getResponse()->getData('datasource_model_query_result') ?? [],
        //   [
        //     [
        //       'result' => $this->result
        //     ]
        //   ]
        // ));

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

    $this->tempRowId++;
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
    // \codename\core\app::getResponse()->setData('datasource_model_valid_test', array_merge(
    //   \codename\core\app::getResponse()->getData('datasource_model_valid_test') ?? [],
    //   [
    //     [
    //       'tempRowId' => $this->tempRowId,
    //       'rowId' => $this->rowId,
    //       'currentResult' => $this->currentResult
    //     ]
    //   ]
    // ));
    return $this->currentResult !== false;
  }

  /**
   * [protected description]
   * @var array
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

    $this->result = $this->executeModelQuery($this->getQuery());

    // \codename\core\app::getResponse()->setData('datasource_model_query_result', array_merge(
    //   \codename\core\app::getResponse()->getData('datasource_model_query_result') ?? [],
    //   [
    //     [
    //       'result' => $this->result
    //     ]
    //   ]
    // ));

    $this->tempRowId = 0;
    $this->next();
  }

  /**
   * [executeModelQuery description]
   * @param  array $query [description]
   * @return array        [description]
   */
  protected function executeModelQuery(array $query) : array {
    if($filters = $query['filter'] ?? null) {
      foreach($filters as $filter) {
        if($filter['value']['option'] ?? false) {
          $filter['value'] = $this->pipelineInstance->getOption($filter['value']['option']);
        }
        $this->model->addFilter($filter['field'], $filter['value'], $filter['operator'] ?? '=');
      }
    }
    if($filtercollections = $query['filtercollection'] ?? null) {
      foreach($filtercollections as $filtercollection) {
        $filters = $filtercollection['filters'];
        foreach($filters as &$filter) {
          if($filter['value']['option'] ?? false) {
            $filter['value'] = $this->pipelineInstance->getOption($filter['value']['option']);
          }
        }
        $this->model->addFilterCollection($filters, $filtercollection['group_operator'] ?? 'AND', $filtercollection['group_name'] ?? 'default', $filtercollection['conjunction'] ?? null);
      }
    }

    if($order = $query['order'] ?? null) {
      foreach($order as $orderStatement) {
        $this->model->addOrder($orderStatement['field'], $orderStatement['order']);
      }
    }
    if($limit = $query['limit'] ?? null) {
      $this->model->setLimit($limit);
    }
    if($offset = $query['offset'] ?? null) {
      $this->model->setOffset($offset);
    }

    // \codename\core\app::getResponse()->setData('datasource_model', array_merge(
    //   \codename\core\app::getResponse()->getData('datasource_model') ?? [],
    //   [
    //     [
    //       'filters' => $query['filter'] ?? null,
    //       'filtercollections' => $query['filtercollection'] ?? null,
    //       'limit' => $query['limit'] ?? null,
    //       'offset' => $query['offset'] ?? null,
    //     ]
    //   ]
    // ));

    return $this->model->search()->getResult();
  }

  /**
   * [getQuery description]
   * @return array [description]
   */
  protected function getQuery() : array {
    if($this->offsetBuffering) {
      $limit = $this->offsetBufferSize;
      $offset = ($this->rowId ?? 0);

      return array_merge(
        $this->query,
        [
          'limit' => $limit,
          'offset' => $offset
        ]
      );
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
    return $this->result ? count($this->result) : 0;
  }


}
