<?php
namespace codename\core\io\tests;

/**
 * [testRemap description]
 */
class testRemap extends \PHPUnit\Framework\TestCase
{
  /**
   * tests general function of the remap datasource
   * @return [type] [description]
   */
  public function testRemapDatasource () {
    $datasource = new \codename\core\io\datasource\arraydata();
    $datasource->setData($this->getTestData());
    $remappedDatasource = new \codename\core\io\datasource\remap(
      $datasource, [
        'remap' => [
          'oldkey1' => 'newkey1',
          'oldkey2' => 'newkey2',
          'oldkey3' => 'newkey3',
        ]
      ]
    );

    $comparisonDatasource = new \codename\core\io\datasource\arraydata();
    $comparisonDatasource->setData($this->getTestData());

    // rewind the datasources
    $remappedDatasource->rewind();
    $comparisonDatasource->rewind();

    // max sure we only iterate over the count of the testdata array
    $iterations = count($this->getTestData());
    $i = 0;

    while($i < $iterations) {
      $remappedDataset = $remappedDatasource->current();
      $originalDataset = $comparisonDatasource->current();

      $this->assertEquals($remappedDataset['newkey1'], $originalDataset['oldkey1']);
      $this->assertEquals($remappedDataset['newkey2'], $originalDataset['oldkey2']);
      $this->assertEquals($remappedDataset['newkey3'], $originalDataset['oldkey3']);

      $i++;
    }
  }

  /**
   * tests outputting a dataset based on the input
   * e.g. the remapped values are put ON TOP of the input (or replaced)
   * @return void
   */
  public function testRemapReplaceSourceInclusion () {
    $datasource = new \codename\core\io\datasource\arraydata();
    $datasource->setData($this->getTestData());

    $remappedDatasource = new \codename\core\io\datasource\remap(
      $datasource, [
        'remap' => [
          'oldkey1' => 'newkey1',
          'oldkey2' => 'newkey2',
          'oldkey3' => 'newkey3',
        ],
        // the relevant config key
        'replace' => true
      ]
    );

    $originalDatasource = new \codename\core\io\datasource\arraydata();
    $originalDatasource->setData($this->getTestData());

    $tempDatasource = new \codename\core\io\datasource\arraydata();
    $tempDatasource->setData($this->getTestData());
    $normalRemappedDatasource = new \codename\core\io\datasource\remap(
      $tempDatasource, [
        'remap' => [
          'oldkey1' => 'newkey1',
          'oldkey2' => 'newkey2',
          'oldkey3' => 'newkey3',
        ]
      ]
    );

    // rewind the datasources
    $remappedDatasource->rewind();
    $originalDatasource->rewind();
    $normalRemappedDatasource->rewind();

    // max sure we only iterate over the count of the testdata array
    $iterations = count($this->getTestData());
    $i = 0;

    while($i < $iterations) {
      $remappedDataset = $remappedDatasource->current();

      // original
      $originalDataset = $originalDatasource->current();

      // remapped without including the input data
      $normallyRemappedDataset = $normalRemappedDatasource->current();

      foreach($originalDataset as $key => $value) {
        $this->assertEquals($value, $remappedDataset[$key]);
      }
      foreach($normallyRemappedDataset as $key => $value) {
        $this->assertEquals($value, $remappedDataset[$key]);
      }

      // Don't forget to move the iterator
      $remappedDatasource->next();
      $originalDatasource->next();
      $normalRemappedDatasource->next();

      $i++;
    }
  }

  /**
   * tests the inclusion of original data in a subkey
   * @return [type] [description]
   */
  public function testRemapSourceDataKey () {
    $datasource = new \codename\core\io\datasource\arraydata();
    $datasource->setData($this->getTestData());

    $sourceDataKey = 'original';

    $remappedDatasource = new \codename\core\io\datasource\remap(
      $datasource, [
        'remap' => [
          'oldkey1' => 'newkey1',
          'oldkey2' => 'newkey2',
          'oldkey3' => 'newkey3',
        ],
        'source_data_key' => $sourceDataKey
      ]
    );

    $comparisonDatasource = new \codename\core\io\datasource\arraydata();
    $comparisonDatasource->setData($this->getTestData());

    // rewind the datasources
    $remappedDatasource->rewind();
    $comparisonDatasource->rewind();

    // max sure we only iterate over the count of the testdata array
    $iterations = count($this->getTestData());
    $i = 0;

    while($i < $iterations) {
      $remappedDataset = $remappedDatasource->current();
      $originalDataset = $comparisonDatasource->current();
      $this->assertEquals($remappedDataset[$sourceDataKey], $originalDataset);

      // Don't forget to move the iterator
      $remappedDatasource->next();
      $comparisonDatasource->next();

      $i++;
    }
  }

  /**
   * returns an array of basic test data
   * @return array
   */
  protected function getTestData() : array {
    return [
      [
        'oldkey1' => 'abc',
        'oldkey2' => 'def',
        'oldkey3' => 'ghi'
      ],
      [
        'oldkey1' => 'jkl',
        'oldkey2' => 'mno',
        'oldkey3' => 'pqr'
      ],
      [
        'oldkey1' => 'stu',
        'oldkey2' => 'vwx',
        'oldkey3' => 'yz'
      ]
    ];
  }
}
