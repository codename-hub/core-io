<?php
namespace codename\core\io\tests\transform\compare;

class isholidayTest extends \codename\core\io\tests\transform\abstractTransformTest
{

  /**
   * Testing transforms for Erors
   */
  public function testValueValid(): void {
    $this->markTestIncomplete('TODO: DB Check');

    $transform = $this->getTransform('compare_isholiday', [
      'country'   => [
        'source'  => 'source',
        'field'   => 'example_country_field',
      ],
      'date'      => [
        'source'  => 'source',
        'field'   => 'example_date_field',
      ],
    ]);
    $result = $transform->transform([
      'example_country_field' => 'DE',
      'example_date_field'    => '2021-04-19',
    ]);
    // Make sure it stays an array
    $this->assertTrue($result);
  }

  /**
   * Test Spec output (simple case)
   */
  public function testSpecification(): void {
    $transform = $this->getTransform('compare_isholiday', [
      'country'   => [
        'source'  => 'source',
        'field'   => 'example_country_field',
      ],
      'date'      => [
        'source'  => 'source',
        'field'   => 'example_date_field',
      ],
    ]);
    $this->assertEquals(
      [
        'type'    => 'transform',
        'source'  => [ 'source.example_country_field', 'source.example_date_field' ]
      ],
      $transform->getSpecification()
    );
  }

}
