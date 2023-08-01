<?php

namespace codename\core\io\tests\target\buffered\file;

use codename\core\exception;
use codename\core\io\datasource\spreadsheet;
use codename\core\io\target;
use codename\core\io\tests\target\abstractWriteReadTest;
use ReflectionException;

class spreadsheetCsvWriteReadTest extends abstractWriteReadTest
{
    /**
     * {@inheritDoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        // PHPSpreadsheet needs fileinfo extension
        // which provides mime_content_type
        // make sure it's available - early block otherwise.
        static::assertTrue(extension_loaded('fileinfo'), 'PhpSpreadsheet needs fileinfo extension - test cannot proceed.');
        static::assertTrue(function_exists('mime_content_type'), 'PhpSpreadsheet needs mime_content_type() from fileinfo extension - test cannot proceed.');
    }

    /**
     * {@inheritDoc}
     * @param array $configOverride
     * @return target
     * @throws ReflectionException
     * @throws exception
     */
    protected function getWriteReadTargetInstance(array $configOverride = []): target
    {
        return new target\buffered\file\spreadsheet('spreadsheet_csv_test', [
          'use_writer' => 'Csv',
          'key_row' => 1,
          'config' => [
            'encoding_utf8bom' => true,
          ],
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
     * @throws \PhpOffice\PhpSpreadsheet\Exception
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
