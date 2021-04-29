<?php
namespace codename\core\io\tests\value\text\fileabsolute;

class testTagged extends \PHPUnit\Framework\TestCase
{

  /**
   * [testTagsIsValid description]
   * @return [type] [description]
   */
  public function testTagsIsValid() {
    $tagged = new \codename\core\io\value\text\fileabsolute\tagged('example', [
      'example' => true,
    ]);

    $this->assertEquals([
      'example' => true,
    ], $tagged->getTags());

  }

}
