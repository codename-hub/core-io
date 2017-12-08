<?php namespace codename\core\io\datasource;
use \codename\core\exception;

/**
 * XML Datasource
 * Query it using XPath Queries
 */
class xml extends \codename\core\io\datasource
{
  /**
   * [protected description]
   * @var \SimpleXMLIterator
   */
  protected $xmlIterator = null;

  /**
   * some count caching
   * @var int
   */
  protected $itemCount = null;

  /**
   * [__construct description]
   * @param string $file   [description]
   * @param array  $config [description]
   */
  public function __construct(string $file = '', array $config = array())
  {
    $this->setConfig($config);
    $this->xmlIterator = new \SimpleXMLIterator($file, 0, true);
    $this->xmlIterator->rewind();
  }

  /**
   * [doXPathQuery description]
   * @return void
   */
  protected function doXPathQuery() {
    $this->xpathQueryResult = $this->xmlIterator->xpath($this->xpathQuery);
    // TODO: neither count(...) nor ->count() work correctly.
    $this->itemCount = 0; // $this->xpathQueryResult->count();
  }

  /**
   * [protected description]
   * @var \SimpleXMLElement[]
   */
  protected $xpathQueryResult = null;

  /**
   * xpath query
   * @see https://msdn.microsoft.com/en-us/library/ms256086(v=vs.110).aspx
   * @var string
   */
  protected $xpathQuery = null;

  /**
   * @inheritDoc
   */
  public function setConfig(array $config)
  {
    $this->xpathQuery = $config['xpath_query'] ?? null;
    $this->xpathMapping = $config['xpath_mapping'] ?? null;
  }

  /**
   * [protected description]
   * @var mixed|bool
   */
  protected $current = false;

  /**
   * [protected description]
   * @var \SimpleXMLElement
   */
  protected $currentRaw = null;

  /**
   * @inheritDoc
   */
  public function current()
  {
    return $this->current;
  }

  /**
   * if next had been called on the underlying iterator before
   * @var bool
   */
  protected $firstCall = true;

  /**
   * [protected description]
   * @var int|null
   */
  protected $index = null;

  /**
   * @inheritDoc
   */
  public function next()
  {
    if($this->xpathQueryResult == null) {
      $this->doXPathQuery();
    }

    if($this->index === null) {
      $this->index = 0;
    } else {
      $this->index++;
    }

    if(isset($this->xpathQueryResult[$this->index])) {
      $this->currentRaw = $this->xpathQueryResult[$this->index];
      $res = [];
      foreach($this->xpathMapping as $key => $query) {
        $res[$key] = (string) $this->currentRaw->xpath($query)[0];
      }
      $this->current = $res;
    } else {
      $this->currentRaw = false;
      $this->current = false;
    }
  }

  /**
   * @inheritDoc
   */
  public function key()
  {
    return $this->index;
  }

  /**
   * @inheritDoc
   */
  public function valid()
  {
    return $this->current !== false;
  }

  /**
   * @inheritDoc
   */
  public function rewind()
  {
    $this->index = null;
    $this->current = false;
    $this->currentRaw = null;
    $this->doXPathQuery();
    $this->next();
  }

  /**
   * @inheritDoc
   */
  public function currentProgressPosition() : int
  {
    return $this->index ?? 0;
  }

  /**
   * @inheritDoc
   */
  public function currentProgressLimit() : int
  {
    return $this->itemCount ?? 0;
  }

}
