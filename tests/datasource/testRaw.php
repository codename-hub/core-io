<?php
namespace codename\core\io\tests\datasource;

class testRaw extends \PHPUnit\Framework\TestCase
{

  /**
   * tests general function of the raw datasource
   * @return [type] [description]
   */
  public function testRawGeneral () {
    $datasource = new \codename\core\io\datasource\raw(__DIR__ . "/" . 'testRaw1.txt');

    $this->assertEquals('0', $datasource->key());

    $this->assertEquals('0', $datasource->currentProgressPosition());

    $this->assertEquals('52', $datasource->currentProgressLimit());

    // rewind the datasources
    $datasource->rewind();

  }

  /**
   * test an simple raw file
   * @return [type] [description]
   */
  public function testDataSourceFileNotExists() {
    $this->expectException(\codename\core\exception::class);
    $this->expectExceptionMessage('FILE_COULD_NOT_BE_OPEN');
    $datasource = new \codename\core\io\datasource\raw(__DIR__ . "/" . 'testRaw1_filenotexists.txt');

  }

  /**
   * test an simple raw file
   * @return [type] [description]
   */
  public function testDataSourceInvalidConfig() {
    $this->expectException(\codename\core\exception::class);
    $this->expectExceptionMessage('CORE_IO_DATASOURCE_INVALID_MAP_CONFIG');

    $datasource = new \codename\core\io\datasource\raw(
      __DIR__ . "/" . 'testRaw1.txt',
      [
        'format'  => [
          'trim'    => true,
          'convert' => [
            'from'  => 'ASCII',
            'to'    => 'UTF-8',
          ],
          'map'     => [
            'field1'  => [
              'type'    => 'wrong',
              'length'  => 10,
            ],
          ],
        ],
      ]
    );

  }

  /**
   * test an simple raw file
   * @return [type] [description]
   */
  public function testDataSourceIsValid() {
    $datasource = new \codename\core\io\datasource\raw(
      __DIR__ . "/" . 'testRaw1.txt',
      [
        'format'  => [
          'trim'    => true,
          'convert' => [
            'from'  => 'ASCII',
            'to'    => 'UTF-8',
          ],
          'map'     => [
            'field1'  => [
              'type'    => 'fixed',
              'length'  => 10,
            ],
            'field2'  => [
              'type'    => 'fixed',
              'length'  => 10,
            ],
            'field3'  => [
              'type'    => 'fixed',
              'length'  => 10,
            ],
          ],
        ],
      ]
    );
    $datasource->next();

    $this->assertNotEmpty($datasource->getRawCurrent());

    $data = $datasource->current();
    $this->assertEquals($data['field1'], 'col11');
    $this->assertEquals($data['field2'], 'col21');
    $this->assertEquals($data['field3'], 'col31');

  }

  /**
   * test an simple raw file
   * @return [type] [description]
   */
  public function testDataSourceIsValidWithMappings() {
    $datasource = new \codename\core\io\datasource\raw(
      __DIR__ . "/" . 'testRaw1.txt',
      [
        'format'    => [
          'trim'    => true,
          'map'     => [
            'key'   => [
              'type'    => 'fixed',
              'length'  => 5,
            ]
          ],
        ],
        'mappings'  => [
          'key'     => [
            'col11' => [
              'trim'    => true,
              'map'     => [
                'field1'  => [
                  'type'    => 'fixed',
                  'length'  => 10,
                ],
                'field2'  => [
                  'type'    => 'fixed',
                  'length'  => 10,
                ],
                'field3'  => [
                  'type'    => 'fixed',
                  'length'  => 10,
                ],
              ],
            ],
            'col12' => [
              'trim'    => true,
              'convert' => [
                'from'  => 'ASCII',
                'to'    => 'UTF-8',
              ],
              'map'     => [
                'field1'  => [
                  'type'    => 'fixed',
                  'length'  => 10,
                ],
                'field2'  => [
                  'type'    => 'fixed',
                  'length'  => 10,
                ],
                'field3'  => [
                  'type'    => 'fixed',
                  'length'  => 10,
                ],
              ],
            ],
          ],
        ],
      ]
    );
    $datasource->next();

    $this->assertNotEmpty($datasource->getRawCurrent());

    $data = $datasource->current();
    $this->assertEquals($data['field1'], 'col11');
    $this->assertEquals($data['field2'], 'col21');
    $this->assertEquals($data['field3'], 'col31');

    $datasource->next();

    $data = $datasource->current();
    $this->assertEquals($data['field1'], 'col12');
    $this->assertEquals($data['field2'], 'col22');
    $this->assertEquals($data['field3'], 'col32');

  }

  /**
   * "head1","head2"
   * "l1_d1","l1_d2"
   * "l2_d1","l2_d2"
   * "l3_d1","l3_d2"
   *
   * @return void testing the next function
   */
  public function testDataSourceNext() {
    $datasource = new \codename\core\io\datasource\raw(
      __DIR__ . "/" . 'testRaw1.txt',
      [
        'format'  => [
          'trim'    => true,
          'map'     => [
            'field1'  => [
              'type'    => 'fixed',
              'length'  => 10,
            ],
            'field2'  => [
              'type'    => 'fixed',
              'length'  => 10,
            ],
            'field3'  => [
              'type'    => 'fixed',
              'length'  => 10,
            ],
          ],
        ],
      ]
    );

    $i = 0;
    foreach($datasource as $dataset) {
      $i++;
      $this->assertEquals($dataset['field1'], "col1{$i}");
      $this->assertEquals($dataset['field2'], "col2{$i}");
      $this->assertEquals($dataset['field3'], "col3{$i}");
    }

    //
    // make sure we have iterated two times
    //
    $this->assertEquals(2, $i);
  }

}
