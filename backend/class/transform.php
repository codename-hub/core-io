<?php

namespace codename\core\io;

use codename\core\errorstack;
use codename\core\exception;
use codename\core\helper\deepaccess;
use LogicException;

/**
 * transform base class
 */
abstract class transform
{
    /**
     * cache hash
     * @var null|string
     */
    public ?string $cacheHash = null;
    /**
     * cached value
     * @var mixed
     */
    public mixed $cacheValue = null;
    /**
     * debug info:
     * measure duration for the transform itself
     * @var float|null [type]
     */
    public ?float $durationMeasured = null;
    /**
     * catch multiple durations measured
     * @var float[]
     */
    public array $durationsMeasured = [];
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
     * [private description]
     * @var null|pipeline
     */
    protected ?pipeline $pipelineInstance = null;
    /**
     * [private description]
     * @var null|transformerInterface
     */
    protected ?transformerInterface $transformerInstance = null;
    /**
     * [protected description]
     * @var bool [type]
     */
    protected bool $cached = false;
    /**
     * debug mode
     * @var bool
     */
    protected bool $debug = false;

    /**
     * @param array $config
     */
    public function __construct(array $config)
    {
        $this->errorstack = new errorstack('TRANSFORM');
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
     * @return void
     */
    public function resetErrors(): void
    {
        $this->errorstack->reset();
    }

    /**
     * @return void
     */
    public function reset(): void
    {
        $this->resetErrors();
        $this->resetCache();
    }

    /**
     * resets the cache
     * @return void
     */
    public function resetCache(): void
    {
        $this->cacheHash = null;
        $this->cacheValue = null;
        $this->cached = false;

        if ($this->debug) {
            $this->durationMeasured = null;
        }
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
     * [setTransformerInstance description]
     * @param transformerInterface $instance [description]
     */
    public function setTransformerInstance(transformerInterface $instance): void
    {
        $this->transformerInstance = $instance;
    }

    /**
     * returns the specification for this transform
     * @return array
     */
    abstract public function getSpecification(): array;

    /**
     * [isDryRun description]
     * @return bool [description]
     */
    protected function isDryRun(): bool
    {
        return $this->pipelineInstance->getDryRun();
    }

    /**
     * [getCacheHash description]
     * @param mixed $parameters [description]
     * @return string [type]             [description]
     */
    protected function getCacheHash(mixed $parameters): string
    {
        return serialize($parameters);
    }

    /**
     * returns a value from a source (either source, the transform or else)
     * name is required
     * as well as the 'value', which is the current item value we're iterating over/the main parameter
     *
     * @param string $sourceType [source type]
     * @param array|string $field [field name]
     * @param mixed $value [main parameter]
     * @return mixed
     * @throws exception
     */
    protected function getValue(string $sourceType, array|string $field, mixed $value): mixed
    {
        if ($sourceType == 'source') {
            return $value[$field] ?? null;
        } elseif ($sourceType == 'source_deep') {
            return deepaccess::get($value, $field);
        } elseif ($sourceType == 'transform') {
            return $this->getTransformValue($field, $value);
        } elseif ($sourceType == 'transform_deep') {
            $transformField = $field[0];
            $path = array_slice($field, 1);
            $transformed = $this->getTransformValue($transformField, $value);
            if (count($path) > 0) {
                return deepaccess::get($transformed, $path);
            } else {
                // only one object path item specified - transform name itself
                return $transformed;
            }
        } elseif ($sourceType == 'option') {
            // a value from the options
            return $this->pipelineInstance->getOption($field);
        } elseif ($sourceType == 'constant') {
            if (is_array($field)) {
                return deepaccess::get($this->pipelineInstance->getConfig()->get('constants'), $field);
            } else {
                return $this->pipelineInstance->getConfig()->get('constants>' . $field);
            }
        } elseif ($sourceType == 'erroneous') {
            // a value from the error handling
            throw new LogicException('Not implemented');
        } else {
            // Error case. Unknown/Invalid source type
            throw new exception('EXCEPTION_TRANSFORM_GETVALUE_INVALID_SOURCE_TYPE', exception::$ERRORLEVEL_ERROR);
        }
    }

    /**
     * retrieve another transform's value
     *
     * @param string $name [name of transform in definition]
     * @param mixed $value [current value]
     * @return mixed          [description]
     */
    protected function getTransformValue(string $name, mixed $value): mixed
    {
        return $this->transformerInstance->getTransformInstance($name)->transform($value);
    }

    /**
     * [transform description]
     * @param mixed $value [input value]
     * @return mixed|null [transform result]
     */
    public function transform(mixed $value): mixed
    {
        if ($this->isCached($value)) {
            // use cached transform result
            return $this->cacheValue;
        } else {
            $start = null;
            if ($this->debug || ($this->pipelineInstance && $this->pipelineInstance->debug)) {
                $start = microtime(true);
            }

            //
            // perform the real transform
            //
            $transformResult = $this->internalTransform($value);

            if ($this->debug || ($this->pipelineInstance && $this->pipelineInstance->debug)) {
                $this->durationMeasured = (microtime(true) - $start);
                $this->durationsMeasured[] = $this->durationMeasured;
            }

            //
            // fill instance cache
            //
            $this->setCacheValue($value, $transformResult);

            return $transformResult;
        }
    }

    /**
     * @param mixed $parameters
     * @return bool
     */
    protected function isCached(mixed $parameters): bool
    {
        return $this->cached;
    }

    /**
     * internal transformation
     * override this method to implement
     * the 'real' transform
     *
     * @param mixed $value [input value]
     * @return mixed|null [transform result]
     */
    abstract public function internalTransform(mixed $value): mixed;

    /**
     * sets cache from current value
     * and the instance's config
     * @param mixed $parameters [input parameters - e.g. the current value]
     * @param mixed $cacheValue [the to-be-cached value]
     */
    protected function setCacheValue(mixed $parameters, mixed $cacheValue): void
    {
        $this->cacheValue = $cacheValue;
        $this->cached = true;
    }
}
