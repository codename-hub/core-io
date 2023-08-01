<?php

namespace codename\core\io\tests\transform\math;

use codename\core\exception;
use codename\core\io\tests\transform\abstractTransformTest;
use ReflectionException;

class roundTest extends abstractTransformTest
{
    /**
     * Testing transforms for Errors
     * @throws ReflectionException
     * @throws exception
     */
    public function testDefaultRounding(): void
    {
        $transform = $this->getTransform('math_round', [
          'source' => 'source',
          'field' => 'example_source_field1',
        ]);
        $result = $transform->transform([
          'example_source_field1' => 5.4321,
        ]);

        static::assertEquals(5.0, $result);
        static::assertIsFloat($result); // Will stay a float in this case...?

        $transform->reset();
        $result = $transform->transform([
          'example_source_field1' => 5.5,
        ]);
        static::assertEquals(6.0, $result);
    }

    /**
     * Tests negative rounding, e.g. 5.432 => 10
     * @throws ReflectionException
     * @throws exception
     */
    public function testNegativeRounding(): void
    {
        $transform = $this->getTransform('math_round', [
          'source' => 'source',
          'field' => 'example_source_field1',
          'precision' => -1,
        ]);
        $result = $transform->transform([
          'example_source_field1' => 5.4321,
        ]);

        static::assertEquals(10, $result);
        static::assertIsFloat($result); // Will stay a float in this case...?

        $transform->reset();
        $result = $transform->transform([
          'example_source_field1' => 5.0,
        ]);
        static::assertEquals(10, $result);

        $transform->reset();
        $result = $transform->transform([
          'example_source_field1' => 4.0,
        ]);
        static::assertEquals(0, $result);
    }

    /**
     * [testNegativeRoundingDown description]
     * @throws ReflectionException
     * @throws exception
     */
    public function testNegativeRoundingDown(): void
    {
        $transform = $this->getTransform('math_round', [
          'source' => 'source',
          'field' => 'example_source_field1',
          'precision' => -1,
          'mode' => 'half_down',
        ]);
        $result = $transform->transform([
          'example_source_field1' => 5.4321,
        ]);

        static::assertEquals(10.0, $result);
        static::assertIsFloat($result); // Will stay a float in this case...?

        $transform->reset();
        $result = $transform->transform([
          'example_source_field1' => 5.0,
        ]);
        static::assertEquals(0, $result);

        $transform->reset();
        $result = $transform->transform([
          'example_source_field1' => 4.0,
        ]);
        static::assertEquals(0, $result);
    }

    /**
     * Tests a custom precision value
     * @throws ReflectionException
     * @throws exception
     */
    public function testCustomPrecision(): void
    {
        $transform = $this->getTransform('math_round', [
          'source' => 'source',
          'field' => 'example_source_field1',
          'precision' => 1,
        ]);
        $result = $transform->transform([
          'example_source_field1' => 5.4321,
        ]);

        static::assertEquals(5.4, $result);
        static::assertIsFloat($result); // Will stay a float in this case...?
    }

    /**
     * Tests half_down rounding
     * @throws ReflectionException
     * @throws exception
     */
    public function testRoundDownBehavior(): void
    {
        $transform = $this->getTransform('math_round', [
          'source' => 'source',
          'field' => 'example_source_field1',
          'precision' => 1,
          'mode' => 'half_down',
        ]);
        $result = $transform->transform([
          'example_source_field1' => 5.45,
        ]);

        static::assertEquals(5.4, $result);
        static::assertIsFloat($result); // Will stay a float in this case...?

        $transform->reset();
        $result = $transform->transform([
          'example_source_field1' => 5.55,
        ]);

        static::assertEquals(5.5, $result);
        static::assertIsFloat($result); // Will stay a float in this case...?
    }

    /**
     * Tests default half_up
     * @throws ReflectionException
     * @throws exception
     */
    public function testRoundUpBehavior(): void
    {
        $transform = $this->getTransform('math_round', [
          'source' => 'source',
          'field' => 'example_source_field1',
          'precision' => 1,
        ]);
        $result = $transform->transform([
          'example_source_field1' => 5.45,
        ]);

        static::assertEquals(5.5, $result);
        static::assertIsFloat($result); // Will stay a float in this case...?
    }

    /**
     * Tests whether half_up and financial modes are the same.
     * @throws ReflectionException
     * @throws exception
     */
    public function testModeAliaseRoundUpFinancial(): void
    {
        $transformA = $this->getTransform('math_round', [
          'source' => 'source',
          'field' => 'example_source_field1',
          'precision' => 1,
          'mode' => 'financial',
        ]);
        $resultA = $transformA->transform([
          'example_source_field1' => 5.45,
        ]);

        $this->getTransform('math_round', [
          'source' => 'source',
          'field' => 'example_source_field1',
          'precision' => 1,
          'mode' => 'half_up',
        ]);
        $resultB = $transformA->transform([
          'example_source_field1' => 5.45,
        ]);

        static::assertEquals(5.5, $resultA);
        static::assertIsFloat($resultA); // Will stay a float in this case...?
        static::assertEquals($resultA, $resultB);
    }

    /**
     * Test specifying an invalid mode will throw an exception
     * @throws ReflectionException
     * @throws exception
     */
    public function testInvalidModeWillThrow(): void
    {
        $this->expectException(exception::class);
        $this->expectExceptionMessage('INVALID_ROUND_MODE');
        $this->getTransform('math_round', [
          'source' => 'source',
          'field' => 'example_source_field1',
          'mode' => 'invalid_value',
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
    //   static::assertEquals(
    //     [
    //       'type'    => 'transform',
    //       'source'  => [ 'source.example_source_field1' ]
    //     ],
    //     $transform->getSpecification()
    //   );
    // }
}
