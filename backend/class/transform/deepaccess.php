<?php

namespace codename\core\io\transform;

use codename\core\exception;
use codename\core\io\transform;
use LogicException;

/**
 * getter for deep-accessing array elements
 */
class deepaccess extends transform
{
    /**
     * accessor/structure dive
     * [ key, subkey, subsubkey, finalkey ]
     * @var null|array
     */
    protected ?array $path = null;

    /**
     * {@inheritDoc}
     */
    public function __construct(array $config)
    {
        parent::__construct($config);

        $config['path'] = $config['path'] ?? '';

        if (is_array($config['path'])) {
            $this->path = $config['path'];
        } else {
            $this->path = explode('.', $config['path']);
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
        // Fallback to 'source' if none provided
        if (($this->config['source'] ?? 'source') == 'source' && !isset($this->config['field'])) {
            $v = $value;
        } else {
            $v = $this->getValue($this->config['source'] ?? 'source', $this->config['field'], $value);
        }

        $dive = \codename\core\helper\deepaccess::get($v, $this->path);

        if ($dive === null && ($this->config['required'] ?? false)) {
            $this->errorstack->addError('VALUE_NULL', 0, [
              'config' => $this->config,
              'value' => $value,
            ]);
        }

        return $dive;
    }

    /**
     * {@inheritDoc}
     */
    public function getSpecification(): array
    {
        throw new LogicException('Not implemented'); // TODO
    }
}
