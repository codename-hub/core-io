<?php

namespace codename\core\io\tests\target\buffered;

use codename\core\exception;
use codename\core\io\target\buffered\file;
use codename\core\test\base;
use ReflectionException;

class fileTest extends base
{
    /**
     * [testFileHandle description]
     * @throws exception
     */
    public function testFileHandle(): void
    {
        $target = new dummyFile('file_test', []);

        $result = $target->getFileHandle();
        static::assertIsResource($result);
    }

    /**
     * [testNewFileHandle description]
     */
    public function testWrongNewFileHandle(): void
    {
        $this->expectException(exception::class);
        $this->expectExceptionMessage('CORE_IO_TARGET_BUFFERED_FILE_HANDLE_ERROR');

        $target = new dummyFile('file_test', [
          'append' => true,
        ]);

        $target->getNewFileHandle(__DIR__ . "/");
    }

    /**
     * [testNewFileHandle description]
     * @throws ReflectionException
     * @throws exception
     */
    public function testEmptyFileResultArray(): void
    {
        $target = new dummyFile('file_test', []);

        $result = $target->getFileResultArray();
        static::assertEmpty($result);
    }
}

class dummyFile extends file
{
    /**
     * perform the real store routine
     * -> store the buffered data
     *
     * @return void
     */
    public function storeBufferedData(): void
    {
    }

    /**
     * legacy method returning a new file handle using the currently set targetFilePath
     * @return resource
     * @throws exception
     */
    public function getFileHandle()
    {
        return parent::getFileHandle();
    }

    /**
     * returns a new file handle for the given filepath
     * to be opened in write or append mode
     * @param string $targetFilePath [description]
     * @return resource               [a file handle resource]
     * @throws exception
     */
    public function getNewFileHandle(string $targetFilePath)
    {
        return parent::getNewFileHandle($targetFilePath);
    }
}
