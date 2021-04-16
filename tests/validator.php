<?php
namespace codename\core\io\tests;

use codename\core\tests\base;
use codename\core\tests\overrideableApp;

/**
 * I am just an extender for the unittest class
 * @package codename\core
 * @since 2016-11-02
 */
abstract class validator extends base {

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
   * [getValidator description]
   * @return \codename\core\validator [description]
   */
  protected function getValidator() : \codename\core\validator {
    // load the respective validator via namespace, by instanciated class name
    // we have to remove __CLASS__ (THIS exact class here)

    // extract validator name from current class name, stripped by validator base namespace
    $validatorClass = str_replace(__CLASS__.'\\', '', (new \ReflectionClass($this))->getName());

    // replace \ by _
    $validatorName = str_replace('\\', '_', $validatorClass);

    $validator = \codename\core\app::getValidator($validatorName);
    $validator->reset();
    return $validator;
  }

}
