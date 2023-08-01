<?php

namespace codename\core\io\value\text\fileabsolute;

use codename\core\value\text\fileabsolute;

class tagged extends fileabsolute
{
    /**
     * [protected description]
     * @var array|null
     */
    protected ?array $tags = null;

    /**
     * {@inheritDoc}
     */
    public function __construct($value, ?array $tags = null)
    {
        parent::__construct($value);
        $this->tags = $tags;
    }

    /**
     * [getTags description]
     * @return array|null
     */
    public function getTags(): ?array
    {
        return $this->tags;
    }
}
