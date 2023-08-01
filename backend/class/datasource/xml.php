<?php

namespace codename\core\io\datasource;

use codename\core\io\datasource;
use Exception;
use SimpleXMLElement;
use SimpleXMLIterator;

/**
 * XML Datasource
 * Query it using XPath Queries
 */
class xml extends datasource
{
    /**
     * [protected description]
     * @var SimpleXMLIterator
     */
    protected SimpleXMLIterator $xmlIterator;

    /**
     * some count caching
     * @var null|int
     */
    protected ?int $itemCount = null;
    /**
     * [protected description]
     * @var SimpleXMLElement[]
     */
    protected ?array $xpathQueryResult = null;
    /**
     * xpath query
     * @see https://msdn.microsoft.com/en-us/library/ms256086(v=vs.110).aspx
     * @var null|string
     */
    protected ?string $xpathQuery = null;
    /**
     * [protected description]
     * @var mixed
     */
    protected mixed $current = false;
    /**
     * [protected description]
     * @var null|bool|SimpleXMLElement
     */
    protected null|bool|SimpleXMLElement $currentRaw = null;
    /**
     * if next had been called on the underlying iterator before
     * @var bool
     */
    protected bool $firstCall = true;
    /**
     * [protected description]
     * @var int|null
     */
    protected ?int $index = null;
    /**
     * @var mixed
     */
    protected mixed $xpathMapping;

    /**
     * [__construct description]
     * @param string $file [description]
     * @param array $config [description]
     * @throws Exception
     */
    public function __construct(string $file = '', array $config = [])
    {
        $this->setConfig($config);
        $this->xmlIterator = new SimpleXMLIterator($file, 0, true);
        $this->xmlIterator->rewind();
    }

    /**
     * {@inheritDoc}
     */
    public function setConfig(array $config): void
    {
        $this->xpathQuery = $config['xpath_query'] ?? null;
        $this->xpathMapping = $config['xpath_mapping'] ?? null;
    }

    /**
     * {@inheritDoc}
     */
    public function rewind(): void
    {
        $this->index = null;
        $this->current = false;
        $this->currentRaw = null;
        $this->doXPathQuery();
        $this->next();
    }

    /**
     * [doXPathQuery description]
     * @return void
     */
    protected function doXPathQuery(): void
    {
        $this->xpathQueryResult = $this->xmlIterator->xpath($this->xpathQuery);
        // TODO: neither count(...) nor ->count() work correctly.
        $this->itemCount = 0;
    }

    /**
     * {@inheritDoc}
     */
    public function next(): void
    {
        if ($this->xpathQueryResult == null) {
            $this->doXPathQuery();
        }

        if ($this->index === null) {
            $this->index = 0;
        } else {
            $this->index++;
        }

        if (isset($this->xpathQueryResult[$this->index])) {
            $this->currentRaw = $this->xpathQueryResult[$this->index];
            $res = [];
            foreach ($this->xpathMapping as $key => $query) {
                $res[$key] = (string)$this->currentRaw->xpath($query)[0];
            }
            $this->current = $res;
        } else {
            $this->currentRaw = false;
            $this->current = false;
        }
    }

    /**
     * {@inheritDoc}
     */
    public function current(): mixed
    {
        return $this->current;
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
    public function valid(): bool
    {
        return $this->current !== false;
    }

    /**
     * {@inheritDoc}
     */
    public function currentProgressPosition(): int
    {
        return $this->index ?? 0;
    }

    /**
     * {@inheritDoc}
     */
    public function currentProgressLimit(): int
    {
        return $this->itemCount ?? 0;
    }
}
