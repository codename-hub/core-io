<?php
namespace codename\core\io\tests\transform\calculate;

class roundTest extends \codename\core\io\tests\transform\abstractTransformTest
{

  /**
   * Testing transforms for Erors
   */
  public function testDefaultRounding(): void {
    $transform = $this->getTransform('math_round', [
      'source'  => 'source',
      'field'   => 'example_source_field1',
    ]);
    $result = $transform->transform([
      'example_source_field1'  => 5.4321,
    ]);

    $this->assertEquals(5.0, $result);
    $this->assertIsFloat($result); // Will stay a float in this case...?

    $transform->reset();
    $result = $transform->transform([
      'example_source_field1'  => 5.5,
    ]);
    $this->assertEquals(6.0, $result);
  }

  /**
   * Tests negative rounding, e.g. 5.432 => 10
   */
  public function testNegativeRounding(): void {
    $transform = $this->getTransform('math_round', [
      'source'    => 'source',
      'field'     => 'example_source_field1',
      'precision' => -1
    ]);
    $result = $transform->transform([
      'example_source_field1'  => 5.4321,
    ]);

    $this->assertEquals(10, $result);
    $this->assertIsFloat($result); // Will stay a float in this case...?

    $transform->reset();
    $result = $transform->transform([
      'example_source_field1'  => 5.0,
    ]);
    $this->assertEquals(10, $result);

    $transform->reset();
    $result = $transform->transform([
      'example_source_field1'  => 4.0,
    ]);
    $this->assertEquals(0, $result);
  }

  /**
   * [testNegativeRoundingDown description]
   */
  public function testNegativeRoundingDown(): void {
    $transform = $this->getTransform('math_round', [
      'source'    => 'source',
      'field'     => 'example_source_field1',
      'precision' => -1,
      'mode'      => 'half_down',
    ]);
    $result = $transform->transform([
      'example_source_field1'  => 5.4321,
    ]);

    $this->assertEquals(10.0, $result);
    $this->assertIsFloat($result); // Will stay a float in this case...?

    $transform->reset();
    $result = $transform->transform([
      'example_source_field1'  => 5.0,
    ]);
    $this->assertEquals(0, $result);

    $transform->reset();
    $result = $transform->transform([
      'example_source_field1'  => 4.0,
    ]);
    $this->assertEquals(0, $result);
  }

  /**
   * Tests a custom precision value
   */
  public function testCustomPrecision(): void {
    $transform = $this->getTransform('math_round', [
      'source'    => 'source',
      'field'     => 'example_source_field1',
      'precision' => 1
    ]);
    $result = $transform->transform([
      'example_source_field1'  => 5.4321,
    ]);

    $this->assertEquals(5.4, $result);
    $this->assertIsFloat($result); // Will stay a float in this case...?
  }

  /**
   * Tests half_down rounding
   */
  public function testRoundDownBehavior(): void {
    $transform = $this->getTransform('math_round', [
      'source'    => 'source',
      'field'     => 'example_source_field1',
      'precision' => 1,
      'mode'      => 'half_down'
    ]);
    $result = $transform->transform([
      'example_source_field1'  => 5.45,
    ]);

    $this->assertEquals(5.4, $result);
    $this->assertIsFloat($result); // Will stay a float in this case...?

    $transform->reset();
    $result = $transform->transform([
      'example_source_field1'  => 5.55,
    ]);

    $this->assertEquals(5.5, $result);
    $this->assertIsFloat($result); // Will stay a float in this case...?
  }

  /**
   * Tests default half_up
   */
  public function testRoundUpBehavior(): void {
    $transform = $this->getTransform('math_round', [
      'source'    => 'source',
      'field'     => 'example_source_field1',
      'precision' => 1
    ]);
    $result = $transform->transform([
      'example_source_field1'  => 5.45,
    ]);

    $this->assertEquals(5.5, $result);
    $this->assertIsFloat($result); // Will stay a float in this case...?
  }

  /**
   * Tests whether half_up and financial modes are the same.
   */
  public function testModeAliaseRoundUpFinancial(): void {
    $transformA = $this->getTransform('math_round', [
      'source'    => 'source',
      'field'     => 'example_source_field1',
      'precision' => 1,
      'mode'      => 'financial',
    ]);
    $resultA = $transformA->transform([
      'example_source_field1'  => 5.45,
    ]);

    $transformB = $this->getTransform('math_round', [
      'source'    => 'source',
      'field'     => 'example_source_field1',
      'precision' => 1,
      'mode'      => 'half_up',
    ]);
    $resultB = $transformA->transform([
      'example_source_field1'  => 5.45,
    ]);

    $this->assertEquals(5.5, $resultA);
    $this->assertIsFloat($resultA); // Will stay a float in this case...?
    $this->assertEquals($resultA, $resultB);
  }

  /**
   * Test specifying an invalid mode will throw an exception
   */
  public function testInvalidModeWillThrow(): void {
    $this->expectException(\codename\core\exception::class);
    $this->expectExceptionMessage('INVALID_ROUND_MODE');
    $transform = $this->getTransform('math_round', [
      'source'  => 'source',
      'field'   => 'example_source_field1',
      'mode'    => 'invalid_value',
    ]);
  }

  // /**
  //  * Test Spec output (simple case)
  //  */
  // public function testSpecification(): void {
  //   $transform = $this->getTransform('calculate_multiply', [
  //     'factors'    => [
  //       [ 'source'  => 'source', 'field'   => 'example_source_field1' ],
  //       'example_source_field2',
  //     ],
  //     'precision' => 2,
  //   ]);
  //   $this->assertEquals(
  //     [
  //       'type'    => 'transform',
  //       'source'  => [ 'source.example_source_field1' ]
  //     ],
  //     $transform->getSpecification()
  //   );
  // }

}
