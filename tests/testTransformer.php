<?php
namespace codename\core\io\tests;

use codename\core\tests\base;
use codename\core\tests\overrideableApp;

class testTransformer extends base
{

  /**
   * @inheritDoc
   */
  protected function setUp(): void
  {
    parent::setUp();
    overrideableApp::__injectApp([
      'vendor' => 'codename',
      'app' => 'core-io',
      'namespace' => '\\codename\\core\\io'
    ]);
    $app = static::createApp();
    $app->getAppstack();
  }

  /**
   * [testTransformerAvailableTransformNames description]
   */
  public function testTransformerAvailableTransformNames(): void {
    $config = new \codename\core\config\json\extendable(__DIR__ . "/" . 'testTransformer1.json');
    $transformer = new \codename\core\io\transformer($config->get('transform'));

    $result = $transformer->getAvailableTransformNames();
    $this->assertEquals([ 'static_true' ], $result);

  }

  /**
   * [testTransformerAvailableTransformNames description]
   */
  public function testTransformerAddTransform(): void {
    $transformer = new \codename\core\io\transformer([]);

    $transformer->addTransform('example', [
      'type'    => 'value',
      'config'  => [
        'value' => 'example'
      ],
    ]);

    $result = $transformer->getTransformInstance('example');
    $this->assertInstanceOf(\codename\core\io\transform::class, $result);

  }

  /**
   * [testTransformerTransformInstance description]
   */
  public function testTransformerTransformInstance(): void {
    $config = new \codename\core\config\json\extendable(__DIR__ . "/" . 'testTransformer1.json');
    $transformer = new \codename\core\io\transformer($config->get('transform'));

    $result = $transformer->getTransformInstance('static_true');
    $this->assertInstanceOf(\codename\core\io\transform::class, $result);
  }

  /**
   * [testTransformerTransformInstance description]
   */
  public function testTransformerWrongTransformInstance(): void {
    $this->expectException(\codename\core\exception::class);
    $this->expectExceptionMessage('EXCEPTION_CORE_IO_TRANSFORMER_GETTRANSFORM_NOTFOUND');

    $config = new \codename\core\config\json\extendable(__DIR__ . "/" . 'testTransformer1.json');
    $transformer = new \codename\core\io\transformer($config->get('transform'));

    $result = $transformer->getTransformInstance('example');
  }

}
