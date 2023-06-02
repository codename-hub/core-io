<?php

namespace codename\core\io\tests\target;

use codename\core\io\target\arraydata;
use PHPUnit\Framework\TestCase;

/**
 * Tests target & source filters
 */
class filterTest extends TestCase
{
    /**
     * [testSourceFilters description]
     */
    public function testSourceFilters(): void
    {
        $data = [
          [
            'expectMatch' => true,
            'key1' => 123,
            'key2' => 'willpass',
            'key3' => null,
            'key4' => 'willpass',
          ],
          [
            'expectMatch' => false,
            'key1' => 123,
            'key2' => 'abc',
            'key3' => 'wontpass',
            'key4' => null,
          ],
          [
            'expectMatch' => false,
            'key1' => 234,
            'key2' => 'abc',
            'key3' => 'wontpass',
            'key4' => null,
          ],
        ];

        $filters = [
          [
            'field' => 'key1',
            'operator' => '=',
            'value' => 123,
          ],
          [
            'field' => 'key2',
            'operator' => '!=',
            'value' => 'abc',
          ],
          [
            'field' => 'key3',
            'operator' => '=',
            'value' => null,
          ],
          [
            'field' => 'key4',
            'operator' => '!=',
            'value' => null,
          ],
          [
              // should be ignored
            'field' => 'key1',
            'operator' => 'bla',
            'value' => null,
          ],
        ];

        //
        // For this example, we directly test only filters
        // and assume source data == target data
        //
        $target = new arraydata('source_filter_test', [
          'source_filter' => $filters,
          'target_filter' => $filters,
        ]);

        foreach ($data as $dataset) {
            static::assertEquals($dataset['expectMatch'], $target->matchesSourceFilters($dataset));
            static::assertEquals($dataset['expectMatch'], $target->matchesTargetFilters($dataset));
        }
    }
}
