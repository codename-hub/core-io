<?php
namespace codename\core\io\tests\transform\get;

class currentdatetimeTest extends \codename\core\io\tests\transform\abstractTransformTest
{

  /**
   * Testing transforms for Erors
   */
  public function testValueValid(): void {
    $transform = $this->getTransform('get_currentdatetime', [
      'modify'  => '+1 day',
      'format'  => 'Y-m-d',
    ]);
    $result = $transform->transform([]);
    // Make sure it stays an array
    $this->assertEquals((new \DateTime('now'))->modify('+1 day')->format('Y-m-d'), $result );
  }

  /**
   * Test Spec output (simple case)
   */
  public function testSpecification(): void {
    $transform = $this->getTransform('get_currentdatetime', [
      'modify'  => '+1 day',
      'format'  => 'Y-m-d',
    ]);
    $this->assertEquals(
      [
        'type'    => 'transform',
        'source'  => [  ]
      ],
      $transform->getSpecification()
    );
  }

}
