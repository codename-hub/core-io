<?php

namespace codename\core\io\target;

use codename\core\exception;
use codename\core\io\target;

/**
 * dummy target (doesn't save anything)
 */
class dummy extends target implements virtualTargetInterface
{
    /**
     * [protected description]
     * @var array
     */
    protected array $virtualStore = [];
    /**
     * determines the finished status of this target
     * @var bool
     */
    protected bool $finished = false;

    /**
     * @param string $name
     * @param array $config
     */
    public function __construct(string $name, array $config)
    {
        parent::__construct($name, $config);
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
        $this->virtualStore[] = $data;
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
