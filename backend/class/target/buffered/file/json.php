<?php

namespace codename\core\io\target\buffered\file;

use codename\core\exception;
use codename\core\helper\deepaccess;
use codename\core\io\target\buffered\file;
use codename\core\io\value\text\fileabsolute\tagged;
use codename\core\value\text\fileabsolute;
use ReflectionException;

class json extends file
{
    /**
     * [protected description]
     * @var string|null [type]
     */
    protected ?string $encoding = null;

    /**
     * count after which we begin a new file
     * @var null|int
     */
    protected ?int $splitCount = null;
    /**
     * @var mixed|null
     */
    private mixed $template;
    /**
     * @var mixed|null
     */
    private mixed $templateElementsPath;

    /**
     * [__construct description]
     * @param string $name [description]
     * @param array $config [description]
     */
    public function __construct(string $name, array $config)
    {
        parent::__construct($name, $config);
        $this->encoding = $this->config['encoding'] ?? $this->encoding;
        $this->template = $this->config['template'] ?? null;
        $this->templateElementsPath = $this->config['template_elements_path'] ?? null;
        $this->splitCount = $config['split_count'] ?? null;
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
                $tagsElement['file_extension'] = 'json';

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
     */
    protected function internalStoreBufferedData($handle, $bufferArray): void
    {
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
                    $resultData[$mapName] = $bufferEntry[$mapName] ?? null;
                }
            }

            $elements[] = $resultData;
        }


        $data = [];

        // NOTE: no elements path results in overridden final data
        if ($this->template ?? false) {
            $data = $this->template;
        }
        if ($this->templateElementsPath ?? false) {
            $data = deepaccess::set($data, $this->templateElementsPath, $elements);
        } else {
            $data = $elements; // we could replace?
        }

        if ($this->splitCount && count($data) === 1) {
            $dataKeys = array_keys($data);
            fwrite($handle, json_encode($data[$dataKeys[0]]));
        } else {
            fwrite($handle, json_encode($data));
        }
        fclose($handle);
    }
}
