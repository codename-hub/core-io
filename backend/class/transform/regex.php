<?php

namespace codename\core\io\transform;

use codename\core\exception;
use codename\core\io\transform;
use LogicException;

/**
 * [regex description]
 */
class regex extends transform
{
    /**
     * [protected description]
     * @var string
     */
    protected string $regexValue;
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
            // TODO
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
