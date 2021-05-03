<?php
namespace codename\core\io\tests\transform;

use codename\core\app;

use codename\core\io\transformerInterface;

use codename\core\tests\overrideableApp;

/**
 * [testRemap description]
 */
abstract class abstractTransformTest extends \codename\core\tests\base
  implements transformerInterface
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
   * [protected description]
   * @var \codename\core\io\transform[]
   */
  protected $transforms = [];

  /**
   * @inheritDoc
   */
  public function getTransformInstance(string $name): \codename\core\io\transform
  {
    if(!isset($this->transforms[$name])) {
      throw new \Exception('Transform not found: '.$name);
    }
    return $this->transforms[$name];
  }

  /**
   * [addTransform description]
   * @param string $name   [description]
   * @param string $type   [description]
   * @param array  $config [description]
   * @return \codename\core\io\transform
   */
  protected function addTransform(string $name, string $type, array $config): \codename\core\io\transform {
    if($this->transforms[$name] ?? false) {
      throw new \Exception("Transform `{$name}` already added.");
    }
    $this->transforms[$name] = $this->getTransform($type, $config);
    return $this->transforms[$name];
  }

  /**
   * [getTransform description]
   * @param  string                         $type                [description]
   * @param  array|null                     $config              [description]
   * @return \codename\core\io\transform                      [description]
   */
  protected function getTransform(string $type, ?array $config = null) : \codename\core\io\transform {
    $class = app::getInheritedClass('transform_' . $type);
    if(class_exists($class)) {
      $transform = new $class($config);
      if($transform instanceof \codename\core\io\transform) {
        // if($transformerInstance instanceof \codename\core\io\pipeline) {
        //   $transform->setPipelineInstance($transformerInstance);
        // }
        $transform->setTransformerInstance($this);
      } else {
        throw new \Exception('Transform has wrong base class: '.$class);
      }
      return $transform;
    } else {
      throw new \Exception('Transform class not found: '.$class);
    }
  }


}
