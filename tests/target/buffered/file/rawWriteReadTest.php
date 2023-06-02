<?php

namespace codename\core\io\tests\target\buffered\file;

use codename\core\exception;
use codename\core\io\datasource\raw;
use codename\core\io\target;
use codename\core\io\tests\target\abstractWriteReadTest;

class rawWriteReadTest extends abstractWriteReadTest
{
    /**
     * {@inheritDoc}
     * @param array $configOverride
     * @return target
     * @throws exception
     */
    protected function getWriteReadTargetInstance(array $configOverride = []): target
    {
        return new target\buffered\file\raw('raw_test', [
          'padding_string' => ' ',
          'padding_mode' => 'left',
          'truncate' => true,
          'mapping' => [
            'key1' => ['rowIndex' => 0, 'columnIndex' => 0, 'length' => 10],
            'key2' => ['rowIndex' => 0, 'columnIndex' => 1, 'length' => 10],
            'key3' => ['rowIndex' => 0, 'columnIndex' => 2, 'length' => 10],
            'key4' => ['rowIndex' => 0, 'columnIndex' => 3, 'length' => 10],
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
            $datasource = new raw($filepath, [
              'format' => [
                'map' => [
                  'key1' => ['type' => 'fixed', 'length' => 10],
                  'key2' => ['type' => 'fixed', 'length' => 10],
                  'key3' => ['type' => 'fixed', 'length' => 10],
                  'key4' => ['type' => 'fixed', 'length' => 10],
                ],
                'trim' => true,
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
