<?php

namespace codename\core\io\transform\get;

use codename\core\exception;
use codename\core\io\transform\get;

/**
 * [strcase description]
 */
class strcase extends get
{
    /**
     * source
     * @var string
     */
    protected string $source;

    /**
     * field from source
     * @var string|array
     */
    protected string|array $field;

    /**
     * whether to work in case-insensitive mode
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

        $this->mode = $config['mode'];

        if (!in_array($this->mode, ['upper', 'lower'])) {
            throw new exception('INVALID_STRCASE_MODE', exception::$ERRORLEVEL_ERROR, $this->mode);
        }

        $this->source = $config['source'];
        $this->field = $config['field'];
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

        if ($v === null) {
            return null;
        }

        if ($this->mode === 'upper') {
            return strtoupper($v);
        } elseif ($this->mode === 'lower') {
            return strtolower($v);
        }
        return null;
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
