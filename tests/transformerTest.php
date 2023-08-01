<?php

namespace codename\core\io\tests;

use codename\core\config\json\extendable;
use codename\core\exception;
use codename\core\io\transform;
use codename\core\io\transformer;
use codename\core\test\base;
use codename\core\test\overrideableApp;
use ReflectionException;

class transformerTest extends base
{
    /**
     * [testTransformerAvailableTransformNames description]
     * @throws ReflectionException
     * @throws exception
     */
    public function testTransformerAvailableTransformNames(): void
    {
        $config = new extendable(__DIR__ . "/" . 'testTransformer1.json');
        $transformer = new transformer($config->get('transform'));

        $result = $transformer->getAvailableTransformNames();
        static::assertEquals(['static_true'], $result);
    }

    /**
     * [testTransformerAvailableTransformNames description]
     * @throws ReflectionException
     * @throws exception
     */
    public function testTransformerAddTransform(): void
    {
        $transformer = new transformer([]);

        $transformer->addTransform('example', [
          'type' => 'value',
          'config' => [
            'value' => 'example',
          ],
        ]);

        $result = $transformer->getTransformInstance('example');
        static::assertInstanceOf(transform::class, $result);
    }

    /**
     * [testTransformerTransformInstance description]
     * @throws ReflectionException
     * @throws exception
     */
    public function testTransformerTransformInstance(): void
    {
        $config = new extendable(__DIR__ . "/" . 'testTransformer1.json');
        $transformer = new transformer($config->get('transform'));

        $result = $transformer->getTransformInstance('static_true');
        static::assertInstanceOf(transform::class, $result);
    }

    /**
     * [testTransformerTransformInstance description]
     * @throws ReflectionException
     * @throws exception
     */
    public function testTransformerWrongTransformInstance(): void
    {
        $this->expectException(exception::class);
        $this->expectExceptionMessage('EXCEPTION_CORE_IO_TRANSFORMER_GETTRANSFORM_NOTFOUND');

        $config = new extendable(__DIR__ . "/" . 'testTransformer1.json');
        $transformer = new transformer($config->get('transform'));

        $transformer->getTransformInstance('example');
    }

    /**
     * {@inheritDoc}
     * @throws ReflectionException
     * @throws exception
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
}
