<?php

namespace codename\core\io\target;

use codename\core\exception;
use codename\core\io\target;
use codename\core\io\targetStoreTagInterface;

/**
 * buffer as a target
 */
abstract class buffered extends target implements targetStoreTagInterface
{
    /**
     * Whether this target supports partial write-outs
     * (buffer flushing)
     * This constant must be overridden in order to use it.
     * @var bool
     */
    public const SupportsPartialWriteout = false;
    /**
     * buffered entries
     * @var array
     */
    protected array $bufferArray = [];
    /**
     * [protected description]
     * @var array
     */
    protected array $tagsArray = [];
    /**
     * buffer size until forced writeout, if SupportsPartialWriteout = true
     * and if bufferSize != null OR > 0
     * @var int|null
     */
    protected $bufferSize = null;

    /**
     * Current count of entries in buffer
     * @var int
     */
    protected int $currentStoredCount = 0;
    /**
     * [protected description]
     * @var bool
     */
    protected bool $finished = false;

    /**
     * {@inheritDoc}
     */
    public function __construct(string $name, array $config)
    {
        parent::__construct($name, $config);
        $this->bufferSize = $this->config['buffer_size'] ?? null;
    }

    /**
     * {@inheritDoc}
     * @param array $data
     * @param array|null $tags
     * @return bool
     * @throws exception
     */
    public function store(array $data, ?array $tags = null): bool
    {
        if ($this->finished) {
            throw new exception('EXCEPTION_CORE_IO_TARGET_BUFFERED_ALREADY_FINISHED', exception::$ERRORLEVEL_ERROR);
        }
        $this->bufferArray[] = $data;
        $this->tagsArray[] = $tags;

        $this->currentStoredCount++;

        //
        // Buffer flush, depending on current size
        //
        if (static::SupportsPartialWriteout && $this->bufferSize && (($this->currentStoredCount % $this->bufferSize) === 0)) {
            // forced write-out
            $this->storeBufferedData();
            $this->bufferArray = []; // clear buffer array to release memory
        }
        return true;
    }

    /**
     * perform the real store routine
     * -> store the buffered data
     *
     * @return void
     */
    abstract protected function storeBufferedData(): void;

    /**
     * {@inheritDoc}
     */
    public function finish(): void
    {
        if (!$this->finished) {
            $this->finished = true;
            $this->storeBufferedData();
        }
    }
}
