<?php
namespace codename\core\io\tests\transform;

class conditionedTest extends abstractTransformTest
{
  /**
   * Tests using a single condition that matches and returns a static value (bool)
   */
  public function testSingleConditionTrueReturnConstant(): void {
    $transform = $this->getTransform('get_conditioned', [
      'condition' => [
        [
          'source'    => 'source',
          'field'     => 'example_source_field',
          'operator'  => '=',
          'value'     => 123,
          'return'    => true
        ]
      ],
    ]);
    $result = $transform->transform([
      'example_source_field' => 123
    ]);
    $this->assertEquals(true, $result);
  }

  /**
   * Tests using a single condition that does *NOT* match and returns default value
   * which is not being set
   */
  public function testSingleConditionFalseReturnNoDefault(): void {
    $transform = $this->getTransform('get_conditioned', [
      'condition' => [
        [
          'source'    => 'source',
          'field'     => 'example_source_field',
          'operator'  => '=',
          'value'     => 123,
          'return'    => true
        ]
      ],
    ]);
    $result = $transform->transform([
      'example_source_field' => 234
    ]);
    $this->assertEquals(null, $result);
  }

  /**
   * Tests using a single condition that does *NOT* match and returns default value
   * which is not being set
   */
  public function testSingleConditionFalseReturnNullRequired(): void {
    $transform = $this->getTransform('get_conditioned', [
      'required'  => true,
      'condition' => [
        [
          'source'    => 'source',
          'field'     => 'example_source_field',
          'operator'  => '=',
          'value'     => 123,
          'return'    => true
        ]
      ],
    ]);
    $result = $transform->transform([
      'example_source_field' => 234
    ]);
    $this->assertNull($result);

    $errors = $transform->getErrors();
    $this->assertNotEmpty($errors);
    $this->assertCount(1, $errors);
    $this->assertEquals('GET_CONDITIONED_MISSING', $errors[0]['__IDENTIFIER'] );
    $this->assertEquals('TRANSFORM.0', $errors[0]['__CODE'] );
  }

  /**
   * Tests using a single condition that does *NOT* match and returns default value
   * which is not being set
   */
  public function testSingleWrongOperator(): void {
    $transform = $this->getTransform('get_conditioned', [
      'condition' => [
        [
          'source'    => 'source',
          'field'     => 'example_source_field',
          'operator'  => 'example',
          'value'     => 123,
          'return'    => true
        ]
      ],
    ]);
    $result = $transform->transform([
      'example_source_field' => 234
    ]);
    $this->assertNull($result);
  }

  /**
   * Tests using a single condition that does *NOT* match and returns default value
   * which is not being set
   */
  public function testSingleDefault(): void {
    $transform = $this->getTransform('get_conditioned', [
      'default'   => 'example',
      'condition' => [
        [
          'source'    => 'source',
          'field'     => 'example_source_field',
          'operator'  => 'example',
          'value'     => 123,
          'return'    => true
        ]
      ],
    ]);
    $result = $transform->transform([
      'example_source_field' => 234
    ]);
    $this->assertEquals('example', $result);

    $transform = $this->getTransform('get_conditioned', [
      'default'   => [
        'source'    => 'source',
        'field'     => 'example_default_field',
      ],
      'condition' => [
        [
          'source'    => 'source',
          'field'     => 'example_source_field',
          'operator'  => 'example',
          'value'     => 123,
          'return'    => true
        ]
      ],
    ]);
    $result = $transform->transform([
      'example_source_field'  => 234,
      'example_default_field' => 'example',
    ]);
    $this->assertEquals('example', $result);
  }

  /**
   * [testSingleConditionTrueReturnDynamic description]
   */
  public function testSingleConditionTrueReturnDynamic(): void {
    $transform = $this->getTransform('get_conditioned', [
      'condition' => [
        [
          'source'    => 'source',
          'field'     => 'example_source_field',
          'operator'  => '=',
          'value'     => 234,
          'return'    => [
            'source'    => 'source',
            'field'     => 'example_return_me',
          ]
        ]
      ],
    ]);
    $result = $transform->transform([
      'example_source_field' => 234,
      'example_return_me'    => 'yes'
    ]);
    $this->assertEquals('yes', $result);
  }

  /**
   * [testFuzzed description]
   */
  public function testFuzzed(): void {

    $testArray = [

      // regular comparisons
      [ 'input' => 123,   'operator' => '=',    'compare' => 123,   'matches' => true  ],
      [ 'input' => 123,   'operator' => '!=',   'compare' => 123,   'matches' => false ],
      [ 'input' => 123,   'operator' => '>',    'compare' => 123,   'matches' => false ],
      [ 'input' => 123,   'operator' => '>',    'compare' => 122,   'matches' => true  ],
      [ 'input' => 123,   'operator' => '<',    'compare' => 123,   'matches' => false ],
      [ 'input' => 123,   'operator' => '<',    'compare' => 124,   'matches' => true  ],
      [ 'input' => null,  'operator' => '=',    'compare' => null,  'matches' => true  ],
      [ 'input' => null,  'operator' => '!=',   'compare' => null,  'matches' => false ],
      [ 'input' => 0,     'operator' => '!=',   'compare' => 0,     'matches' => false ],
      [ 'input' => 0,     'operator' => '=',    'compare' => 0,     'matches' => true  ],
      [ 'input' => '',    'operator' => '=',    'compare' => '',    'matches' => true  ],

      // some alphanumeric comparisons
      [ 'input' => 'abc', 'operator' => '=',    'compare' => 'abc', 'matches' => true   ],
      [ 'input' => 'abc', 'operator' => '!=',   'compare' => 'abc', 'matches' => false  ],
      [ 'input' => 'abc', 'operator' => '=',    'compare' => 'def', 'matches' => false  ],
      [ 'input' => 'abc', 'operator' => '!=',   'compare' => 'def', 'matches' => true   ],
      [ 'input' => 'abc', 'operator' => '>',    'compare' => 'def', 'matches' => false  ],
      [ 'input' => 'abc', 'operator' => '<',    'compare' => 'def', 'matches' => true   ],
      [ 'input' => 'abc1',  'operator' => '>',  'compare' => 'def2', 'matches' => false  ],
      [ 'input' => 'abc2',  'operator' => '<',  'compare' => 'def2', 'matches' => true   ],
      [ 'input' => '1abc',  'operator' => '>',  'compare' => '2def', 'matches' => false  ],
      [ 'input' => '1abc',  'operator' => '<',  'compare' => '2def', 'matches' => true   ],

      // String null and empty string comparisons
      [ 'input' => 'abc', 'operator' => '=',    'compare' => null,  'matches' => false  ],
      [ 'input' => 'abc', 'operator' => '!=',   'compare' => null,  'matches' => true   ],
      [ 'input' => 'abc', 'operator' => '=',    'compare' => '',    'matches' => false  ],
      [ 'input' => 'abc', 'operator' => '!=',   'compare' => '',    'matches' => true   ],

      // NOTE: Null, 0 and empty string comparisons are special
      // as we're not performing strict type checking here. Victim of PHP Type Juggling!
      [ 'input' => '',    'operator' => '!=',   'compare' => 0,     'matches' => false  ], // w/ strict type checking: true
      [ 'input' => 0,     'operator' => '!=',   'compare' => '',    'matches' => false  ], // w/ strict type checking: true
      [ 'input' => '',    'operator' => '=',    'compare' => 0,     'matches' => true   ], // w/ strict type checking: false
      [ 'input' => 0,     'operator' => '=',    'compare' => '',    'matches' => true   ], // w/ strict type checking: false
      [ 'input' => null,  'operator' => '!=',   'compare' => 0,     'matches' => false  ], // w/ strict type checking: true
      [ 'input' => 0,     'operator' => '!=',   'compare' => null,  'matches' => false  ], // w/ strict type checking: true
      [ 'input' => null,  'operator' => '=',    'compare' => '',    'matches' => true   ], // w/ strict type checking: false
      [ 'input' => '',    'operator' => '=',    'compare' => null,  'matches' => true   ], // w/ strict type checking: false

      //
      // TODO: tests with invalid or bad data
      //
    ];

    $resultIfMatch = true;
    $resultIfNoMatch = false;

    foreach($testArray as $test) {
      $transform = $this->getTransform('get_conditioned', [
        'condition' => [
          [
            'source'    => 'source',
            'field'     => 'input_value',
            'operator'  => $test['operator'],
            'value'     => [
              'source' => 'source',
              'field'  => 'compare_value'
            ],
            'return'    => [
              'source'    => 'source',
              'field'     => 'result_if_match',
            ]
          ]
        ],
        'default' => $resultIfNoMatch
      ]);

      $result = $transform->transform([
        'input_value'     => $test['input'],
        'compare_value'   => $test['compare'],
        'result_if_match' => $resultIfMatch,
      ]);

      $this->assertEquals($test['matches'], $result, 'Comparing '
        . var_export($test['input'],true)
        . $test['operator']
        . var_export($test['compare'],true)
        . ' to be '
        . var_export($test['matches'] ? $resultIfMatch : $resultIfNoMatch,true)
      );
    }
  }

  /**
   * Test Spec output (simple case)
   */
  public function testSpecification(): void {
    $transform = $this->getTransform('get_conditioned', [
      'condition' => [
        [
          'source'    => 'source',
          'field'     => 'example_source_field',
          'operator'  => '=',
          'value'     => 123,
          'return'    => true
        ]
      ]
    ]);
    $this->assertEquals(
      [
        'type'    => 'transform',
        'source'  => [ 'source.example_source_field' ]
      ],
      $transform->getSpecification()
    );
  }
}
