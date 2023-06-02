<?php

namespace codename\core\io\datasource;

use codename\core\app;
use codename\core\exception;
use codename\core\io\datasource;
use codename\core\io\pipeline;
use codename\core\io\setPipelineInstanceInterface;
use PDO;
use PDOStatement;
use ReflectionException;

/**
 * database datasource
 */
class database extends datasource implements setPipelineInstanceInterface
{
    /**
     * pipeline instance this target is connected to (optional)
     * @var null|pipeline
     */
    protected ?pipeline $pipelineInstance = null;
    /**
     * [protected description]
     * @var \codename\core\database|null
     */
    protected ?\codename\core\database $database = null;
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
     * current SQL query used
     * @var null|string
     */
    protected ?string $query = null;
    /**
     * [protected description]
     * @var array|bool
     */
    protected array|bool $currentResult = false;
    /**
     * [protected description]
     * @var null|PDOStatement
     */
    protected ?PDOStatement $result = null;
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
        if ($this->pipelineInstance) {
            $databaseConfig = $this->pipelineInstance->getOption('database_config') ?? [];
            // merge with config
            $config = array_replace($config, $databaseConfig);
        }

        if ($config['driver'] ?? false) {
            $dbClass = app::getInheritedClass('database_' . $config['driver']);
            $this->database = new $dbClass($config);

            // use buffered queries when using MYSQL
            // this should reduce memory usage BY A HUGE AMOUNT!
            if ($this->database->getConnection()->getAttribute(PDO::ATTR_DRIVER_NAME) == 'mysql') {
                $this->database->getConnection()->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, true);
            }
        }

        if ($query = $config['query'] ?? false) {
            $this->setQuery($query);
        }

        if ($this->offsetBuffering = $config['offset_buffering'] ?? false) {
            $this->offsetBufferSize = $config['offset_buffer_size'] ?? 100;
        }
    }

    /**
     * {@inheritDoc}
     */
    public function setPipelineInstance(pipeline $instance): void
    {
        $this->pipelineInstance = $instance;
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
     */
    public function rewind(): void
    {
        $this->rowId = null;
        $this->result = $this->database->getConnection()->query($this->getQuery());
        $this->next();
    }

    /**
     * [getQuery description]
     * @return string [description]
     */
    protected function getQuery(): string
    {
        if ($this->offsetBuffering) {
            $limit = $this->offsetBufferSize;
            $offset = ($this->rowId ?? 0);
            return $this->query . " LIMIT $limit OFFSET $offset ";
        } else {
            return $this->query;
        }
    }

    /**
     * [setQuery description]
     * @param string $sql [description]
     */
    public function setQuery(string $sql): void
    {
        $this->query = $sql;
    }

    /**
     * {@inheritDoc}
     */
    public function next(): void
    {
        $this->currentResult = $this->result->fetch();

        if ($this->offsetBuffering) {
            if (!$this->valid()) {
                // try next (auto-offsetting)
                $this->result = $this->database->getConnection()->query($this->getQuery());
                $this->currentResult = $this->result->fetch();
            }
        }

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
        return $this->result ? $this->result->rowCount() : 0;
    }
}
