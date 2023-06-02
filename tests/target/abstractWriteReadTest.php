<?php

namespace codename\core\io\tests\target;

use codename\core\io\target;
use codename\core\test\base;
use Exception;

abstract class abstractWriteReadTest extends base
{
    /**
     * [testWriteReadTarget description]
     * @param array $configOverride [description]
     * @param array|null $samplesOverride [description]
     */
    public function testWriteReadTarget(array $configOverride = [], ?array $samplesOverride = null): void
    {
        $target = $this->getWriteReadTargetInstance($configOverride);
        $samples = $samplesOverride ?? $this->getSampleData();
        $tags = $this->getSampleTags();
        foreach ($samples as $sample) {
            $target->store($sample, $tags);
        }
        $target->finish();
        $this->compareData($target, $samples);
        $this->cleanupTarget($target);
    }

    /**
     * Creates the instance for the generic write-read test
     * @param array $configOverride [optional configuration override]
     * @return target [description]
     */
    abstract protected function getWriteReadTargetInstance(array $configOverride = []): target;

    /**
     * [getSampleData description]
     * @return array [description]
     */
    protected function getSampleData(): array
    {
        return [
          [
            'key1' => 'value1',
            'key2' => 2,
            'key3' => 3.1415,
            'key4' => null,
          ],
          [
            'key1' => 'value2',
            'key2' => 3,
            'key3' => 4.23446,
            'key4' => null,
          ],
          [
            'key1' => 'value3',
            'key2' => 4,
            'key3' => 5.454545,
            'key4' => null,
          ],
        ];
    }

    /**
     * [getSampleTags description]
     * @return array|null
     */
    protected function getSampleTags(): ?array
    {
        return null;
    }

    /**
     * [compareData description]
     * @param target $target [description]
     * @param array $samples [description]
     */
    protected function compareData(target $target, array $samples): void
    {
        $result = $this->readTargetData($target);
        static::assertEquals($samples, $result);
    }

    /**
     * Read the target's written/collected data
     * and reparse it as an array
     * @param target $target [description]
     * @return array                        [description]
     */
    abstract protected function readTargetData(target $target): array;

    /**
     * [cleanupTarget description]
     * @param target $target [description]
     * @return void
     */
    abstract protected function cleanupTarget(target $target): void;

    /**
     * [testCallingStoreAfterFinishCrashes description]
     */
    public function testCallingStoreAfterFinishCrashes(): void
    {
        $target = $this->getWriteReadTargetInstance();
        $samples = $this->getSampleData();
        $tags = $this->getSampleTags();
        foreach ($samples as $sample) {
            $target->store($sample, $tags);
        }
        $target->finish();

        // In this case, we do not compare,
        // but try to call store() again
        // which MUST result in an exception
        try {
            $target->store([]);
            static::fail('Exception EXCEPTION_CORE_IO_TARGET_BUFFERED_ALREADY_FINISHED did not happen!');
        } catch (Exception $e) {
            static::assertEquals('EXCEPTION_CORE_IO_TARGET_BUFFERED_ALREADY_FINISHED', $e->getMessage());
        }

        $this->cleanupTarget($target);
    }
}
