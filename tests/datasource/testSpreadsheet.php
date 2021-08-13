<?php
namespace codename\core\io\tests\datasource;

/**
 * [testSpreadsheet description]
 */
class testSpreadsheet extends \PHPUnit\Framework\TestCase {

  /**
   * tests general function of the spreadsheet datasource
   * @return [type] [description]
   */
  public function testSpreadsheetGeneral () {
    $datasource = new \codename\core\io\datasource\spreadsheet(__DIR__ . "/" . 'testSpreadsheet2.xlsx');

    $this->assertEquals('0', $datasource->key());

    $this->assertEquals('0', $datasource->currentProgressPosition());

    $this->assertEquals('0', $datasource->currentProgressLimit());

    $this->assertNull($datasource->setConfig([]));

  }

  /**
   * Test function to be moved to enshared, when available.
   */
  public function testCsvStaticFormConfigProvider(): void {
    $this->markTestIncomplete('Feature is to a different package.');
    $datasource = new \codename\core\io\datasource\spreadsheet(__DIR__ . "/" . 'testSpreadsheet2.xlsx');
    $formFieldConfigArrayStatic = $datasource->getFormFieldConfigArrayStatic([]);
    $this->assertEquals([
      [
        'field_title'     => 'Multisheet',
        'field_name'      => 'multisheet',
        'field_type'      => 'checkbox',
        'field_datatype'  => 'boolean',
        'field_value'     => false
      ],
      [
        'field_title'     => 'Kopfzeile vorhanden?',
        'field_name'      => 'headed',
        'field_type'      => 'checkbox',
        'field_datatype'  => 'boolean',
        'field_value'     => true
      ],
      [
        'field_title'     => 'Kopfzeile in Zeile x',
        'field_name'      => 'header_row',
        'field_type'      => 'input',
        'field_datatype'  => 'number_natural',
        'field_value'     => 1
      ],
      [
        'field_title'     => 'Spezifisches Sheet',
        'field_name'      => 'custom_sheet_index',
        'field_type'      => 'input',
        'field_datatype'  => 'number_natural',
        'field_value'     => null
      ],
      [
        'field_title'     => 'Tabellenspaltennamen einbeziehen',
        'field_name'      => 'include_spreadsheet_columns',
        'field_type'      => 'checkbox',
        'field_datatype'  => 'boolean',
        'field_value'     => false
      ],
      [
        'field_title'     => 'Zeilen von oben überspringen',
        'field_name'      => 'skip_rows',
        'field_type'      => 'input',
        'field_datatype'  => 'number_natural',
        'field_value'     => 0
      ]
    ], $formFieldConfigArrayStatic);
  }

  /**
   * test an simple spreadsheet file
   * @return [type] [description]
   */
  public function testDataSourceIsValid() {
    $datasource = new \codename\core\io\datasource\spreadsheet(
      __DIR__ . "/" . 'testSpreadsheet1.xlsx',
      [
        'custom_sheet_index'  => 0,
        'multisheet'          => 0,
        'skip_rows'           => 3,
        'header_row'          => 2,
      ]
    );

    $datasource->next();

    $data = $datasource->current();
    $this->assertEquals($data['A'], 'Value2-1');
    $this->assertEquals($data['B'], 'Value2-2');
    $this->assertEquals($data['C'], 'Value2-3');

  }

  /**
   * test an simple spreadsheet file
   * @return [type] [description]
   */
  public function testDataSourceIsValidWithXls() {
    $datasource = new \codename\core\io\datasource\spreadsheet(
      __DIR__ . "/" . 'testSpreadsheet1.xls',
      [
        'custom_sheet_index'  => 0,
        'multisheet'          => 0,
        'skip_rows'           => 3,
        'header_row'          => 2,
      ]
    );

    $datasource->next();

    $data = $datasource->current();
    $this->assertEquals($data['A'], 'Value2-1');
    $this->assertEquals($data['B'], 'Value2-2');
    $this->assertEquals($data['C'], 'Value2-3');

  }

  /**
   * test an simple spreadsheet file
   * @return [type] [description]
   */
  public function testDataSourceNext() {
    $datasource = new \codename\core\io\datasource\spreadsheet(
      __DIR__ . "/" . 'testSpreadsheet1.xlsx',
      [
        'skip_rows'   => 1,
        'header_row'  => 2,
      ]
    );

    $i = 0;
    foreach($datasource as $dataset) {
      $i++;
      $this->assertEquals($dataset['Head1'], "Value{$i}-1");
      $this->assertEquals($dataset['Head2'], "Value{$i}-2");
      $this->assertEquals($dataset['Head3'], "Value{$i}-3");
    }

    //
    // make sure we have iterated two times
    //
    $this->assertEquals(2, $i);

  }

  /**
   * test an simple spreadsheet file
   * @return [type] [description]
   */
  public function testDataSourceMulti() {
    $datasource = new \codename\core\io\datasource\spreadsheet(
      __DIR__ . "/" . 'testSpreadsheet3.xlsx',
      [
        'multisheet'          => true,
        'custom_sheet_index'  => 1,
        'skip_rows'           => 3,
        'header_row'          => 2,
      ]
    );

    $i = 0;
    foreach($datasource as $dataset) {
      $i++;
      if($i === 1) {
        $this->assertEquals($dataset['A'], '12');
        $this->assertEquals($dataset['B'], '345');
        $this->assertEquals($dataset['C'], '678');
      } elseif($i === 2) {
        $this->assertEquals($dataset['A'], '901');
        $this->assertEquals($dataset['B'], '234');
        $this->assertEquals($dataset['C'], '567');
      } elseif($i === 3) {
        $this->assertEquals($dataset['A'], 'jkl');
        $this->assertEquals($dataset['B'], 'mno');
        $this->assertEquals($dataset['C'], 'pqr');
      } elseif($i === 4) {
        $this->assertEquals($dataset['A'], 'stu');
        $this->assertEquals($dataset['B'], 'vwx');
        $this->assertEquals($dataset['C'], 'yz');
      }
    }

  }

}
