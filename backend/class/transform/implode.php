<?php

namespace codename\core\io\transform;

use codename\core\exception;
use codename\core\io\transform;

use function is_array;

/**
 * [implode description]
 */
class implode extends transform
{
    /**
     * glue
     * @var string
     */
    protected string $glue = '';

    /**
     * specific fields
     * @var string[]
     */
    protected array $fields = [];
    /**
     * @var bool
     */
    protected bool $allowConstants;

    /**
     * {@inheritDoc}
     */
    public function __construct(array $config)
    {
        parent::__construct($config);
        $this->glue = $config['glue'] ?? '';
        $this->fields = $config['fields'];
        $this->allowConstants = $config['allowConstants'] ?? false;
    }

    /**
     * {@inheritDoc}
     * @param mixed $value
     * @return mixed
     * @throws exception
     */
    public function internalTransform(mixed $value): mixed
    {
        // IMPORTANT: NOTE the array_flip in the constructor

        // implode all array field (values?) that match the given fields config
        // NOTE: we may need to filter the array_values explicitly after array_intersect_key
        // return implode($this->glue, array_values(array_intersect_key($value, $this->fields)));
        $values = [];
        foreach ($this->fields as $field) {
            if (is_array($field)) {
                $values[] = $this->getValue($field['source'], $field['field'], $value);
            } elseif ($this->allowConstants) {
                //
                // CHANGED/ADDED 2019-07-17
                // supply "allowConstants" : true
                // in config to enable using the bare values as array elements
                // instead of trying to retrieve them from the source ($value)
                //
                $values[] = $field;
            } else {
                // NOTE: fallback to source = source
                $values[] = $value[$field] ?? $this->config['fallbackValue'];
            }
        }
        return implode($this->glue, $values);
    }

    /**
     * {@inheritDoc}
     */
    public function getSpecification(): array
    {
        $sources = [];

        // TODO: add transform sources
        foreach ($this->fields as $field) {
            if (is_array($field)) {
                $sources[] = "{$field['source']}.{$field['field']}";
            } elseif ($this->allowConstants) {
                $sources[] = $field;
            } else {
                // NOTE: fallback to source = source
                $sources[] = "source.$field" ?? "fallbackValue";
            }
        }

        return [
          'type' => 'transform',
          'source' => $sources,
        ];
    }
}
