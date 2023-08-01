<?php

namespace codename\core\io\transform\get;

use codename\core\exception;
use codename\core\io\transform\get;
use DateInterval;
use DateTime;

use function easter_days;

/**
 * convert a string (date) to another date format
 */
class easterdatetime extends get
{
    /**
     * {@inheritDoc}
     * @param mixed $value
     * @return mixed
     * @throws exception
     */
    public function internalTransform(mixed $value): mixed
    {
        $v = $this->getValue($this->config['source'], $this->config['field'], $value);
        $datetime = new DateTime($v);
        $days = easter_days($datetime->format('Y'));
        $datetime->setDate($datetime->format('Y'), 3, 21);
        $datetime->add(new DateInterval("P{$days}D"));
        return $datetime->format($this->config['format'] ?? 'Y-m-d');
    }

    /**
     * {@inheritDoc}
     */
    public function getSpecification(): array
    {
        return [
          'type' => 'transform',
          'source' => [],
        ];
    }
}
