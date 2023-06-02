<?php

namespace codename\core\io\tests\target\buffered\file;

use codename\core\exception;
use codename\core\io\datasource\multicsv;
use codename\core\io\target;
use codename\core\io\target\buffered\file\csv;
use codename\core\io\tests\target\abstractWriteReadTest;

class multicsvWriteReadTest extends abstractWriteReadTest
{
    /**
     * {@inheritDoc}
     */
    protected function getWriteReadTargetInstance(array $configOverride = []): target
    {
        //
        // We're simply writing a CSV split at 2 items/rows each
        // and re-read them using multicsv
        //
        return new csv('csv_test', [
          'delimiter' => ';',
          'split_count' => 2,
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
        static::assertCount(2, $files);
        $filepaths = [];
        foreach ($files as $file) {
            $filepaths[] = $file->get();
        }
        $datasource = new multicsv($filepaths, [
          'delimiter' => ';',
          'autodetect_utf8_bom' => true,
        ]);
        $res = [];
        foreach ($datasource as $r) {
            $res[] = $r;
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
