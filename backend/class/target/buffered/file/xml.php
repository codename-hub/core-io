<?php

namespace codename\core\io\target\buffered\file;

use codename\core\app;
use codename\core\exception;
use codename\core\helper\deepaccess;
use codename\core\io\target\buffered\file;
use codename\core\io\value\text\fileabsolute\tagged;
use codename\core\value\text\fileabsolute;
use DOMDocument;
use ReflectionException;
use Sabre\Xml\Service;
use Sabre\Xml\Writer;

class xml extends file
{
    /**
     * [protected description]
     * @var Service
     */
    protected Service $xmlService;

    /**
     * [protected description]
     * @var string [type]
     */
    protected string $version = '1.0';

    /**
     * [protected description]
     * @var string|null [type]
     */
    protected ?string $encoding = null;
    /**
     * @var mixed|null
     */
    protected mixed $template;
    /**
     * @var mixed|null
     */
    protected mixed $templateElementsPath;

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
        $this->template = $this->config['template'] ?? null;
        $this->templateElementsPath = $this->config['template_elements_path'] ?? null;
    }

    /**
     * {@inheritDoc}
     * @throws ReflectionException
     * @throws exception
     */
    protected function storeBufferedData(): void
    {
        // split the array
        if ($this->splitCount && (count($this->bufferArray) > $this->splitCount)) {
            // we have to split at least one time
            $dataChunks = array_chunk($this->bufferArray, $this->splitCount);
            $tagsChunks = array_chunk($this->tagsArray, $this->splitCount);
        } else {
            $dataChunks = [$this->bufferArray];
            $tagsChunks = [$this->tagsArray];
        }

        $resultObjects = [];

        foreach ($dataChunks as $index => $dataChunk) {
            // skip empty chunks
            if (count($dataChunk) === 0) {
                continue;
            }

            $tagsChunk = $tagsChunks[$index];

            // create a new file handle?
            $handle = null;

            $path = $this->getNewFilePath();
            $handle = $this->getNewFileHandle($path);

            $this->internalStoreBufferedData($handle, $dataChunk);

            if (!$tagsChunk) {
                // fill with empty array, if not set
                $tagsChunk = array_fill(0, count($dataChunk), []);
            }
            foreach ($tagsChunk as &$tagsElement) {
                // force csv extension in tag
                $tagsElement['file_extension'] = 'xml';

                if (count($dataChunks) > 1) {
                    // override filename with chunk number
                    if ($addendum = $tagsElement['file_name_add'] ?? ('_' . ($index + 1))) {
                        $tagsElement['file_name'] .= $addendum;
                    }
                }
            }

            if ($tagsChunk) {
                $resultObjects[] = new tagged($path, $tagsChunk);
            } else {
                $resultObjects[] = new fileabsolute($path);
            }
        }

        $this->fileResults = $resultObjects;
    }

    /**
     * @param $handle
     * @param $bufferArray
     * @return void
     * @throws exception
     */
    protected function internalStoreBufferedData($handle, $bufferArray): void
    {
        $writer = new Writer();
        $writer->openMemory();
        $writer->setIndent(true);
        $writer->startDocument($this->version, $this->encoding);

        $elements = [];
        foreach ($bufferArray as $bufferEntry) {
            $resultData = [];

            // pre-work some mapping options
            foreach ($this->config['mapping'] as $mapName => $mapConfig) {
                if ($mapConfig['path'] ?? false) {
                    // path is relative base for the map name
                    $objPath = array_merge($mapConfig['path'], [$mapName]);
                    $resultData = deepaccess::set($resultData, $objPath, $bufferEntry[$mapName]);
                } else {
                    $resultData[$mapName] = $bufferEntry[$mapName];
                }
            }

            $elements[] = $resultData;
        }


        $data = [];

        // NOTE: no elements path results in overridden final data
        if ($this->template) {
            $data = $this->template;
        }
        if ($this->templateElementsPath) {
            $data = deepaccess::set($data, $this->templateElementsPath, $elements);
        } else {
            $data = $elements; // we could replace?
        }

        $writer->write($data);

        $xmlString = $writer->outputMemory();

        //
        // Validate, if schema_file provided
        //
        if ($this->config['schema_file'] ?? false) {
            try {
                $doc = new DOMDocument();
                $doc->loadXML($xmlString);
                $valid = $doc->schemaValidate(app::getInheritedPath($this->config['schema_file']));

                if (!$valid) {
                    $errors = libxml_get_errors();

                    throw new exception('EXCEPTION_TARGET_XML_ERRORS', exception::$ERRORLEVEL_ERROR, app::object2array($errors));
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

        fwrite($handle, $xmlString);
        fclose($handle);
    }
}
