<?php

namespace codename\core\io;

use codename\core\app;
use codename\core\exception;
use ReflectionException;

/**
 * [transformer description]
 */
class transformer implements transformerInterface
{
    /**
     * [EXCEPTION_CORE_IO_TRANSFORMER_GETTRANSFORM_NOTFOUND description]
     * @var string
     */
    public const EXCEPTION_CORE_IO_TRANSFORMER_GETTRANSFORM_NOTFOUND = 'EXCEPTION_CORE_IO_TRANSFORMER_GETTRANSFORM_NOTFOUND';
    /**
     * [EXCEPTION_CORE_IO_TRANSFORMER_GETTRANSFORM_MISSING_CLASS description]
     * @var string
     */
    public const EXCEPTION_CORE_IO_TRANSFORMER_GETTRANSFORM_MISSING_CLASS = 'EXCEPTION_CORE_IO_TRANSFORMER_GETTRANSFORM_MISSING_CLASS';
    /**
     * configs of the transforms
     * @var array
     */
    protected array $transformConfigs = [];
    /**
     * [protected description]
     * @var transform[]
     */
    protected array $transforms = [];

    /**
     * @param array $transformConfigs
     * @throws ReflectionException
     * @throws exception
     */
    public function __construct(array $transformConfigs)
    {
        $this->transformConfigs = $transformConfigs;
        $this->createTransforms();
    }

    /**
     * resets and creates a new collection of named transforms
     * @return void
     * @throws ReflectionException
     * @throws exception
     */
    protected function createTransforms(): void
    {
        $this->transforms = [];
        foreach ($this->transformConfigs as $name => $transform) {
            $this->transforms[$name] = $this->getTransform($transform);
        }
    }

    /**
     * [getTransform description]
     * @param  [type]    $transformconfig [description]
     * @return transform                  [description]
     * @throws ReflectionException
     * @throws exception
     */
    protected function getTransform($transformconfig): transform
    {
        $class = app::getInheritedClass('transform_' . $transformconfig['type']);
        if (class_exists($class)) {
            $transform = new $class($transformconfig['config']);
            $transform->setTransformerInstance($this);
            return $transform;
        } else {
            throw new exception(self::EXCEPTION_CORE_IO_TRANSFORMER_GETTRANSFORM_MISSING_CLASS, exception::$ERRORLEVEL_ERROR, $transformconfig['type']);
        }
    }

    /**
     * [addTransform description]
     * @param string $name [description]
     * @param array $transformConfig [description]
     * @throws ReflectionException
     * @throws exception
     */
    public function addTransform(string $name, array $transformConfig): void
    {
        $this->transforms[$name] = $this->getTransform($transformConfig);
    }

    /**
     * returns all non-internal transforms (key names)
     * @return string[] [description]
     */
    public function getAvailableTransformNames(): array
    {
        return array_keys(
            array_filter($this->transformConfigs, function ($item) {
                return !isset($item['internal']) || $item['internal'] === false;
            })
        );
    }

    /**
     * get a transform instance
     * by name
     * (has to be already initialized during ->run() )
     *
     * @param string $name [transform name from config]
     * @return transform       [transform instance]
     * @throws exception
     */
    public function getTransformInstance(string $name): transform
    {
        if (!isset($this->transforms[$name])) {
            throw new exception(self::EXCEPTION_CORE_IO_TRANSFORMER_GETTRANSFORM_NOTFOUND, exception::$ERRORLEVEL_ERROR, $name);
        }
        return $this->transforms[$name];
    }
}
