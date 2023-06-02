<?php

namespace codename\core\io\tests\target\buffered\file;

use codename\core\exception;
use codename\core\io\target;
use codename\core\io\target\buffered\file\parquet;
use codename\core\io\tests\target\abstractWriteReadTest;
use codename\parquet\exception\ArgumentNullException;
use codename\parquet\exception\NotSupportedException;
use codename\parquet\ParquetException;

class parquetBufferedWriteReadTest extends abstractWriteReadTest
{
    /**
     * [testCompressionNone description]
     */
    public function testCompressionNone(): void
    {
        $this->testWriteReadTarget([
          'compression' => 'none',
        ]);
    }

    /**
     * [testAutoGuessTypes description]
     */
    public function testAutoGuessTypes(): void
    {
        $this->testWriteReadTarget([
          'mapping' => [
            'key1' => [],
            'key2' => [],
            'key3' => [],
            'key4' => ['php_type' => 'string',], // having only null items can't be type-guessed
          ],
        ]);
    }

    /**
     * [testCompressionGzip description]
     */
    public function testCompressionGzip(): void
    {
        $this->testWriteReadTarget([
          'compression' => 'gzip',
        ]);
    }

    /**
     * Special test with an overridden target class
     * to allow empty data pages/RGs to be written in-between
     * @throws ArgumentNullException
     * @throws NotSupportedException
     * @throws ParquetException
     * @throws exception
     */
    public function testEmptyDataPagesInbetween(): void
    {
        $target = new overriddenParquetTarget('parquet_empty_data_pages_test', [
          'buffer' => true,
          'buffer_size' => 2,
          'mapping' => [
            'key1' => ['php_type' => 'string'],
            'key2' => ['php_type' => 'integer'],
            'key3' => ['php_type' => 'double'],
            'key4' => ['php_type' => 'string', 'is_nullable' => true],
          ],
        ]);
        $samples = $this->getSampleData();
        $tags = $this->getSampleTags();
        foreach ($samples as $sample) {
            $target->publicWriteData([]);
            $target->store($sample, $tags);
            $target->publicWriteData([]);
        }
        $target->finish();
        $this->compareData($target, $samples);
        $this->cleanupTarget($target);
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

    /**
     * {@inheritDoc}
     * @param array $configOverride
     * @return target
     * @throws \Exception
     */
    protected function getWriteReadTargetInstance(array $configOverride = []): target
    {
        return new parquet(
            'parquet_test',
            array_replace([
              'buffer' => true,
              'buffer_size' => 2,
              'mapping' => [
                'key1' => ['php_type' => 'string'],
                'key2' => ['php_type' => 'integer'],
                'key3' => ['php_type' => 'double'],
                'key4' => ['php_type' => 'string', 'is_nullable' => true],
              ],
            ], $configOverride)
        );
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
            $datasource = new \codename\core\io\datasource\parquet($filepath);

            foreach ($datasource as $r) {
                $res[] = $r;
            }
        }
        return $res;
    }
}

class overriddenParquetTarget extends parquet
{
    /**
     * public access to ::writeData for simulating special kinds
     * of parquet files (e.g. writing empty RGs/data pages)
     * @param array $data [description]
     * @return void
     * @throws ParquetException
     * @throws ArgumentNullException
     * @throws NotSupportedException
     * @throws exception
     */
    public function publicWriteData(array $data): void
    {
        $this->writeData($data);
    }
}
