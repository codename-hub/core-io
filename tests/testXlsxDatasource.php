<?php
namespace codename\core\io\tests;

/**
 * [testXlsxDatasource description]
 */
class testXlsxDatasource extends \PHPUnit\Framework\TestCase {
  /**
   * test an simple csv file
   * @return [type] [description]
   */
  public function testDataSource()
  {
    $spreadsheetDatasource = new \codename\core\io\datasource\spreadsheet(__DIR__ . "/" . 'spreadsheet2.xlsx');

    $result = [];

    $spreadsheetDatasource->next();
    while($spreadsheetDatasource->valid()) {
      $result[] = $spreadsheetDatasource->current();
      $spreadsheetDatasource->next();
    }

    print_r($result);
  }
}