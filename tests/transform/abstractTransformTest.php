<?php

namespace codename\core\io\tests\transform;

use codename\core\app;
use codename\core\io\transform;
use codename\core\io\transformerInterface;
use codename\core\test\base;
use codename\core\test\overrideableApp;
use Exception;
use ReflectionException;

/**
 * [testRemap description]
 */
abstract class abstractTransformTest extends base implements transformerInterface
{
    /**
     * [protected description]
     * @var array
     */
    protected array $transforms = [];

    /**
     * {@inheritDoc}
     * @param string $name
     * @return transform
     * @throws Exception
     */
    public function getTransformInstance(string $name): transform
    {
        if (!isset($this->transforms[$name])) {
            throw new Exception('Transform not found: ' . $name);
        }
        return $this->transforms[$name];
    }

    /**
     * {@inheritDoc}
     * @throws ReflectionException
     * @throws \codename\core\exception
     */
    protected function setUp(): void
    {
        parent::setUp();
        overrideableApp::__injectApp([
          'vendor' => 'codename',
          'app' => 'core-io',
          'namespace' => '\\codename\\core\\io',
        ]);
        $app = static::createApp();
        $app::getAppstack();
    }

    /**
     * [addTransform description]
     * @param string $name [description]
     * @param string $type [description]
     * @param array $config [description]
     * @return transform
     * @throws Exception
     */
    protected function addTransform(string $name, string $type, array $config): transform
    {
        if ($this->transforms[$name] ?? false) {
            throw new Exception("Transform `$name` already added.");
        }
        $this->transforms[$name] = $this->getTransform($type, $config);
        return $this->transforms[$name];
    }

    /**
     * [getTransform description]
     * @param string $type [description]
     * @param array|null $config [description]
     * @return transform                      [description]
     * @throws ReflectionException
     * @throws \codename\core\exception
     */
    protected function getTransform(string $type, ?array $config = null): transform
    {
        $class = app::getInheritedClass('transform_' . $type);
        if (class_exists($class)) {
            $transform = new $class($config);
            if ($transform instanceof transform) {
                $transform->setTransformerInstance($this);
            } else {
                throw new Exception('Transform has wrong base class: ' . $class);
            }
            return $transform;
        } else {
            throw new Exception('Transform class not found: ' . $class);
        }
    }
}
