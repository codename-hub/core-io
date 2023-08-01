<?php

namespace codename\core\io\target;

use codename\core\io\target;

/**
 * pure array data as target
 */
class arraydata extends target
{
    /**
     * data storage
     * @var array
     */
    protected array $virtualStore = [];

    /**
     * [__construct description]
     * @param string $name [description]
     * @param array $config [description]
     */
    public function __construct(string $name, array $config)
    {
        parent::__construct($name, $config);
    }

    /**
     * {@inheritDoc}
     */
    public function store(array $data): bool
    {
        $this->virtualStore[] = $data;
        return true;
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
    public function finish(): void
    {
        // nothing. lock storing?
    }
}
