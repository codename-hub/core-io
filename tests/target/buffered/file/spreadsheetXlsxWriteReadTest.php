<?php

namespace codename\core\io\tests\target\buffered\file;

use codename\core\io\datasource\spreadsheet;
use codename\core\io\target;
use codename\core\io\tests\target\abstractWriteReadTest;
use PhpOffice\PhpSpreadsheet\Exception;
use ReflectionException;

class spreadsheetXlsxWriteReadTest extends abstractWriteReadTest
{
    /**
     * {@inheritDoc}
     * @param array $configOverride
     * @return target
     * @throws ReflectionException
     * @throws \codename\core\exception
     */
    protected function getWriteReadTargetInstance(array $configOverride = []): target
    {
        return new target\buffered\file\spreadsheet('xlsx_test', [
          'use_writer' => 'Xlsx',
          'key_row' => 1,
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
     * @throws Exception
     * @throws \PhpOffice\PhpSpreadsheet\Reader\Exception
     */
    protected function readTargetData(target $target): array
    {
        $files = $target->getFileResultArray();
        static::assertCount(1, $files);
        $res = [];
        foreach ($files as $file) {
            $filepath = $file->get();
            $datasource = new spreadsheet($filepath, [
                // default config
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
