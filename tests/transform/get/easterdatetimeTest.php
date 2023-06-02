<?php

namespace codename\core\io\tests\transform\get;

use codename\core\exception;
use codename\core\io\tests\transform\abstractTransformTest;
use DateInterval;
use DateTime;
use ReflectionException;

use function easter_days;

class easterdatetimeTest extends abstractTransformTest
{
    /**
     * Testing transforms for Errors
     * @throws ReflectionException
     * @throws exception
     */
    public function testValueValid(): void
    {
        $transform = $this->getTransform('get_easterdatetime', [
          'source' => 'source',
          'field' => 'example_source_field',
          'format' => 'Y-m-d',
        ]);
        $result = $transform->transform([
          'example_source_field' => '2021-04-19',
        ]);

        // calculate easter
        $days = easter_days('2021');
        $easterDate = (new DateTime('2021-04-19'))
          ->setDate('2021', 3, 21)
          ->add(new DateInterval("P{$days}D"))
          ->format('Y-m-d');

        // Make sure it stays an array
        static::assertEquals($easterDate, $result);
    }

    /**
     * Test Spec output (simple case)
     * @throws ReflectionException
     * @throws exception
     */
    public function testSpecification(): void
    {
        $transform = $this->getTransform('get_easterdatetime', [
          'source' => 'source',
          'field' => 'example_source_field',
          'format' => 'Y-m-d',
        ]);
        static::assertEquals(
            [
              'type' => 'transform',
              'source' => [],
            ],
            $transform->getSpecification()
        );
    }

    /**
     * {@inheritDoc}
     */
    protected function setUp(): void
    {
        parent::setUp();
        if (!extension_loaded('calendar')) {
            static::fail('Calendar extension needed for testing get_easterdatetime transform');
        }
    }
}
