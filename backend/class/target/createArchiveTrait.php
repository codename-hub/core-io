<?php

namespace codename\core\io\target;

use codename\core\exception;
use codename\core\io\value\text\fileabsolute\tagged;
use LogicException;
use ReflectionException;
use ZipArchive;

/**
 * trait for using ZIP archive creation in a file-based target
 */
trait createArchiveTrait
{
    /**
     * [protected description]
     * NOTE: until created, this HAS to be null.
     * @var null|array
     */
    protected ?array $archiveResults = null;

    /**
     * [createArchive description]
     * @return array
     * @throws ReflectionException
     * @throws exception
     */
    protected function createArchive(): array
    {
        $tempZipInstances = [];
        $tempZipTags = [];

        foreach ($this->fileResults as $fileResult) {
            $localName = null;
            $archiveTarget = 'default';

            $tags = null;

            $encryption = null;
            $encryptionType = null;
            $encryptionPassphrase = null;

            if ($fileResult instanceof tagged) {
                $tags = $fileResult->getTags();
                $archiveTarget = $fileResult->getTags()[0]['archive_name'] ?? null;

                $encryption = $fileResult->getTags()[0]['archive_encryption'] ?? false;
                $encryptionType = $fileResult->getTags()[0]['archive_encryption_type'] ?? null;
                $encryptionPassphrase = $fileResult->getTags()[0]['archive_encryption_passphrase'] ?? null;

                if (($fileName = $fileResult->getTags()[0]['file_name'] ?? null) && ($fileExtension = $fileResult->getTags()[0]['file_extension'] ?? null)) {
                    $localName = $fileName . '.' . $fileExtension;
                } else {
                    // error?
                }
            } else {
                // should we warn?
            }

            if (!($tempZipInstances[$archiveTarget] ?? false)) {
                $tempFilename = tempnam(sys_get_temp_dir(), 'zip_');
                $instance = new ZipArchive();
                $tempZipInstances[$archiveTarget] = $instance;
                $tempZipInstances[$archiveTarget]->open($tempFilename, ZipArchive::CREATE | ZipArchive::OVERWRITE);
            }

            $tempZipInstances[$archiveTarget]->addFile($fileResult->get(), $localName); // TODO: test, if PHP gets it ($localName == null fallback!)

            //
            // NOTE:
            // this requires
            // - PHP 7.2+
            // - pecl zip 1.14.0+
            //
            if ($encryption && $encryptionPassphrase) {
                // NOTE: localName may have been set (inside archive), so we have to use this, if not null

                $encTypeConstants = [
                    // 'EM_NONE'    => \ZipArchive::EM_NONE, // this disables passwords
                  'EM_AES_128' => ZipArchive::EM_AES_128,
                  'EM_AES_192' => ZipArchive::EM_AES_192,
                  'EM_AES_256' => ZipArchive::EM_AES_256, // the safe way.
                ];

                $encryptionConst = $encTypeConstants[$encryptionType] ?? ZipArchive::EM_AES_256;
                $tempZipInstances[$archiveTarget]->setEncryptionName($localName ?? $fileResult->get(), $encryptionConst, $encryptionPassphrase);
            }

            if ($tags) {
                $tempZipTags[$archiveTarget] = array_merge($tempZipTags[$archiveTarget] ?? [], $tags);
            }
        }

        $zipFileResults = [];

        foreach ($tempZipInstances as $archiveTarget => $instance) {
            $tags = $tempZipTags[$archiveTarget] ?? null;
            $filename = $instance->filename;
            if (!$instance->close()) {
                throw new exception('ZIP ERROR', exception::$ERRORLEVEL_ERROR);
            }
            if ($tags) {
                //
                // Just override the first element so transmission module gets it.
                //
                foreach ($tags as &$tagSet) {
                    $tagSet['file_name'] = $archiveTarget;
                    $tagSet['file_extension'] = 'zip';
                }
                $zipFileResult = new tagged($filename, $tags);
                $zipFileResults[] = $zipFileResult;
            } else {
                // error?
                throw new LogicException('Tagless Archives not implemented');
            }
        }

        return $zipFileResults;
    }
}
