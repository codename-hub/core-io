<?php

namespace codename\core\io\tests\target\buffered\file;

use codename\core\io\datasource\xml;
use codename\core\io\target;
use codename\core\io\tests\target\abstractWriteReadTest;
use Exception;

class xmlWriteReadTest extends abstractWriteReadTest
{
    /**
     * {@inheritDoc}
     */
    protected function getWriteReadTargetInstance(array $configOverride = []): target
    {
        return new target\buffered\file\xml('xml_test', [
          'version' => '1.0',
          'encoding' => 'UTF-8',
          'template_elements_path' => ['data'],
          'mapping' => [
            'key1' => ['path' => ['element']],
            'key2' => ['path' => ['element']],
            'key3' => ['path' => ['element']],
            'key4' => ['path' => ['element']],
          ],
        ]);
    }

    /**
     * {@inheritDoc}
     * @param target $target
     * @return array
     * @throws Exception
     */
    protected function readTargetData(target $target): array
    {
        $files = $target->getFileResultArray();
        static::assertCount(1, $files);
        $res = [];
        foreach ($files as $file) {
            $filepath = $file->get();
            $datasource = new xml($filepath, [
              'xpath_query' => '/data/element',
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
        }
        return $res;
    }

    /**
     * {@inheritDoc}
     */
    protected function cleanupTarget(target $target): void
    {
        foreach ($target->getFileResultArray() as $file) {
            unlink($file->get());
        }
    }
}
