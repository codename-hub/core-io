<?php

namespace codename\core\io\tests\transform\get;

use codename\core\exception;
use codename\core\io\tests\transform\abstractTransformTest;
use ReflectionException;

class conditionedTest extends abstractTransformTest
{
    /**
     * Tests using a single condition that matches and returns a static value (bool)
     * @throws ReflectionException
     * @throws exception
     */
    public function testSingleConditionTrueReturnConstant(): void
    {
        $transform = $this->getTransform('get_conditioned', [
          'condition' => [
            [
              'source' => 'source',
              'field' => 'example_source_field',
              'operator' => '=',
              'value' => 123,
              'return' => true,
            ],
          ],
        ]);
        $result = $transform->transform([
          'example_source_field' => 123,
        ]);
        static::assertTrue($result);
    }

    /**
     * Tests using a single condition that does *NOT* match and returns default value
     * which is not being set
     * @throws ReflectionException
     * @throws exception
     */
    public function testSingleConditionFalseReturnNoDefault(): void
    {
        $transform = $this->getTransform('get_conditioned', [
          'condition' => [
            [
              'source' => 'source',
              'field' => 'example_source_field',
              'operator' => '=',
              'value' => 123,
              'return' => true,
            ],
          ],
        ]);
        $result = $transform->transform([
          'example_source_field' => 234,
        ]);
        static::assertEquals(null, $result);
    }

    /**
     * Tests using a single condition that does *NOT* match and returns default value
     * which is not being set
     * @throws ReflectionException
     * @throws exception
     */
    public function testSingleConditionFalseReturnNullRequired(): void
    {
        $transform = $this->getTransform('get_conditioned', [
          'required' => true,
          'condition' => [
            [
              'source' => 'source',
              'field' => 'example_source_field',
              'operator' => '=',
              'value' => 123,
              'return' => true,
            ],
          ],
        ]);
        $result = $transform->transform([
          'example_source_field' => 234,
        ]);
        static::assertNull($result);

        $errors = $transform->getErrors();
        static::assertNotEmpty($errors);
        static::assertCount(1, $errors);
        static::assertEquals('GET_CONDITIONED_MISSING', $errors[0]['__IDENTIFIER']);
        static::assertEquals('TRANSFORM.0', $errors[0]['__CODE']);
    }

    /**
     * Tests using a single condition that does *NOT* match and returns default value
     * which is not being set
     * @throws ReflectionException
     * @throws exception
     */
    public function testSingleWrongOperator(): void
    {
        $transform = $this->getTransform('get_conditioned', [
          'condition' => [
            [
              'source' => 'source',
              'field' => 'example_source_field',
              'operator' => 'example',
              'value' => 123,
              'return' => true,
            ],
          ],
        ]);
        $result = $transform->transform([
          'example_source_field' => 234,
        ]);
        static::assertNull($result);
    }

    /**
     * Tests using a single condition that does *NOT* match and returns default value
     * which is not being set
     * @throws ReflectionException
     * @throws exception
     */
    public function testSingleDefault(): void
    {
        $transform = $this->getTransform('get_conditioned', [
          'default' => 'example',
          'condition' => [
            [
              'source' => 'source',
              'field' => 'example_source_field',
              'operator' => 'example',
              'value' => 123,
              'return' => true,
            ],
          ],
        ]);
        $result = $transform->transform([
          'example_source_field' => 234,
        ]);
        static::assertEquals('example', $result);

        $transform = $this->getTransform('get_conditioned', [
          'default' => [
            'source' => 'source',
            'field' => 'example_default_field',
          ],
          'condition' => [
            [
              'source' => 'source',
              'field' => 'example_source_field',
              'operator' => 'example',
              'value' => 123,
              'return' => true,
            ],
          ],
        ]);
        $result = $transform->transform([
          'example_source_field' => 234,
          'example_default_field' => 'example',
        ]);
        static::assertEquals('example', $result);
    }

    /**
     * [testSingleConditionTrueReturnDynamic description]
     * @throws ReflectionException
     * @throws exception
     */
    public function testSingleConditionTrueReturnDynamic(): void
    {
        $transform = $this->getTransform('get_conditioned', [
          'condition' => [
            [
              'source' => 'source',
              'field' => 'example_source_field',
              'operator' => '=',
              'value' => 234,
              'return' => [
                'source' => 'source',
                'field' => 'example_return_me',
              ],
            ],
          ],
        ]);
        $result = $transform->transform([
          'example_source_field' => 234,
          'example_return_me' => 'yes',
        ]);
        static::assertEquals('yes', $result);
    }

    /**
     * [testFuzzed description]
     * @throws ReflectionException
     * @throws exception
     */
    public function testFuzzed(): void
    {
        $testArray = [

            // regular comparisons
          ['input' => 123, 'operator' => '=', 'compare' => 123, 'matches' => true],
          ['input' => 123, 'operator' => '!=', 'compare' => 123, 'matches' => false],
          ['input' => 123, 'operator' => '>', 'compare' => 123, 'matches' => false],
          ['input' => 123, 'operator' => '>', 'compare' => 122, 'matches' => true],
          ['input' => 123, 'operator' => '<', 'compare' => 123, 'matches' => false],
          ['input' => 123, 'operator' => '<', 'compare' => 124, 'matches' => true],
          ['input' => null, 'operator' => '=', 'compare' => null, 'matches' => true],
          ['input' => null, 'operator' => '!=', 'compare' => null, 'matches' => false],
          ['input' => 0, 'operator' => '!=', 'compare' => 0, 'matches' => false],
          ['input' => 0, 'operator' => '=', 'compare' => 0, 'matches' => true],
          ['input' => '', 'operator' => '=', 'compare' => '', 'matches' => true],

            // some alphanumeric comparisons
          ['input' => 'abc', 'operator' => '=', 'compare' => 'abc', 'matches' => true],
          ['input' => 'abc', 'operator' => '!=', 'compare' => 'abc', 'matches' => false],
          ['input' => 'abc', 'operator' => '=', 'compare' => 'def', 'matches' => false],
          ['input' => 'abc', 'operator' => '!=', 'compare' => 'def', 'matches' => true],
          ['input' => 'abc', 'operator' => '>', 'compare' => 'def', 'matches' => false],
          ['input' => 'abc', 'operator' => '<', 'compare' => 'def', 'matches' => true],
          ['input' => 'abc1', 'operator' => '>', 'compare' => 'def2', 'matches' => false],
          ['input' => 'abc2', 'operator' => '<', 'compare' => 'def2', 'matches' => true],
          ['input' => '1abc', 'operator' => '>', 'compare' => '2def', 'matches' => false],
          ['input' => '1abc', 'operator' => '<', 'compare' => '2def', 'matches' => true],

            // String null and empty string comparisons
          ['input' => 'abc', 'operator' => '=', 'compare' => null, 'matches' => false],
          ['input' => 'abc', 'operator' => '!=', 'compare' => null, 'matches' => true],
          ['input' => 'abc', 'operator' => '=', 'compare' => '', 'matches' => false],
          ['input' => 'abc', 'operator' => '!=', 'compare' => '', 'matches' => true],

            // NOTE: Null, 0 and empty string comparisons are special
          ['input' => '', 'operator' => '!=', 'compare' => 0, 'matches' => true], // w/ strict type checking: true
          ['input' => 0, 'operator' => '!=', 'compare' => '', 'matches' => true], // w/ strict type checking: true
          ['input' => '', 'operator' => '=', 'compare' => 0, 'matches' => false], // w/ strict type checking: false
          ['input' => 0, 'operator' => '=', 'compare' => '', 'matches' => false], // w/ strict type checking: false
          ['input' => null, 'operator' => '!=', 'compare' => 0, 'matches' => false], // w/ strict type checking: true
          ['input' => 0, 'operator' => '!=', 'compare' => null, 'matches' => false], // w/ strict type checking: true
          ['input' => null, 'operator' => '=', 'compare' => '', 'matches' => true], // w/ strict type checking: false
          ['input' => '', 'operator' => '=', 'compare' => null, 'matches' => true], // w/ strict type checking: false

            //
            // TODO: tests with invalid or bad data
            //
        ];

        foreach ($testArray as $test) {
            $transform = $this->getTransform('get_conditioned', [
              'condition' => [
                [
                  'source' => 'source',
                  'field' => 'input_value',
                  'operator' => $test['operator'],
                  'value' => [
                    'source' => 'source',
                    'field' => 'compare_value',
                  ],
                  'return' => [
                    'source' => 'source',
                    'field' => 'result_if_match',
                  ],
                ],
              ],
              'default' => false,
            ]);

            $result = $transform->transform([
              'input_value' => $test['input'],
              'compare_value' => $test['compare'],
              'result_if_match' => true,
            ]);

            static::assertEquals(
                $test['matches'],
                $result,
                'Comparing '
                . var_export($test['input'], true)
                . $test['operator']
                . var_export($test['compare'], true)
                . ' to be '
                . var_export((bool)$test['matches'], true)
            );
        }
    }

    /**
     * Test Spec output (simple case)
     * @throws ReflectionException
     * @throws exception
     */
    public function testSpecification(): void
    {
        $transform = $this->getTransform('get_conditioned', [
          'condition' => [
            [
              'source' => 'source',
              'field' => 'example_source_field',
              'operator' => '=',
              'value' => 123,
              'return' => true,
            ],
          ],
        ]);
        static::assertEquals(
            [
              'type' => 'transform',
              'source' => ['source.example_source_field'],
            ],
            $transform->getSpecification()
        );
    }
}
