<?php

namespace codename\core\io\tests\transform;

use codename\core\io\datasource\arraydata;
use codename\core\io\pipeline;
use codename\core\io\transform\dummy;
use codename\core\response\cli;
use Exception;
use LogicException;
use ReflectionException;

/**
 * pseudo-pipeline for testing source, source_deep, transform, transform_deep and stuff.
 */
class transformGetValueTest extends abstractTransformTest
{
    /**
     * [testGetValueSource description]
     * @throws \codename\core\exception
     */
    public function testGetValueSource(): void
    {
        $data = [
          'source_key1' => 'abc',
          'source_key2' => '123',
        ];
        $transform = $this->getDummyTransform();
        static::assertEquals('abc', $transform->getInternalPipelineValue('source', 'source_key1', $data));
    }

    /**
     * [getDummyTransform description]
     * @return dummy [description]
     */
    protected function getDummyTransform(): dummy
    {
        $dummy = new dummy([]);
        $dummy->setTransformerInstance($this);
        return $dummy;
    }

    /**
     * [testGetValueSourceDeep description]
     * @throws \codename\core\exception
     */
    public function testGetValueSourceDeep(): void
    {
        $data = [
          'source_key1' => 'abc',
          'source_key2' => '123',
          'source_key3' => [
            'subkey1' => 'hä',
          ],
        ];
        $transform = $this->getDummyTransform();
        static::assertEquals('abc', $transform->getInternalPipelineValue('source_deep', ['source_key1'], $data));
        static::assertEquals('hä', $transform->getInternalPipelineValue('source_deep', ['source_key3', 'subkey1'], $data));
        static::assertEquals($data, $transform->getInternalPipelineValue('source_deep', [], $data));
    }

    /**
     * [testGetValueTransform description]
     * @throws \codename\core\exception
     */
    public function testGetValueTransform(): void
    {
        $data = [
          'source_key1' => 'abc',
          'source_key2' => '123',
        ];
        $transform = $this->getDummyTransform();
        $this->addTransform('transformed_key1', 'pad_left', [
          'source' => 'source',
          'field' => 'source_key1',
          'length' => 10,
          'string' => '#',
        ]);
        static::assertEquals('#######abc', $transform->getInternalPipelineValue('transform', 'transformed_key1', $data));
    }

    /**
     * [testGetValueTransformDeep description]
     * @throws \codename\core\exception
     */
    public function testGetValueTransformDeep(): void
    {
        $data = [
          'source_key1' => 'abc',
          'source_key2' => '123',
        ];
        $transform = $this->getDummyTransform();
        $this->addTransform('transformed_object1', 'get_valuearray', [
          'elements' => [
            'key1' => ['source' => 'source', 'field' => 'source_key1'],
            'key2' => ['source' => 'source', 'field' => 'source_key2'],
          ],
        ]);
        static::assertEquals(['key1' => 'abc', 'key2' => '123'], $transform->getInternalPipelineValue('transform_deep', ['transformed_object1'], $data));
        static::assertEquals('abc', $transform->getInternalPipelineValue('transform_deep', ['transformed_object1', 'key1'], $data));
    }

    /**
     * [testGetValueInvalidType description]
     */
    public function testGetValueInvalidType(): void
    {
        $this->expectException(\codename\core\exception::class);
        $this->expectExceptionMessage('EXCEPTION_TRANSFORM_GETVALUE_INVALID_SOURCE_TYPE');
        $transform = $this->getDummyTransform();
        $transform->getInternalPipelineValue('invalid', 'somekey', []);
    }

    /**
     * [testGetValueNonexistingSourceKey description]
     * @throws \codename\core\exception
     */
    public function testGetValueNonexistingSourceKey(): void
    {
        $transform = $this->getDummyTransform();
        static::assertNull($transform->getInternalPipelineValue('source', 'nonexisting', []));
    }

    /**
     * [testGetValueNonexistingSourceDeepKey description]
     * @throws \codename\core\exception
     */
    public function testGetValueNonexistingSourceDeepKey(): void
    {
        $transform = $this->getDummyTransform();
        static::assertNull($transform->getInternalPipelineValue('source_deep', ['nonexisting'], []));
    }

    /**
     * [testGetValueNonexistingTransformKey description]
     * @throws \codename\core\exception
     */
    public function testGetValueNonexistingTransformKey(): void
    {
        $this->expectException(Exception::class);
        // NOTE: exception test from abstractTransformTest class
        $this->expectExceptionMessage('Transform not found: nonexisting');
        $transform = $this->getDummyTransform();
        $transform->getInternalPipelineValue('transform', 'nonexisting', []);
    }

    /**
     * [testGetValueNonexistingTransformDeepKey description]
     * @throws \codename\core\exception
     */
    public function testGetValueNonexistingTransformDeepKey(): void
    {
        $this->expectException(Exception::class);
        // NOTE: exception test from abstractTransformTest class
        $this->expectExceptionMessage('Transform not found: nonexisting');
        $transform = $this->getDummyTransform();
        $transform->getInternalPipelineValue('transform_deep', ['nonexisting'], []);
    }

    /**
     * [testGetValueErroneous description]
     * @throws \codename\core\exception
     */
    public function testGetValueErroneous(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Not implemented');
        $transform = $this->getDummyTransform();
        //
        // 'erroneous' type is not implemented to be accessed this way
        // Make sure we get an exception if we try to do it anyway.
        //
        $transform->getInternalPipelineValue('erroneous', 'abc', []);
    }

    /**
     * [testGetValueConstant description]
     * @throws ReflectionException
     * @throws \codename\core\exception
     */
    public function testGetValueConstant(): void
    {
        // Emulate some response-related stuff
        // as pipeline may access it in CLI for stdout
        $app = static::createApp();
        $app::getAppstack();
        $app::__setInstance('response', new cli());

        $pipelineConfig = [
          'constants' => [
            'primitive' => 123,
            'array' => [4, 5, 6],
            'object' => ['a' => 1, 'b' => 2, 'c' => 3],
          ],
          'source' => [],
          'transform' => [
            'dummy' => [
              'type' => 'dummy',
              'config' => [],
            ],
          ],
          'target' => [],
        ];
        $pipeline = new pipeline(null, $pipelineConfig);
        $datasource = new arraydata();
        $datasource->setData([
          [],
        ]);
        $pipeline->setDatasource($datasource);
        $pipeline->run();
        $dummy = $pipeline->getTransformInstance('dummy');
        if ($dummy instanceof dummy) {
            static::assertEquals(123, $dummy->getInternalPipelineValue('constant', 'primitive', []));
            static::assertEquals([4, 5, 6], $dummy->getInternalPipelineValue('constant', 'array', []));
            static::assertEquals(['a' => 1, 'b' => 2, 'c' => 3], $dummy->getInternalPipelineValue('constant', 'object', []));
            static::assertEquals(2, $dummy->getInternalPipelineValue('constant', ['object', 'b'], []));
        } else {
            static::assertInstanceOf(dummy::class, $dummy);
        }
    }
}
