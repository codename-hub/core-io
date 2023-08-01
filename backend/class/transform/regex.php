<?php

namespace codename\core\io\transform;

use codename\core\exception;
use codename\core\io\transform;
use LogicException;

use function in_array;
use function is_string;
use function preg_match;
use function preg_replace;

/**
 * [regex description]
 */
class regex extends transform
{
    /**
     * [protected description]
     * @var string|array
     */
    protected string|array $regexValue;
    /**
     * [protected description]
     * @var null|string|array
     */
    protected null|string|array $replaceValue;
    /**
     * [protected description]
     * @var string
     */
    protected string $mode;

    /**
     * {@inheritDoc}
     * @param array $config
     * @throws exception
     */
    public function __construct(array $config)
    {
        parent::__construct($config);
        $this->regexValue = $config['regex_value'];
        $this->replaceValue = $config['replace_value'] ?? null;
        $this->mode = $config['mode'];

        if (!in_array($this->mode, [
          'match_success',
          'match',
          'replace',
        ])) {
            throw new exception('TRANSFORM_REGEX_INVALID_CONFIG', exception::$ERRORLEVEL_ERROR, $config);
        }
    }

    /**
     * {@inheritDoc}
     * @param mixed $value
     * @return mixed
     * @throws exception
     */
    public function internalTransform(mixed $value): mixed
    {
        $v = $this->getValue($this->config['source'], $this->config['field'], $value);

        if ($this->mode === 'match' || $this->mode === 'match_success') {

            if (!is_string($this->regexValue)) {
                throw new exception('TRANSFORM_REGEX_REGEX_VALUE_MUST_BE_STRING', exception::$ERRORLEVEL_ERROR);
            }

            $matches = [];
            $res = preg_match($this->regexValue, $v, $matches);

            if ($res === 1) {
                // match!
                if ($this->mode === 'match_success') {
                    return true;
                } else {
                    return $matches;
                }
            } elseif ($res === 0) {
                // no match
                if ($this->mode === 'match_success') {
                    return false;
                } else {
                    return null;
                }
            } else {
                // error
                $this->errorstack->addError('REGEX_ERROR', 0, [
                  'config' => $this->config,
                  'value' => $value,
                ]);
                return null;
            }
        } elseif ($this->mode === 'replace') {

            if ($this->replaceValue === null) {
                throw new exception('TRANSFORM_REGEX_REPLACE_VALUE_MUST_BE_STRING', exception::$ERRORLEVEL_ERROR);
            }

            return preg_replace($this->regexValue, $this->replaceValue, $v);
        }
        throw new LogicException('Not implemented and shouldn\'t be');
    }

    /**
     * {@inheritDoc}
     */
    public function getSpecification(): array
    {
        return [
          'type' => 'transform',
          'source' => ["{$this->config['source']}.{$this->config['field']}"],
        ];
    }
}
