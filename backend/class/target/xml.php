<?php

namespace codename\core\io\target;

use codename\core\app;
use codename\core\exception;
use codename\core\helper\deepaccess;
use codename\core\io\target;
use DOMDocument;
use Sabre\Xml\Service;
use Sabre\Xml\Writer;

/**
 * xml file as a target
 */
class xml extends target
{
    /**
     * [protected description]
     * @var Service
     */
    protected Service $xmlService;

    /**
     * [protected description]
     * @var string
     */
    protected string $version = '1.0';

    /**
     * [protected description]
     * @var null|string
     */
    protected ?string $encoding = null;
    /**
     * store xml strings here
     * @var string[]
     */
    protected array $virtualXmlStore = [];

    /**
     * [__construct description]
     * @param string $name [description]
     * @param array $config [description]
     */
    public function __construct(string $name, array $config)
    {
        parent::__construct($name, $config);
        $this->xmlService = new Service();
        $this->version = $this->config['version'] ?? $this->version;
        $this->encoding = $this->config['encoding'] ?? $this->encoding;
    }

    /**
     * {@inheritDoc}
     * @param array $data
     * @return bool
     * @throws exception
     */
    public function store(array $data): bool
    {
        $resultData = [];

        // pre-work some mapping options
        foreach ($this->config['mapping'] as $mapName => $mapConfig) {
            if ($mapConfig['path'] ?? false) {
                // path is relative base for the map name
                $objPath = array_merge($mapConfig['path'], [$mapName]);
                $resultData = deepaccess::set($resultData, $objPath, $data[$mapName]);
            } else {
                $resultData[$mapName] = $data[$mapName];
            }
        }

        $writer = new Writer();
        $writer->openMemory();
        $writer->setIndent(true);
        $writer->startDocument($this->version, $this->encoding);
        $writer->write($resultData);

        $xmlString = $writer->outputMemory();

        if ($this->config['schema_file'] ?? false) {
            try {
                $doc = new DOMDocument();
                $doc->loadXML($xmlString);
                $valid = $doc->schemaValidate(app::getInheritedPath($this->config['schema_file']));

                if (!$valid) {
                    $errors = libxml_get_errors();

                    throw new exception('EXCEPTION_TARGET_XML_ERRORS', exception::$ERRORLEVEL_ERROR, $errors);
                } else {
                    // echo "VALID!";
                }
            } catch (\Exception $e) {
                throw new exception('EXCEPTION_TARGET_XML_ERROR_EXCEPTION', exception::$ERRORLEVEL_ERROR, [
                  'message' => $e->getMessage(),
                  'code' => $e->getCode(),
                ]);
            }
        }

        $this->virtualXmlStore[] = $xmlString;
        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function finish(): void
    {
        // for xml strings, do nothing?
        // for xml files, write them?
    }
}
