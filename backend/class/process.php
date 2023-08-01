<?php

namespace codename\core\io;

use codename\core\errorstack;

/**
 * abstract process (for pre- and postprocessing)
 */
abstract class process
{
    /**
     * debug info:
     * measure duration for the transform itself
     * @var null|int
     */
    public ?int $durationMeasured = null;
    /**
     * debug info, optional
     * @var mixed
     */
    public mixed $debugInfo = null;
    /**
     * [protected description]
     * @var array
     */
    protected array $config;
    /**
     * [protected description]
     * @var errorstack
     */
    protected errorstack $errorstack;
    /**
     * debug mode
     * @var bool
     */
    protected bool $debug = false;
    /**
     * [private description]
     * @var null|pipeline
     */
    private ?pipeline $pipelineInstance = null;

    /**
     * @param array $config
     */
    public function __construct(array $config)
    {
        $this->errorstack = new errorstack('PROCESS');
        $this->config = $config;
        $this->debug = $this->config['debug'] ?? false;
    }

    /**
     * [getErrors description]
     * @return array [description]
     */
    public function getErrors(): array
    {
        return $this->errorstack->getErrors();
    }

    /**
     * [resetErrors description]
     */
    public function resetErrors(): void
    {
        $this->errorstack->reset();
    }

    /**
     * [run description]
     * @return void [type] [description]
     */
    abstract public function run(): void;

    /**
     * returns the specification for this transform
     * @return array
     */
    abstract public function getSpecification(): array;

    /**
     * [getPipelineInstance description]
     * @return pipeline [description]
     */
    protected function getPipelineInstance(): pipeline
    {
        return $this->pipelineInstance;
    }

    /**
     * [setPipelineInstance description]
     * @param pipeline $instance [description]
     */
    public function setPipelineInstance(pipeline $instance): void
    {
        $this->pipelineInstance = $instance;
    }

    /**
     * [isDryRun description]
     * @return bool [description]
     */
    protected function isDryRun(): bool
    {
        return $this->pipelineInstance->getDryRun();
    }
}
