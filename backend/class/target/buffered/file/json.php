<?php
namespace codename\core\io\target\buffered\file;

class json extends \codename\core\io\target\buffered\file
{

  /**
   * [protected description]
   * @var [type]
   */
  protected $encoding = null;

  /**
   * count after which we begin a new file
   * @var int
   */
  protected $splitCount = null;

  /**
   * [__construct description]
   * @param string $name   [description]
   * @param array  $config [description]
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
   * @inheritDoc
   */
  protected function storeBufferedData()
  {
    // split the array

    $dataChunks = [];
    $tagsChunks = [];

    if($this->splitCount && (count($this->bufferArray) > $this->splitCount)) {
      // we have to split at least one time
      $dataChunks = array_chunk($this->bufferArray, $this->splitCount);
      $tagsChunks = array_chunk($this->tagsArray, $this->splitCount);

    } else {
      $dataChunks = [ $this->bufferArray ];
      $tagsChunks = [ $this->tagsArray ];
    }

    $resultObjects = [];

    foreach ($dataChunks as $index => $dataChunk) {

      // skip empty chunks
      if(count($dataChunk) === 0) {
        continue;
      }

      $tagsChunk = $tagsChunks[$index];

      // create a new file handle?
      $handle = null;

      $path = $this->getNewFilePath();
      $handle = $this->getNewFileHandle($path);

      $this->internalStoreBufferedData($handle, $dataChunk);

      if(!$tagsChunk) {
        // fill with empty array, if not set
        $tagsChunk = array_fill(0, count($dataChunk), []);
      }
      foreach($tagsChunk as &$tagsElement) {
        // force csv extension in tag
        $tagsElement['file_extension'] = 'json';

        if(count($dataChunks) > 1) {
          // override filename with chunk number
          if($addendum = $tagsElement['file_name_add'] ?? ('_'.($index+1))) {
            $tagsElement['file_name'] .= $addendum;
          }
        }
      }

      if($tagsChunk) {
        $resultObjects[] = new \codename\core\io\value\text\fileabsolute\tagged($path, $tagsChunk);
      } else {
        $resultObjects[] = new \codename\core\value\text\fileabsolute($path, $tagsChunk);
      }
    }

    $this->fileResults = $resultObjects;
  }

  /**
   * @inheritDoc
   */
  protected function internalStoreBufferedData($handle, $bufferArray)
  {
    $elements = [];
    foreach($bufferArray as $bufferEntry) {

      $resultData = [];

      // pre-work some mapping options
      foreach($this->config['mapping'] as $mapName => $mapConfig) {
        if($mapConfig['path'] ?? false) {
          // path is relative base for the map name
          $objPath = array_merge($mapConfig['path'], [$mapName]);
          $resultData = \codename\core\io\helper\deepaccess::set($resultData, $objPath, $bufferEntry[$mapName]);
        } else {
          $resultData[$mapName] = $bufferEntry[$mapName];
        }
      }

      $elements[] = $resultData;
    }


    $data = [];

    // NOTE: no elements path results in overridden final data
    if($this->template) {
      $data = $this->template;
    }
    if($this->templateElementsPath) {
      $data = \codename\core\io\helper\deepaccess::set($data, $this->templateElementsPath, $elements);
    } else {
      $data = $elements; // we could replace?
    }

    if($this->splitCount && count($data) === 1) {
      $dataKeys = array_keys($data);
      fwrite($handle, json_encode($data[$dataKeys[0]]));
    } else {
      fwrite($handle, json_encode($data));
    }
    fclose($handle);
  }


}
