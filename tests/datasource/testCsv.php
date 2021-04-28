<?php
namespace codename\core\io\tests\datasource;

class testCsv extends \PHPUnit\Framework\TestCase
{

  /**
   * tests general function of the csv datasource
   * @return [type] [description]
   */
  public function testCsvGeneral () {
    $datasource = new \codename\core\io\datasource\csv(__DIR__ . "/" . 'testcsv1.csv');

    $formFieldConfigArrayStatic = $datasource->getFormFieldConfigArrayStatic([]);
    $this->assertEquals([
      [
        'field_title' => 'Trennzeichen',
        'field_name'  => 'delimiter',
        'field_type'  => 'input',
        'field_value' => ';'
      ],
      [
        'field_title'     => 'Kopfzeile vorhanden?',
        'field_name'      => 'headed',
        'field_type'      => 'checkbox',
        'field_datatype'  => 'boolean',
        'field_value'     => true
      ],
      [
        'field_title'     => 'UTF8-BOM automatisch erkennen',
        'field_name'      => 'autodetect_utf8_bom',
        'field_type'      => 'checkbox',
        'field_datatype'  => 'boolean',
        'field_value'     => false
      ],
      [
        'field_title'       => 'Leere Zeilen überspringen',
        'field_description' => 'Aktiviert die Überprüfung und Überspringen von leeren Zeilen',
        'field_name'      => 'skip_empty_rows',
        'field_type'      => 'checkbox',
        'field_datatype'  => 'boolean',
        'field_value'     => false
      ],
      [
        'field_title'     => 'Codierung',
        'field_name'      => 'encoding',
        'field_type'      => 'form',
        'form'            => [
          'config' => [ 'dummy' => true ],
          'fields' => [
            [
              'field_title' => 'Codierung von',
              'field_name'  => 'from',
              'field_type'  => 'input',
              'field_value' => 'ASCII'
            ],
            [
              'field_title' => 'Codierung zu',
              'field_name'  => 'to',
              'field_type'  => 'input',
              'field_value' => 'UTF-8'
            ],
          ],
        ],
        'field_value' => [ 'from' => 'ASCII', 'to' => 'UTF-8' ]
      ],
      [
        'field_title'     => 'Zeilen von oben überspringen',
        'field_name'      => 'skip_rows',
        'field_type'      => 'input',
        'field_datatype'  => 'number_natural',
        'field_value'     => 0
      ]
    ], $formFieldConfigArrayStatic);

    $this->assertEquals('0', $datasource->currentProgressPosition());

    $this->assertEquals('31', $datasource->currentProgressLimit());

  }

  /**
   * test an simple csv file
   * @return [type] [description]
   */
  public function testDataSourceFileNotExists() {
    $this->expectException(\codename\core\exception::class);
    $this->expectExceptionMessage('FILE_COULD_NOT_BE_OPEN');
    $datasource = new \codename\core\io\datasource\csv(__DIR__ . "/" . 'testcsv1_filenotexists.csv');

  }

  /**
   * test an simple csv file
   * @return [type] [description]
   */
  public function testDataSourceIsValid() {
    $datasource = new \codename\core\io\datasource\csv(__DIR__ . "/" . 'testcsv1.csv');
    $datasource->next();

    $head = $datasource->getHeadings();
    $this->assertEquals($head[0], 'head1');
    $this->assertEquals($head[1], 'head2');

    $data = $datasource->current();
    $this->assertEquals($data['head1'], 'bla');
    $this->assertEquals($data['head2'], 'foo');

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
    $datasource = new \codename\core\io\datasource\csv(
      __DIR__ . "/" . 'testcsv2.csv',
      [
        'autodetect_utf8_bom'   => true,
        'skip_empty_rows'       => true,
        'skip_rows'             => 1,
        'encoding'              => [ 'from' => 'UTF-8', 'to' => 'UTF-8' ],
      ]
    );

    $i = 0;
    foreach($datasource as $dataset) {
      $i++;
      $this->assertEquals($dataset['head0'], "l{$i}_d1");
      $this->assertEquals($dataset['head1'], "l{$i}_d2");
    }

    //
    // make sure we have iterated three times
    //
    $this->assertEquals(3, $i);
  }
  /**
   * "head1","head2"
   * "l1_d1","l1_d2"
   * "l2_d1","l2_d2"
   * "l3_d1","l3_d2"
   *
   * @return void testing the next function
   */
  public function testDataSourceWrongOffset() {
    $datasource = new overriddenCsv(
      __DIR__ . "/" . 'testcsv2.csv',
      [
        'autodetect_utf8_bom'   => true,
        'skip_empty_rows'       => true,
        'skip_rows'             => 1,
        'encoding'              => [ 'from' => 'UTF-8', 'to' => 'UTF-8' ],
      ]
    );

    $i = 0;
    foreach($datasource as $dataset) {
      $i++;
      $this->assertEquals($dataset['head0'], "l{$i}_d1");
      $this->assertEquals($dataset['head1'], "l{$i}_d2");
    }

    //
    // make sure we have iterated three times
    //
    $this->assertEquals(3, $i);
  }

}

/**
 * [overriddenCsv description]
 */
class overriddenCsv extends \codename\core\io\datasource\csv {

  /**
   * [rewind description]
   * @return [type] [description]
   */
   public function rewind() {
     fseek($this->handle, 1);
     if($this->autodetectUtf8Bom) {
       $this->handleUtf8Bom();
     }
     $this->index = 0;
     $this->next();
   }
}
