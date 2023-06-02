<?php

namespace codename\core\io\datasource;

use codename\core\config;
use codename\core\exception;
use codename\core\io\datasource;

/**
 * A datasource that consists of two or more datasource's
 * to be joined upon defined keys.
 * Optionally, the adjacent datasource may be indexed based on key configuration
 */
class joined extends datasource
{
    /**
     * underlying datasource's
     * @var datasource[]
     */
    protected array $datasources = [];
    /**
     * In-memory index
     * @var array
     */
    protected array $indexes = [];
    /**
     * [protected description]
     * @var string|int|null
     */
    protected string|int|null $mainDatasourceKey = null;
    /**
     * [protected description]
     * @var null|config
     */
    protected ?config $config = null;
    /**
     * current array item itself
     * @var bool|array
     */
    protected bool|array $current;
    /**
     * [protected description]
     * @var int
     */
    protected int $currentJoinResultIndex = 0;
    /**
     * [protected description]
     * @var null|int
     */
    protected ?int $currentJoinResultCount = null;
    /**
     * the current position
     * @var int
     */
    protected int $index;

    /**
     * [__construct description]
     * @param array $datasources
     * @param array $config [description]
     * @throws exception
     */
    public function __construct(array $datasources, array $config = [])
    {
        // make an array of files, if it's ONE file.
        if (count($datasources) < 2) {
            throw new exception('JOINED_DATASOURCE_NEEDS_MULTIPLE_INPUT_DATASOURCES', exception::$ERRORLEVEL_ERROR);
        }

        $this->setConfig($config);

        foreach ($datasources as $key => $ds) {
            // We require instances of datasources here
            if (!($ds instanceof datasource)) {
                throw new exception('INVALID_DATASOURCE_INSTANCE_GIVEN', exception::$ERRORLEVEL_ERROR);
            }

            // datasources may be keyed/named
            $this->datasources[$key] = $ds;

            // first entry represents the main datasource
            if ($this->mainDatasourceKey === null) {
                $this->mainDatasourceKey = $key;
            }
        }

        // handle join configs, if defined
        if ($this->config->get('join')) {
            foreach ($this->config->get('join') as $joinIndex => $join) {
                if ($join['index'] ?? false) {
                    // create index for this datasource
                    $ds = $this->datasources[$join['join_datasource']];
                    foreach ($ds as $d) {
                        $indexHashValue = $d[$join['join_field']] ?? null;
                        if ($indexHashValue) {
                            $this->indexes[$joinIndex][$indexHashValue][] = $d;
                        }
                    }
                }
            }
        }
    }

    /**
     * {@inheritDoc}
     */
    public function setConfig(array $config): void
    {
        $this->config = new config($config);
    }

    /**
     * {@inheritDoc}
     */
    public function next(): void
    {
        if ($this->currentJoinResultCount !== null) {
            $this->currentJoinResultIndex++;
        }


        if (($this->currentJoinResultCount !== null) && ($this->currentJoinResultIndex < $this->currentJoinResultCount)) {
            $this->index++;
            return;
        }
        $this->datasources[$this->mainDatasourceKey]->next();

        if ($this->datasources[$this->mainDatasourceKey]->valid()) {
            $this->handleCurrent();
        } else {
            $this->current = false;
        }
    }

    /**
     * {@inheritDoc}
     */
    public function valid(): bool
    {
        return ($this->current !== false);
    }

    /**
     * handles data of the current entry
     * @return void
     */
    protected function handleCurrent(): void
    {
        $current = [$this->datasources[$this->mainDatasourceKey]->current()];

        // Perform joins based on the main datasource first
        $handleDatasources = [$this->mainDatasourceKey];

        $dsAvailable = true;
        $offset = count($handleDatasources) - 1;

        while ($dsAvailable) {
            $dsAvailable = false;

            $dses = [];

            foreach ($handleDatasources as $idx => $dsIdentifier) {
                // skip already handled datasources
                if ($idx < $offset) {
                    continue;
                }

                $offset++;

                // new datasource identifiers that have become available by joining
                $newDatasourceIdentifiers = $this->performJoins($dsIdentifier, $current);

                if (count($newDatasourceIdentifiers) === 0) {
                    continue;
                } else {
                    $dses = array_merge($dses, $newDatasourceIdentifiers);
                }
            }

            $diffDatasourceIdentifiers = array_diff($dses, $handleDatasources);

            // new DSes have become available
            if (count($diffDatasourceIdentifiers) > 0) {
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
     * {@inheritDoc}
     */
    public function current(): mixed
    {
        return $this->current[$this->currentJoinResultIndex] ?? null;
    }

    /**
     * internally joins the various datasources
     * @param int|string $baseDatasourceIdentifier
     * @param array        &$current
     * @return array        an array of handled datasource identifiers
     */
    protected function performJoins(int|string $baseDatasourceIdentifier, array &$current): array
    {
        $handledDatasources = [];
        $joinedResult = [];
        foreach ($this->config->get('join') as $joinIndex => $join) {
            if ($join['base_datasource'] == $baseDatasourceIdentifier) {
                $baseField = $join['base_field'];
                $joinField = $join['join_field'];

                foreach ($current as $c) {
                    if (($c[$baseField] ?? null) === null) {
                        continue;
                    }

                    if ($join['index'] ?? false) {
                        // indexed key/column
                        $indexValues = $this->indexes[$joinIndex][$c[$baseField]] ?? null;
                        if ($indexValues) {
                            foreach ($indexValues as $d) {
                                $joinedResult[] = array_merge($c, $d);
                                // NOTE: dataset multiplication due to join ambiguity possible right here.
                            }
                        }
                    } else {
                        // only joins based on the main datasource key
                        $ds = $this->datasources[$join['join_datasource']];

                        // Un-Indexed variant
                        foreach ($ds as $d) {
                            // TODO NULL handling
                            if ($d[$joinField] == $c[$baseField]) {
                                $joinedResult[] = array_merge($c, $d);
                                // NOTE: dataset multiplication due to join ambiguity possible right here.
                            }
                        }
                    }
                }

                // we might have had no join matches
                // therefore, pass original datasets
                if (count($joinedResult) > 0) {
                    $current = $joinedResult;
                    $joinedResult = [];
                }

                $handledDatasources[] = $join['join_datasource'];
            }
        }
        return $handledDatasources;
    }

    /**
     * {@inheritDoc}
     */
    public function key(): mixed
    {
        return $this->index;
    }

    /**
     * {@inheritDoc}
     */
    public function rewind(): void
    {
        $this->datasources[$this->mainDatasourceKey]->rewind();
        $this->index = 0;
        $this->currentJoinResultIndex = 0;
        $this->currentJoinResultCount = null;
        $this->handleCurrent();
    }

    /**
     * {@inheritDoc}
     */
    public function currentProgressPosition(): int
    {
        return $this->index;
    }

    /**
     * {@inheritDoc}
     */
    public function currentProgressLimit(): int
    {
        return 0; // Cannot be estimated
    }
}
