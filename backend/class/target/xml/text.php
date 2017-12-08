<?php
namespace codename\core\io\target\xml;

/**
 * xml text as a target
 * "string" is a reserved keyword.
 */
class text extends \codename\core\io\target\xml
  implements \codename\core\io\target\textResultArrayInterface {

  /**
   * @inheritDoc
   */
  public function getTextResultArray() : array
  {
    return array_map(function($item) {
      return new \codename\core\value\text($item);
    }, $this->virtualXmlStore);
  }

}
