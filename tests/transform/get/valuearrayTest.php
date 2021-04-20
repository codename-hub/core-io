<?php
namespace codename\core\io\tests\transform\get;

class valuearrayTest extends \codename\core\io\tests\transform\abstractTransformTest
{

  /**
   * Testing transforms for Erors
   */
  public function testValue(): void {
    $transform = $this->getTransform('get_valuearray', [
      'elements'  => [
        'country' => 'DE',
        'zipcode' => [ 'source' => 'source', 'field' => 'example_zipcode_field' ],
        'city'    => [ 'source' => 'source', 'field' => 'example_city_field' ],
        'street'  => [ 'source' => 'source', 'field' => 'example_street_field' ],
        'houseno' => [ 'source' => 'source', 'field' => 'example_houseno_field' ],
      ]
    ]);
    $result = $transform->transform([
      'example_zipcode_field' => '01067',
      'example_city_field'    => 'Dresden',
      'example_street_field'  => 'Adlergasse',
      'example_houseno_field' => '1',
    ]);

    // Make sure it stays an array
    $this->assertEquals([
      'country' => 'DE',
      'zipcode' => '01067',
      'city'    => 'Dresden',
      'street'  => 'Adlergasse',
      'houseno' => '1',
    ], $result );
  }

  /**
   * Testing transforms for Erors
   */
  public function testValueRequired(): void {
    $transform = $this->getTransform('get_valuearray', [
      'elements'  => [
        'country' => 'DE',
        'zipcode' => [ 'source' => 'source', 'field' => 'example_zipcode_field' ],
        'city'    => [ 'source' => 'source', 'field' => 'example_city_field' ],
        'street'  => [ 'source' => 'source', 'field' => 'example_street_field' ],
        'houseno' => [ 'source' => 'source', 'field' => 'example_houseno_field', 'required' => true ],
      ]
    ]);
    $result = $transform->transform([
      'example_zipcode_field' => '01067',
      'example_city_field'    => 'Dresden',
      'example_street_field'  => null,
      'example_houseno_field' => null,
    ]);

    // Make sure it stays an array
    $this->assertEquals([
      'country' => 'DE',
      'zipcode' => '01067',
      'city'    => 'Dresden',
    ], $result );

    $errors = $transform->getErrors();
    $this->assertNotEmpty($errors);
    $this->assertCount(1, $errors);
    $this->assertEquals('GET_VALUEARRAY_MISSING_KEY', $errors[0]['__IDENTIFIER'] );
    $this->assertEquals('TRANSFORM.0', $errors[0]['__CODE'] );
  }

  /**
   * Test Spec output (simple case)
   */
  public function testSpecification(): void {
    $transform = $this->getTransform('get_valuearray', [
      'elements'  => [
        'country' => 'DE',
        'zipcode' => [ 'source' => 'source', 'field' => 'example_zipcode_field' ],
        'city'    => [ 'source' => 'source', 'field' => 'example_city_field' ],
        'street'  => [ 'source' => 'source', 'field' => 'example_street_field' ],
        'houseno' => [ 'source' => 'source', 'field' => 'example_houseno_field' ],
        'example' => [ 'source' => 'source', 'field' => [ 'example', 'example'] ],
      ]
    ]);
    $this->assertEquals(
      [
        'type'    => 'transform',
        'source'  => [
          'zipcode' => 'source.example_zipcode_field',
          'city'    => 'source.example_city_field',
          'street'  => 'source.example_street_field',
          'houseno' => 'source.example_houseno_field',
          'example' => 'source.example.example',
        ]
      ],
      $transform->getSpecification()
    );
  }

}
