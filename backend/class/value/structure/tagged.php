<?php

namespace codename\core\io\value\structure;

use codename\core\value\structure;

class tagged extends structure
{
    /**
     * This validator is used to validate the value on generation.
     * @var string
     */
    protected string $validator = 'structure';

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
