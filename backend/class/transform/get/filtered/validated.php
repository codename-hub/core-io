<?php

namespace codename\core\io\transform\get\filtered;

use codename\core\app;
use codename\core\exception;
use codename\core\io\transform\get\filtered;
use codename\core\validator;
use ReflectionException;

class validated extends filtered
{
    /**
     * array of validators to use
     * @var validator[]
     */
    protected array $validators;

    /**
     * {@inheritDoc}
     * @param array $config
     * @throws ReflectionException
     * @throws exception
     */
    public function __construct(array $config)
    {
        parent::__construct($config);

        if ($validator = $config['validator'] ?? false) {
            if (is_array($validator)) {
                $this->validators = [];
                foreach ($validator as $validatorName) {
                    $this->validators[] = app::getValidator($validatorName);
                }
            } else {
                $this->validators = [
                  app::getValidator($validator),
                ];
            }
        } else {
            throw new exception('NO_VALIDATOR_SPECIFIED', exception::$ERRORLEVEL_ERROR);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function internalTransform(mixed $value): mixed
    {
        // reset validators before execution
        foreach ($this->validators as $validatorInstance) {
            $validatorInstance->reset();
        }

        // get value
        $v = $this->getValue($this->config['source'], $this->config['field'], $value);

        $overallErrorCount = 0;

        // iterate over every validator
        foreach ($this->validators as $validatorInstance) {
            // validate!
            $validatorInstance->validate($v);

            if (($errorCount = count($errors = $validatorInstance->getErrors())) > 0) {
                $overallErrorCount += $errorCount;
                $this->errorstack->addErrors($errors);
            }
        }

        if ($overallErrorCount === 0) {
            return $v;
        } else {
            // NOTE: we already added errors before...
            return null;
        }
    }
}
