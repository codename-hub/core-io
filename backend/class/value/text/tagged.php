<?php

namespace codename\core\io\value\text;

use codename\core\value\text;

class tagged extends text
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
