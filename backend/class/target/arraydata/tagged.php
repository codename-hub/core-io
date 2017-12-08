<?php
namespace codename\core\io\target\arraydata;

use codename\core\exception;

/**
 * tagged array data as target
 */
class tagged extends \codename\core\io\target\arraydata
  implements \codename\core\io\targetStoreTagInterface, \codename\core\io\target\structureResultArrayInterface {

    /**
     * buffered entries
     * @var \codename\core\value\structure[]
     */
    protected $resultObjects = [];

    /**
     * @inheritDoc
     */
    public function store(array $data, ?array $tags = null) : bool
    {
      if($this->finished) {
        throw new exception('EXCEPTION_CORE_IO_TARGET_BUFFERED_ALREADY_FINISHED', exception::$ERRORLEVEL_ERROR);
      }

      if($tags) {
        $tagsChunk = [ $tags ];
        $this->resultObjects[] = new \codename\core\io\value\structure\tagged($data, $tagsChunk);
      } else {
        $this->resultObjects[] = new \codename\core\value\structure($data);
      }

      return true;
    }

    /**
     * returns data stored virtually in this instance
     * @return array [description]
     */
    public function getVirtualStoreData() : array {
      $result = [];
      foreach($this->resultObjects as $obj) {
        $result[] = $obj->get();
      }
      return $result;
    }

    /**
     * @inheritDoc
     */
    public function getStructureResultArray(): array
    {
      return $this->resultObjects;
    }
}
