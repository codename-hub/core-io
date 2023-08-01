<?php

namespace codename\core\io\tests\target\xml;

use codename\core\exception;
use codename\core\io\target\xml\text;
use codename\core\test\base;
use ReflectionException;
use Sabre\Xml\ParseException;
use Sabre\Xml\Service;

class textTest extends base
{
    /**
     * [testXmlCase1 description]
     * @throws ReflectionException
     * @throws ParseException
     * @throws exception
     */
    public function testXmlCase1(): void
    {
        $target = new text('xml_test', [
          'version' => '1.0',
          'encoding' => 'UTF-8',
          'mapping' => [
            'key1' => ['path' => ['process']],
            'key2' => ['path' => ['process']],
            'key3' => ['path' => ['process']],
            'key4' => ['path' => ['process']],
          ],
        ]);

        $samples = $this->getSampleData();
        foreach ($samples as $sample) {
            $result = $target->store($sample);
            static::assertTrue($result);
        }
        $target->finish();

        $results = $target->getTextResultArray();

        foreach ($results as $k => $v) {
            $reader = new Service();
            $xmlResults = $reader->parse($v->get());

            foreach ($xmlResults as $kXml => $vXml) {
                $sampleValues = array_values($samples[$k]);
                static::assertEquals(
                    $sampleValues[$kXml],
                    $vXml['value'],
                    print_r([
                      $sampleValues[$kXml],
                      $vXml['value'],
                    ], true)
                );
            }
        }
    }

    /**
     * [getSampleData description]
     * @return array [description]
     */
    protected function getSampleData(): array
    {
        return [
          [
            'key1' => 'value1',
            'key2' => '2',
            'key3' => '3.1415',
            'key4' => '',
          ],
          [
            'key1' => 'value2',
            'key2' => '3',
            'key3' => '4.2344',
            'key4' => '',
          ],
          [
            'key1' => 'value3',
            'key2' => '4',
            'key3' => '5.4545',
            'key4' => '',
          ],
        ];
    }

    /**
     * [testXmlCase2 description]
     * @throws ParseException
     * @throws ReflectionException
     * @throws exception
     */
    public function testXmlCase2(): void
    {
        $target = new text('xml_test', [
          'version' => '1.0',
          'encoding' => 'UTF-8',
          'mapping' => [
            'process' => [],
          ],
        ]);

        $samples = $this->getSampleData();
        foreach ($samples as $sample) {
            $result = $target->store([
              'process' => $sample,
            ]);
            static::assertTrue($result);
        }
        $target->finish();

        $results = $target->getTextResultArray();

        foreach ($results as $k => $v) {
            $reader = new Service();
            $xmlResults = $reader->parse($v->get());

            foreach ($xmlResults as $kXml => $vXml) {
                $sampleValues = array_values($samples[$k]);
                static::assertEquals(
                    $sampleValues[$kXml],
                    $vXml['value'],
                    print_r([
                      $sampleValues[$kXml],
                      $vXml['value'],
                    ], true)
                );
            }
        }
    }
}
