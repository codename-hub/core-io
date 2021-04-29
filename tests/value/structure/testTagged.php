<?php
namespace codename\core\io\tests\value\structure;

class testTagged extends \PHPUnit\Framework\TestCase
{

  /**
   * [testTagsIsValid description]
   * @return [type] [description]
   */
  public function testTagsIsValid() {
    $tagged = new \codename\core\io\value\structure\tagged([], [
      'example' => true,
    ]);

    $this->assertEquals([
      'example' => true,
    ], $tagged->getTags());

  }

}
