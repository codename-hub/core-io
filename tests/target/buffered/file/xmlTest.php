<?php

namespace codename\core\io\tests\target\buffered\file;

use codename\core\exception;
use codename\core\io\datasource\xml;
use codename\core\test\base;
use ReflectionException;

class xmlTest extends base
{
    /**
     * [testXml description]
     * @throws ReflectionException
     * @throws exception
     */
    public function testXml(): void
    {
        $tags = [
          'file_name' => 'moep',
        ];

        $target = new \codename\core\io\target\buffered\file\xml('xml_test', [
          'version' => '1.0',
          'encoding' => 'UTF-8',
          'template_elements_path' => ['processes'],
          'split_count' => 2,
          'mapping' => [
            'key1' => ['path' => ['process']],
            'key2' => ['path' => ['process']],
            'key3' => ['path' => ['process']],
            'key4' => ['path' => ['process']],
          ],
          'tags' => $tags,
        ]);

        $samples = $this->getSampleData();
        foreach ($samples as $sample) {
            $target->store($sample, $tags);
        }
        $target->finish();


        $files = $target->getFileResultArray();
        static::assertCount(2, $files);

        $res = [];
        foreach ($files as $file) {
            $filepath = $file->get();

            $datasource = new xml($filepath, [
              'xpath_query' => '/processes/process',
              'xpath_mapping' => [
                'key1' => 'key1',
                'key2' => 'key2',
                'key3' => 'key3',
                'key4' => 'key4',
              ],
            ]);

            foreach ($datasource as $r) {
                $res[] = $r;
            }

            unlink($filepath);
        }

        static::assertEquals($samples, $res);
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
     * [testXmlWithTemplate description]
     * @throws ReflectionException
     * @throws exception
     */
    public function testXmlWithTemplate(): void
    {
        $tags = [
          'file_name' => 'moep',
        ];

        $target = new \codename\core\io\target\buffered\file\xml('xml_test', [
          'version' => '1.0',
          'encoding' => 'UTF-8',
          'template' => [
            'process' => [

            ],
          ],
          'split_count' => 2,
          'mapping' => [
            'process' => [],
          ],
          'tags' => $tags,
        ]);

        $samples = $this->getSampleData();
        $target->store([
          'process' => $samples[0],
        ], $tags);
        $target->finish();

        $files = $target->getFileResultArray();
        static::assertCount(1, $files);

        $res = [];
        foreach ($files as $file) {
            $filepath = $file->get();

            $datasource = new xml($filepath, [
              'xpath_query' => '/process',
              'xpath_mapping' => [
                'key1' => 'key1',
                'key2' => 'key2',
                'key3' => 'key3',
                'key4' => 'key4',
              ],
            ]);

            foreach ($datasource as $r) {
                $res[] = $r;
            }

            unlink($filepath);
        }

        static::assertEquals($samples[0], $res[0]);
    }
}
