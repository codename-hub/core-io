<?php
namespace codename\core\io\tests\transform\get;

class easterdatetimeTest extends \codename\core\io\tests\transform\abstractTransformTest
{

  /**
   * Testing transforms for Erors
   */
  public function testValueValid(): void {
    $transform = $this->getTransform('get_easterdatetime', [
      'source'  => 'source',
      'field'   => 'example_source_field',
      'format'  => 'Y-m-d',
    ]);
    $result = $transform->transform([
      'example_source_field'  => '2021-04-19'
    ]);

    // calculate easter
    $days = \easter_days('2021');
    $easterDate = (new \DateTime('2021-04-19'))
      ->setDate('2021', 3, 21)
      ->add(new \DateInterval("P{$days}D"))
      ->format('Y-m-d');

    // Make sure it stays an array
    $this->assertEquals($easterDate, $result );
  }

  /**
   * Test Spec output (simple case)
   */
  public function testSpecification(): void {
    $transform = $this->getTransform('get_easterdatetime', [
      'source'  => 'source',
      'field'   => 'example_source_field',
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
