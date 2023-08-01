<?php

namespace codename\core\io\datasource;

use codename\core\exception;
use codename\core\io\datasource;
use SplQueue;

/**
 * encapsulates a buffered value
 */
class bufferedValue
{
    /**
     * [public description]
     * @var mixed
     */
    public mixed $value;
    /**
     * [public description]
     * @var int [type]
     */
    public int $progressPosition;

    /**
     * @param mixed $value
     * @param int $progressPosition
     */
    public function __construct(
        mixed $value,
        int $progressPosition
    ) {
        $this->value = $value;
        $this->progressPosition = $progressPosition;
    }
}

/**
 * buffered datasource
 * that encapsulates another datasource
 */
class buffered extends datasource
{
    /**
     * size of the buffer
     * @var int
     */
    protected int $bufferSize = 1;
    /**
     * [protected description]
     * @var datasource
     */
    protected datasource $datasource;
    /**
     * [protected description]
     * @var int [type]
     */
    protected int $progressLimit;
    /**
     * index
     * @var int
     */
    protected int $index = -1;
    /**
     * [protected description]
     * @var SplQueue
     */
    protected SplQueue $bufferQueue;
    /**
     * [protected description]
     * @var mixed
     */
    protected mixed $currentValue = null;

    /**
     * [__construct description]
     * @param datasource $datasource [description]
     * @param int $bufferSize [description]
     * @throws exception
     */
    public function __construct(datasource $datasource, int $bufferSize)
    {
        if ($bufferSize < 1) {
            throw new exception('EXCEPTION_DATASOURCE_BUFFERED_BUFFERSIZE_TOO_LOW', exception::$ERRORLEVEL_ERROR, $bufferSize);
        }
        $this->bufferSize = $bufferSize;
        $this->datasource = $datasource;
        $this->progressLimit = $this->datasource->currentProgressLimit();
        $this->bufferQueue = new SplQueue();
    }

    /**
     * {@inheritDoc}
     */
    public function currentProgressLimit(): int
    {
        return $this->progressLimit ?? 1;
    }

    /**
     * [getBuffer description]
     * @return SplQueue [description]
     */
    public function getBuffer(): SplQueue
    {
        return $this->bufferQueue;
    }

    /**
     * [setBufferSize description]
     * @param int $size [description]
     */
    public function setBufferSize(int $size): void
    {
        $this->bufferSize = $size;
    }

    /**
     * {@inheritDoc}
     */
    public function setConfig(array $config): void
    {
        //
        // passthrough config
        //
        $this->datasource->setConfig($config);
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
        $this->index = -1;
        $this->datasource->rewind();

        // empty the buffer
        while (!$this->bufferQueue->isEmpty()) {
            $this->bufferQueue->dequeue();
        }

        $this->currentValue = null;

        // CHANGED 2021-04-29: missed next() call, leading to inconsistencies
        // when using iteration interfaces
        // NOTE: might cause side effects with several underlying datasource's.
        $this->next();
    }

    /**
     * {@inheritDoc}
     */
    public function next(): void
    {
        if ($this->bufferQueue->isEmpty()) {
            $this->fillBuffer();
        }
        if (!$this->bufferQueue->isEmpty()) {
            $this->currentValue = $this->bufferQueue->dequeue();
            $this->index++;
        } else {
            // end reached
            $this->currentValue = false;
        }
    }

    /**
     * [fillBuffer description]
     * @return void
     */
    protected function fillBuffer(): void
    {
        for ($i = 0; $i < $this->bufferSize; $i++) {
            if ($this->datasource->valid()) {
                $this->bufferQueue->enqueue(
                    new bufferedValue(
                        $this->datasource->current(),
                        $this->datasource->currentProgressPosition()
                    )
                );
                $this->datasource->next();
            } else {
                break;
            }
        }
    }

    /**
     * {@inheritDoc}
     */
    public function valid(): bool
    {
        return $this->currentValue !== false; //  && $this->currentValue !== false && ($this->currentValue->value !== false) || $this->currentValue === null;
    }

    /**
     * {@inheritDoc}
     */
    public function current(): mixed
    {
        return $this->currentValue ? $this->currentValue->value : $this->currentValue;
    }

    /**
     * {@inheritDoc}
     */
    public function currentProgressPosition(): int
    {
        return $this->currentValue ? $this->currentValue->progressPosition : 0;
    }
}
