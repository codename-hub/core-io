<?php
namespace codename\core\io\target;

use codename\core\exception;

/**
 * xml file as a target
 */
class xml extends \codename\core\io\target {

  /**
   * [protected description]
   * @var \Sabre\Xml\Service
   */
  protected $xmlService = null;

  /**
   * [protected description]
   * @var [type]
   */
  protected $version = 1.0;

  /**
   * [protected description]
   * @var [type]
   */
  protected $encoding = null;

  /**
   * [__construct description]
   * @param string $name   [description]
   * @param array  $config [description]
   */
  public function __construct(string $name, array $config)
  {
    parent::__construct($name, $config);
    $this->xmlService = new \Sabre\Xml\Service();
    $this->version = $this->config['version'] ?? $this->version;
    $this->encoding = $this->config['encoding'] ?? $this->encoding;
  }

  /**
   * @inheritDoc
   */
  public function store(array $data) : bool
  {

    $resultData = [];

    // pre-work some mapping options
    foreach($this->config['mapping'] as $mapName => $mapConfig) {
      if($mapConfig['path'] ?? false) {
        // path is relative base for the map name
        $objPath = array_merge($mapConfig['path'], [$mapName]);
        $resultData = \codename\core\io\helper\deepaccess::set($resultData, $objPath, $data[$mapName]);
      } else {
        $resultData[$mapName] = $data[$mapName];
      }
    }

    // print_r($resultData);

    $writer = new \Sabre\Xml\Writer();
    $writer->openMemory();
    $writer->setIndent(true);
    $writer->startDocument($this->version, $this->encoding);
    $writer->write($resultData);

    $xmlString = $writer->outputMemory();
    // echo $xmlString;

    if($this->config['schema_file'] ?? false) {
      try {

        $doc = new \DOMDocument();
        $doc->loadXML($xmlString);
        $valid = $doc->schemaValidate(\codename\core\app::getInheritedPath($this->config['schema_file']));

        if(!$valid) {
          $errors = libxml_get_errors();
          // echo("LIBXML ERRORS:".chr(10));
          // print_r($errors);

          throw new exception('EXCEPTION_TARGET_XML_ERRORS', exception::$ERRORLEVEL_ERROR, $errors);
        } else {
          // echo "VALID!";
        }
      } catch (\Exception $e) {
        // print_r($e);
        throw new exception('EXCEPTION_TARGET_XML_ERROR_EXCEPTION', exception::$ERRORLEVEL_ERROR, [
          'message' => $e->getMessage(),
          'code'    => $e->getCode()
        ]);
        // or rethrow?
      }
    }

    $this->virtualXmlStore[] = $xmlString;
    return true;
  }

  /**
   * store xml strings here
   * @var string[]
   */
  protected $virtualXmlStore = [];

  // protected static function setRecursively(array $data, array $path = []) {
  //
  // }

  /**
   * @inheritDoc
   */
  public function finish()
  {
    // for xml strings, do nothing?
    // for xml files, write them?
  }


}
