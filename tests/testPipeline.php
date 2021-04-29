<?php
namespace codename\core\io\tests;

use codename\core\tests\base;
use codename\core\tests\overrideableApp;

class testPipeline extends base
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
   * [testPipelineGeneral description]
   */
  public function testPipelineGeneral(): void {
    $pipline = new \codename\core\io\pipeline(null, [
      'example' => true
    ]);

    //
    // check config
    //
    $this->assertEquals([ 'example' => true ], $pipline->getConfig()->get());

    //
    // check options
    //
    $this->assertNull($pipline->getOption('example'));
    $pipline->setOptions([ 'example' => true ]);
    $this->assertTrue($pipline->getOption('example'));

    //
    // check dryrun
    //
    $pipline->setDryRun(true);
    $this->assertTrue($pipline->getDryRun());

    //
    // check erroneous
    //
    $this->assertEmpty($pipline->getErroneousEntries());

    //
    // set default?
    //
    $pipline->setDebug(true);
    $pipline->setLimit(0, 10);
    $pipline->setThrowExceptionOnErroneousData(true);
    $pipline->setErrorstackEnabled(true);
    $pipline->setSkipErroneous(true);
    $pipline->setTrackErroneous(true);
  }

  /**
   * [testPipelineDatasource description]
   */
  public function testPipelineDatasource(): void {
    $pipline = new \codename\core\io\pipeline(null, []);

    $datasource = new \codename\core\io\datasource\arraydata();
    $datasource->setData([
      [
        'key1' => 'abc',
        'key2' => 'def',
        'key3' => 'ghi',
      ],
      [
        'key1' => 'jkl',
        'key2' => 'mno',
        'key3' => 'pqr',
      ],
      [
        'key1' => 'stu',
        'key2' => 'vwx',
        'key3' => 'yz',
      ]
    ]);
    $pipline->setDatasource($datasource);
    $this->assertInstanceOf(\codename\core\io\datasource\arraydata::class, $pipline->getDatasource());

    $this->assertEquals(3, $pipline->getItemCount());
    $this->assertEquals(null, $pipline->getItemIndex());
    $this->assertEquals(null, $pipline->getStoredItemCount());

  }

  /**
   * [testPipelineDatasourceBuffered description]
   */
  public function testPipelineDatasourceBuffered(): void {
    $pipline = new \codename\core\io\pipeline(null, [
      'config'  => [
        'datasource_buffering'    => true,
        'datasource_buffer_size'  => 100,
      ],
    ]);

    $datasource = new \codename\core\io\datasource\arraydata();
    $datasource->setData([
      [
        'key1' => 'abc',
        'key2' => 'def',
        'key3' => 'ghi',
      ],
      [
        'key1' => 'jkl',
        'key2' => 'mno',
        'key3' => 'pqr',
      ],
      [
        'key1' => 'stu',
        'key2' => 'vwx',
        'key3' => 'yz',
      ]
    ]);
    $pipline->setDatasource($datasource);
    $this->assertInstanceOf(\codename\core\io\datasource\buffered::class, $pipline->getDatasource());

  }

  /**
   * [testPipelineDatasource description]
   */
  public function testPipelineDatasourceLoad(): void {
    $pipline = new \codename\core\io\pipeline(__DIR__ . "/" . 'testPipeline.json', []);

    $datasource = new \codename\core\io\datasource\arraydata();
    $datasource->setData([
      [
        'key1' => 'abc',
        'key2' => 'def',
        'key3' => 'ghi',
      ],
      [
        'key1' => 'jkl',
        'key2' => 'mno',
        'key3' => 'pqr',
      ],
      [
        'key1' => 'stu',
        'key2' => 'vwx',
        'key3' => 'yz',
      ]
    ]);
    $pipline->setDatasource($datasource);
    $this->assertInstanceOf(\codename\core\io\datasource\arraydata::class, $pipline->getDatasource());

    $this->assertEquals(3, $pipline->getItemCount());
    $this->assertEquals(null, $pipline->getItemIndex());
    $this->assertEquals(null, $pipline->getStoredItemCount());

  }

}
