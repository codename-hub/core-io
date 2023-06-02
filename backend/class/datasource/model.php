<?php

namespace codename\core\io\datasource;

use codename\core\app;
use codename\core\exception;
use codename\core\io\datasource;
use codename\core\io\pipeline;
use codename\core\io\setPipelineInstanceInterface;
use ReflectionException;

/**
 * model datasource
 */
class model extends datasource implements setPipelineInstanceInterface
{
    /**
     * pipeline instance this target is connected to (optional)
     * @var null|pipeline
     */
    protected ?pipeline $pipelineInstance = null;
    /**
     * [protected description]
     * @var null|\codename\core\model
     */
    protected ?\codename\core\model $model = null;
    /**
     * [protected description]
     * @var array [type]
     */
    protected array $separateDbConnections = [];
    /**
     * offset-based result buffering
     * @var bool
     */
    protected bool $offsetBuffering = false;
    /**
     * size of the offset buffer
     * @var null|int
     */
    protected ?int $offsetBufferSize = null;
    /**
     * Total limit, across all buffered runs
     * This emulates a "LIMIT", if offset buffering enabled.
     * @var null|int
     */
    protected ?int $offsetLimit = null;
    /**
     * current query parameters being used
     * @var null|array
     */
    protected ?array $query = null;
    /**
     * [protected description]
     * @var array|bool
     */
    protected array|bool $currentResult = false;
    /**
     * [protected description]
     * @var int
     */
    protected int $tempRowId = 0;
    /**
     * [protected description]
     * @var array|null
     */
    protected ?array $result = null;
    /**
     * [protected description]
     * @var int|null [type]
     */
    protected ?int $rowId = null;

    /**
     * [__construct description]
     * @param array $config [description]
     * @throws ReflectionException
     * @throws exception
     */
    public function __construct(array $config = [])
    {
        $this->setConfig($config);
    }

    /**
     * {@inheritDoc}
     * @param array $config
     * @throws ReflectionException
     * @throws exception
     */
    public function setConfig(array $config): void
    {
        //
        // Allow empty queries to be pre-configured
        //
        if (($query = ($config['query'] ?? false)) || (is_array($query = $config['query'] ?? false))) {
            $this->setQuery($query);
        }

        if ($this->offsetBuffering = $config['offset_buffering'] ?? false) {
            $this->offsetBufferSize = $config['offset_buffer_size'] ?? 100;
            $this->offsetLimit = $config['offset_limit'] ?? null;
        }

        if ($config['model'] ?? null) {
            // clean connections
            $this->separateDbConnections = [];
            $this->model = $this->buildModelStructure($config);
        }
    }

    /**
     * [buildModelStructure description]
     * @param array $config [description]
     * @return \codename\core\model         [description]
     * @throws ReflectionException
     * @throws exception
     */
    protected function buildModelStructure(array $config): \codename\core\model
    {
        $model = app::getModel($config['model'], $config['app'] ?? '', $config['vendor'] ?? '');

        if ($config['virtualFieldResult'] ?? false) {
            $model->setVirtualFieldResult(true);
        }

        //
        // Crazy stuff...
        // if you're doing a model=>model import (same model)
        // you might get into the situation of querying inside a transaction
        // which leads to STRANGE stuff happening.
        // we simply overcome it here by using a separate connection. BAM!
        //
        if (($config['connection_separate'] ?? false) || ($this->separateDbConnections[$model->getConfig()->get('connection')] ?? false)) {
            // get a non-stored db connection
            $conn = $model->getConfig()->get('connection');
            // share separate connections, especially for larger joins.
            $db = $this->separateDbConnections[$conn] ?? $this->separateDbConnections[$conn] = app::getDb($conn, false);
            $model->setConnectionOverride($db);
        }

        if ($fields = $config['fields'] ?? null) {
            $model->hideAllFields();
            foreach ($fields as $field) {
                $model->addField($field);
            }
        }

        //
        // Apply source-based filters & filtercollections as default filter(collections)
        //
        if ($filter = $config['filter'] ?? null) {
            foreach ($filter as $f) {
                $model->addDefaultFilter($f['field'], $f['value'], $f['operator'] ?? '=');
            }
        }
        if ($filtercollection = $config['filtercollection'] ?? null) {
            foreach ($filtercollection as $fc) {
                $model->addDefaultFilterCollection($fc['filters'], $fc['group_operator'] ?? 'AND', $fc['group_name'] ?? 'default', $fc['conjunction'] ?? 'AND');
            }
        }

        if ($joins = $config['join'] ?? null) {
            foreach ($joins as $join) {
                $joinModel = $this->buildModelStructure($join);
                if (($join['type'] ?? false) === 'collection') {
                    $model->addCollectionModel($joinModel, $join['modelfield'] ?? null);
                } else {
                    $model->addModel($joinModel);
                }
            }
        }

        return $model;
    }

    /**
     * {@inheritDoc}
     */
    public function setPipelineInstance(pipeline $instance): void
    {
        $this->pipelineInstance = $instance;
    }

    /**
     * [setModel description]
     * @param \codename\core\model $model [description]
     * @throws exception
     */
    public function setModel(\codename\core\model $model): void
    {
        if (!$this->model) {
            $this->model = $model;
        } else {
            throw new exception('EXCEPTION_DATASOURCE_MODEL_ALREADY_SET', exception::$ERRORLEVEL_FATAL);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function current(): mixed
    {
        return $this->currentResult;
    }

    /**
     * {@inheritDoc}
     */
    public function key(): mixed
    {
        return $this->rowId;
    }

    /**
     * {@inheritDoc}
     * @throws ReflectionException
     * @throws exception
     */
    public function rewind(): void
    {
        $this->rowId = null;

        $this->result = $this->executeModelQuery($this->getQuery());

        $this->tempRowId = 0;
        $this->next();
    }

    /**
     * [executeModelQuery description]
     * @param array $query [description]
     * @return array        [description]
     * @throws ReflectionException
     * @throws exception
     */
    protected function executeModelQuery(array $query): array
    {
        if ($filters = $query['filter'] ?? null) {
            foreach ($filters as $filter) {
                if ($filter['value']['option'] ?? false) {
                    $filter['value'] = $this->pipelineInstance->getOption($filter['value']['option']);
                }
                $this->model->addFilter($filter['field'], $filter['value'], $filter['operator'] ?? '=');
            }
        }
        if ($filtercollections = $query['filtercollection'] ?? null) {
            foreach ($filtercollections as $filtercollection) {
                $filters = $filtercollection['filters'];
                foreach ($filters as &$filter) {
                    if ($filter['value']['option'] ?? false) {
                        $filter['value'] = $this->pipelineInstance->getOption($filter['value']['option']);
                    }
                }
                $this->model->addFilterCollection($filters, $filtercollection['group_operator'] ?? 'AND', $filtercollection['group_name'] ?? 'default', $filtercollection['conjunction'] ?? null);
            }
        }

        if ($order = $query['order'] ?? null) {
            foreach ($order as $orderStatement) {
                $this->model->addOrder($orderStatement['field'], $orderStatement['order']);
            }
        }
        if ($limit = $query['limit'] ?? null) {
            $this->model->setLimit($limit);
        }
        if ($offset = $query['offset'] ?? null) {
            $this->model->setOffset($offset);
        }

        return $this->model->search()->getResult();
    }

    /**
     * [getQuery description]
     * @return array [description]
     */
    protected function getQuery(): array
    {
        if ($this->offsetBuffering) {
            $limit = $this->offsetBufferSize;
            $offset = ($this->rowId ?? 0);

            return array_merge(
                $this->query,
                [
                  'limit' => $limit,
                  'offset' => $offset,
                ]
            );
        } else {
            return $this->query;
        }
    }

    /**
     * [setQuery description]
     * @param array $data [description]
     */
    public function setQuery(array $data): void
    {
        $this->query = $data;
    }

    /**
     * {@inheritDoc}
     * @throws ReflectionException
     * @throws exception
     */
    public function next(): void
    {
        $this->currentResult = $this->result[$this->tempRowId] ?? false;

        if ($this->offsetBuffering) {
            if (!$this->valid()) {
                // try next (auto-offsetting)
                $this->result = $this->executeModelQuery($this->getQuery());
                $this->tempRowId = 0;
                $this->currentResult = $this->result[$this->tempRowId] ?? false;
            }

            if ($this->offsetLimit !== null) {
                if ($this->offsetLimit <= $this->rowId) {
                    $this->currentResult = false; // emulate limit?
                }
            }
        }

        $this->tempRowId++;
        $this->rowId++;
    }

    /**
     * {@inheritDoc}
     */
    public function valid(): bool
    {
        return $this->currentResult !== false;
    }

    /**
     * {@inheritDoc}
     */
    public function currentProgressPosition(): int
    {
        return $this->rowId;
    }

    /**
     * {@inheritDoc}
     */
    public function currentProgressLimit(): int
    {
        return $this->result ? count($this->result) : 0;
    }
}
