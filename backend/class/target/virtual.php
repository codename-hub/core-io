<?php

namespace codename\core\io\target;

use codename\core\app;
use codename\core\exception;
use codename\core\io\target;
use codename\core\io\targetModelInterface;
use ReflectionException;

/**
 * virtual target (doesn't save anything)
 */
class virtual extends target implements
    targetModelInterface,
    virtualTargetInterface
{
    /**
     * [protected description]
     * @var array
     */
    protected array $virtualStore = [];
    /**
     * target model
     * @var \codename\core\model
     */
    protected \codename\core\model $model;
    /**
     * store method
     * 'save' or 'replace'
     * @var string
     */
    protected string $method = 'save';
    /**
     * determines the finished status of this target
     * @var bool
     */
    protected bool $finished = false;

    /**
     * @param string $name
     * @param array $config
     * @throws ReflectionException
     * @throws exception
     */
    public function __construct(string $name, array $config)
    {
        parent::__construct($name, $config);
        $this->model = app::getModel($config['model'], $config['app'] ?? '', $config['vendor'] ?? '');
        $this->method = $config['method'] ?? 'save';
    }

    /**
     * [getModel description]
     * @return \codename\core\model [description]
     */
    public function getModel(): \codename\core\model
    {
        return $this->model;
    }

    /**
     * returns data stored virtually in this instance
     * @return array [description]
     */
    public function getVirtualStoreData(): array
    {
        return $this->virtualStore;
    }

    /**
     * {@inheritDoc}
     */
    public function setVirtualStoreEnabled(bool $state): void
    {
    }

    /**
     * {@inheritDoc}
     */
    public function getVirtualStoreEnabled(): bool
    {
        return true;
    }

    /**
     * {@inheritDoc}
     * @param array $data
     * @return bool
     * @throws exception
     */
    public function store(array $data): bool
    {
        if ($this->finished) {
            throw new exception('EXCEPTION_CORE_IO_TARGET_VIRTUAL_ALREADY_FINISHED', exception::$ERRORLEVEL_ERROR);
        }
        $this->virtualStore[] = $this->model->normalizeData($data);
        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function finish(): void
    {
        $this->finished = true;
    }
}
