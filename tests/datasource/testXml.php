<?php
namespace codename\core\io\tests\datasource;

class testXml extends \PHPUnit\Framework\TestCase
{

  /**
   * tests general function of the xml datasource
   * @return [type] [description]
   */
  public function testXmlGeneral () {
    $datasource = new \codename\core\io\datasource\xml(
      __DIR__ . "/" . 'testXml1.xml',
      [
        'xpath_query'   => '/example/data',
        'xpath_mapping' => [
          'field1'  => 'field1',
          'field2'  => 'field2',
          'field3'  => 'field3',
        ],
      ]
    );

    $this->assertEquals('0', $datasource->currentProgressPosition());

    $this->assertEquals('0', $datasource->currentProgressLimit());

    // rewind the datasources
    $datasource->rewind();

    $this->assertEquals('0', $datasource->key());

  }

  /**
   * test an simple xml file
   * @return [type] [description]
   */
  public function testDataSourceIsValid() {
    $datasource = new \codename\core\io\datasource\xml(
      __DIR__ . "/" . 'testXml1.xml',
      [
        'xpath_query'   => '/example/data',
        'xpath_mapping' => [
          'field1'  => 'field1',
          'field2'  => 'field2',
          'field3'  => 'field3',
        ],
      ]
    );
    $datasource->next();

    $data = $datasource->current();
    $this->assertEquals($data['field1'], 'example11');
    $this->assertEquals($data['field2'], 'example12');
    $this->assertEquals($data['field3'], 'example13');

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
    $datasource = new \codename\core\io\datasource\xml(
      __DIR__ . "/" . 'testXml1.xml',
      [
        'xpath_query'   => '/example/data',
        'xpath_mapping' => [
          'field1'  => 'field1',
          'field2'  => 'field2',
          'field3'  => 'field3',
        ],
      ]
    );

    $i = 0;
    foreach($datasource as $dataset) {
      $i++;
      $this->assertEquals($dataset['field1'], "example{$i}1");
      $this->assertEquals($dataset['field2'], "example{$i}2");
      $this->assertEquals($dataset['field3'], "example{$i}3");
    }

    //
    // make sure we have iterated three times
    //
    $this->assertEquals(3, $i);
  }

}
