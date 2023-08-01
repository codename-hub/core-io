<?php

namespace codename\core\io\tests\target\buffered\file;

use codename\core\exception;
use codename\core\io\datasource\csv;
use codename\core\io\target;
use codename\core\io\tests\target\abstractWriteReadTest;

class csvWriteReadTest extends abstractWriteReadTest
{
    /**
     * {@inheritDoc}
     */
    protected function getWriteReadTargetInstance(array $configOverride = []): target
    {
        return new target\buffered\file\csv('csv_test', [
          'delimiter' => ';',
          'mapping' => [
            'key1' => [],
            'key2' => [],
            'key3' => [],
            'key4' => [],
          ],
        ]);
    }

    /**
     * {@inheritDoc}
     * @param target $target
     * @return array
     * @throws exception
     */
    protected function readTargetData(target $target): array
    {
        $files = $target->getFileResultArray();
        static::assertCount(1, $files);
        $res = [];
        foreach ($files as $file) {
            $filepath = $file->get();
            $datasource = new csv($filepath, [
              'delimiter' => ';',
              'autodetect_utf8_bom' => true,
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
