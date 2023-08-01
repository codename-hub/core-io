<?php

namespace codename\core\io\target\buffered;

use codename\core\exception;
use codename\core\io\target\buffered;
use codename\core\io\target\createArchiveTrait;
use codename\core\io\target\fileResultArrayInterface;
use codename\core\value\text\fileabsolute;
use ReflectionException;

/**
 * file as a target
 */
abstract class file extends buffered implements fileResultArrayInterface
{
    use createArchiveTrait;

    /**
     * output filename
     * @var null|string
     */
    protected ?string $targetFilePath = null;

    /**
     * [protected description]
     * @var bool
     */
    protected bool $append = false;

    /**
     * count after which we begin a new file
     * @var null|int
     */
    protected ?int $splitCount = null;
    /**
     * [protected description]
     * @var fileabsolute[]
     */
    protected ?array $fileResults = null;

    /**
     * {@inheritDoc}
     */
    public function __construct(string $name, array $config)
    {
        parent::__construct($name, $config);
        $this->targetFilePath = $config['filepath'] ?? null;
        $this->append = $config['append'] ?? false;
        $this->splitCount = $config['split_count'] ?? null;
    }

    /**
     * {@inheritDoc}
     * @return array
     * @throws ReflectionException
     * @throws exception
     */
    public function getFileResultArray(): array
    {
        if ($this->fileResults ?? false) {
            if ($this->config['config']['archive'] ?? false) {
                return $this->archiveResults ?? $this->archiveResults = $this->createArchive();
            } else {
                return $this->fileResults;
            }
        } else {
            // invalid, exception!
        }
        return [];
        // throw new \LogicException('Not implemented'); // TODO
    }

    /**
     * legacy method returning a new file handle using the currently set targetFilePath
     * @return resource
     * @throws exception
     */
    protected function getFileHandle()
    {
        if (!$this->targetFilePath) {
            $this->targetFilePath = $this->getNewFilePath();
        }
        return $this->getNewFileHandle($this->targetFilePath);
    }

    /**
     * returns a new, temporary file path
     * @return false|string [type]                 [description]
     */
    protected function getNewFilePath(): bool|string
    {
        return tempnam(sys_get_temp_dir(), '_bf_');
    }

    /**
     * returns a new file handle for the given filepath
     * to be opened in write or append mode
     * @param string $targetFilePath [description]
     * @return resource               [a file handle resource]
     * @throws exception
     */
    protected function getNewFileHandle(string $targetFilePath)
    {
        $handle = @fopen($targetFilePath, ($this->append ? 'a+' : 'w+'));
        if ($handle === false) {
            throw new exception('CORE_IO_TARGET_BUFFERED_FILE_HANDLE_ERROR', exception::$ERRORLEVEL_ERROR, $targetFilePath);
        }
        return $handle;
    }
}
