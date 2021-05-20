<?php
namespace codename\core\io\tests\target\xml;

use codename\core\tests\base;

class textTest extends base
{

  /**
   * [getSampleData description]
   * @return array [description]
   */
  protected function getSampleData(): array {
    return [
      [
        'key1' => 'value1',
        'key2' => '2',
        'key3' => '3.1415',
        'key4' => ''
      ],
      [
        'key1' => 'value2',
        'key2' => '3',
        'key3' => '4.2344',
        'key4' => ''
      ],
      [
        'key1' => 'value3',
        'key2' => '4',
        'key3' => '5.4545',
        'key4' => ''
      ],
    ];
  }

  /**
   * [testXmlCase1 description]
   */
  public function testXmlCase1(): void {

    $target = new \codename\core\io\target\xml\text('xml_test', [
      'version'   => '1.0',
      'encoding'  => 'UTF-8',
      'mapping'   => [
        'key1' => [ 'path' => [ 'process' ] ],
        'key2' => [ 'path' => [ 'process' ] ],
        'key3' => [ 'path' => [ 'process' ] ],
        'key4' => [ 'path' => [ 'process' ] ],
      ],
    ]);

    $samples = $this->getSampleData();
    foreach($samples as $sample) {
      $result = $target->store($sample);
      $this->assertTrue($result);
    }
    $target->finish();

    $results = $target->getTextResultArray();

    foreach($results as $k => $v) {
      $reader = new \Sabre\Xml\Reader();
      $reader->xml($v->get());
      $xmlResults = $reader->parse();

      foreach($xmlResults['value'] as $kXml => $vXml) {
        $sampleValues = array_values($samples[$k]);
        $this->assertEquals($sampleValues[$kXml], $vXml['value'], print_r([
          $sampleValues[$kXml],
          $vXml['value']
        ], true));
      }

    }
  }

  /**
   * [testXmlCase2 description]
   */
  public function testXmlCase2(): void {

    $target = new \codename\core\io\target\xml\text('xml_test', [
      'version'   => '1.0',
      'encoding'  => 'UTF-8',
      'mapping'   => [
        'process' => [  ],
      ],
    ]);

    $samples = $this->getSampleData();
    foreach($samples as $sample) {
      $result = $target->store([
        'process' => $sample
      ]);
      $this->assertTrue($result);
    }
    $target->finish();

    $results = $target->getTextResultArray();

    foreach($results as $k => $v) {
      $reader = new \Sabre\Xml\Reader();
      $reader->xml($v->get());
      $xmlResults = $reader->parse();

      foreach($xmlResults['value'] as $kXml => $vXml) {
        $sampleValues = array_values($samples[$k]);
        $this->assertEquals($sampleValues[$kXml], $vXml['value'], print_r([
          $sampleValues[$kXml],
          $vXml['value']
        ], true));
      }

    }
  }


}
