<?php

namespace codename\core\io\validator\structure\config\import;

use codename\core\validator\structure\config;

/**
 * Validating import definitions
 */
class target extends config
{
    /**
     * Contains a list of array keys that MUST exist in the validated array
     * @var array
     */
    public $arrKeys = [];

    /**
     * {@inheritDoc}
     */
    public function validate(mixed $value): array
    {
        if (count(parent::validate($value)) != 0) {
            return $this->errorstack->getErrors();
        }


        return $this->getErrors();
    }
}
