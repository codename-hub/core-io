<?php
namespace codename\core\tests\pipeline;

use codename\core\io\pipeline;

/**
 * [pipelineModelTargetTest description]
 */
class genericPipelineTest extends abstractPipelineTest {

  public function testPipeline(): void {
    $pipeline = $this->executePipelineWithConfig([
      'source' => [
        'type' => 'arraydata'
      ],
      'constants' => [
        'my_constant' => 'Foo',
        'constant_array' => [
          'key' => 'Bar',
        ]
      ],
      'transform' => [
        'padded_simple_text' => [
          'type' => 'pad_left',
          'config' => [
            'source'  => 'source',
            'field'   => 'simple_text',
            'length'  => 10,
            'string'  => '#',
          ]
        ],
        'created_array' => [
          'type' => 'get_valuearray',
          'config' => [
            'elements' => [
              'c1' => 'X',
              'c2' => 'Y',
              'c3' => 'Z',
            ]
          ]
        ]
      ],
      'target' => [
        'test' => [
          'type' => 'dummy',
          'source_filter' => [
            [
              'field'     => 'simple_text',
              'operator'  => '!=',
              'value'     => 'DEF',
            ]
          ],
          'target_filter' => [
            [
              'field'     => 'source_array_by_key',
              'operator'  => '!=',
              'value'     => 'filterme',
            ]
          ],
          'mapping' => [
            'simple_text'               => [ 'type' => 'source',          'field' => 'simple_text'              ],
            'padded_simple_text'        => [ 'type' => 'transform',       'field' => 'padded_simple_text'       ],
            'padded_simple_text_deep'   => [ 'type' => 'transform_deep',  'field' => [ 'padded_simple_text' ]   ],
            'source_array_all'          => [ 'type' => 'source_deep',     'field' => [ 'array' ]                ],
            'source_array_by_index'     => [ 'type' => 'source_deep',     'field' => [ 'array', 1 ]             ],
            'source_array_by_key'       => [ 'type' => 'source_deep',     'field' => [ 'assoc_array', 'k2' ]    ],
            'transform_array_by_key'    => [ 'type' => 'transform_deep',  'field' => [ 'created_array', 'c2' ]  ],
            'raw'                       => [ 'type' => 'source_deep',     'field' => []                         ],
            'constant_value'            => [ 'type' => 'constant',        'field' => 'my_constant'              ],
            'constant_value_arrayid'    => [ 'type' => 'constant',        'field' => ['my_constant']            ],
            'constant_path'             => [ 'type' => 'constant',        'field' => ['constant_array', 'key']  ],

          ]
        ]
      ]
    ],[
      [
        // Filtered out by source value
        'simple_text' => 'DEF',
        'array'       => [ 'filtered_out' ],
        'assoc_array' => [ ],
      ],
      [
        // Filtered out by target value
        'simple_text' => 'GHI',
        'array'       => [ 'filtered_out' ],
        'assoc_array' => [ 'k2' => 'filterme' ],
      ],
      $original = [
        'simple_text' => 'ABC',
        'array'       => [ 'A', 'B', 'C' ],
        'assoc_array' => [ 'k1' => 'v1', 'k2' => 'v2', 'k3' => 'v3' ],
      ]
    ]);

    $target = $pipeline->getTarget('test');

    $this->assertInstanceOf(\codename\core\io\target\virtualTargetInterface::class, $target);

    if($target instanceof \codename\core\io\target\virtualTargetInterface) {
      $data = $target->getVirtualStoreData();
      $this->assertCount(1, $data);
      $this->assertEquals(
        [
          'simple_text'             => 'ABC',
          'padded_simple_text'      => '#######ABC',
          'padded_simple_text_deep' => '#######ABC',
          'source_array_all'        => [ 'A', 'B', 'C' ],
          'source_array_by_index'   => 'B',
          'source_array_by_key'     => 'v2',
          'transform_array_by_key'  => 'Y',
          'raw'                     => $original,
          'constant_value'          => 'Foo',
          'constant_value_arrayid'  => 'Foo',
          'constant_path'           => 'Bar',
        ],
        $data[0]
      );
    }
  }


  /**
   * [testPipelineGetTransformInstanceOfNonexistingFails description]
   */
  public function testPipelineGetTransformInstanceOfNonexistingFails(): void {
    $this->expectException(\codename\core\exception::class);
    $this->expectExceptionMessage('EXCEPTION_CORE_IO_PIPELINE_GETTRANSFORM_NOTFOUND');

    $pipeline = $this->executePipelineWithConfig([
      'source' => [
        'type' => 'arraydata'
      ],
      'transform' => [
      ],
      'target' => [
        'test' => [
          'type' => 'dummy',
          'mapping' => [
            // 'somefield' => [ 'type' => 'transform', 'field' => 'nonexistant' ]
          ]
        ]
      ]
    ]);

    $pipeline->getTransformInstance('nonexisting');
  }

  /**
   * [testPipelineFailsWithNonexistantTransform description]
   */
  public function testPipelineFailsWithNonexistantTransform(): void {
    $this->expectException(\codename\core\exception::class);
    $this->expectExceptionMessage('EXCEPTION_ENWAREHOUSE_PIPELINE_MISSING_TRANSFORM');

    $this->executePipelineWithConfig([
      'source' => [
        'type' => 'arraydata'
      ],
      'transform' => [
      ],
      'target' => [
        'test' => [
          'type' => 'dummy',
          'mapping' => [
            'somefield' => [ 'type' => 'transform', 'field' => 'nonexistant' ]
          ]
        ]
      ]
    ]);
  }

  /**
   * [testPipelineFailsWithNonexistantTransformDeep description]
   */
  public function testPipelineFailsWithNonexistantTransformDeep(): void {
    $this->expectException(\codename\core\exception::class);
    $this->expectExceptionMessage('EXCEPTION_ENWAREHOUSE_PIPELINE_MISSING_TRANSFORM');

    $this->executePipelineWithConfig([
      'source' => [
        'type' => 'arraydata'
      ],
      'transform' => [
      ],
      'target' => [
        'test' => [
          'type' => 'dummy',
          'mapping' => [
            'somefield' => [ 'type' => 'transform_deep', 'field' => ['nonexistant'] ]
          ]
        ]
      ]
    ]);
  }

  /**
   * [testPipelineFailsWithInvalidTransformType description]
   */
  public function testPipelineFailsWithInvalidTransformType(): void {
    $this->expectException(\codename\core\exception::class);

    // NOTE: at the moment, this fires a missing class exception
    // but I think it really should be EXCEPTION_CORE_IO_PIPELINE_GETTRANSFORM_MISSING_CLASS
    $this->expectExceptionMessage('EXCEPTION_GETINHERITEDCLASS_CLASSFILENOTFOUND');

    $this->executePipelineWithConfig([
      'source' => [
        'type' => 'arraydata'
      ],
      'transform' => [
        'example_invalid_type' => [
          'type'    => 'someinvalidtransformtype',
          'config'  => []
        ]
      ],
      'target' => [
        'test' => [
          'type' => 'dummy',
          'mapping' => [
            'somefield' => [ 'type' => 'transform', 'field' => 'example_invalid_type' ]
          ]
        ]
      ]
    ]);
  }

  /**
   * [executePipelineWithConfig description]
   * @param  array        $config [description]
   * @param  array|null   $data   [data samples for source]
   * @return pipeline         [description]
   */
  protected function executePipelineWithConfig(array $config, ?array $data = null): pipeline {
    $pipeline = new \codename\core\io\pipeline(null, $config);
    $datasource = new \codename\core\io\datasource\arraydata([]);
    if($data === null) {
      // example dataset
      $data = [
        [ 'value' => 'test' ],
      ];
    }
    $datasource->setData($data);

    $pipeline->setDatasource($datasource);
    $pipeline->run();
    return $pipeline;
  }


}
