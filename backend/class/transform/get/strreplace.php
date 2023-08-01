<?php

namespace codename\core\io\transform\get;

use codename\core\exception;
use codename\core\io\transform\get;

/**
 * [substr description]
 */
class strreplace extends get
{
    /**
     * source
     * @var string
     */
    protected string $source;

    /**
     * field from source
     * @var array|string
     */
    protected array|string $field;


    /**
     * whether to work in case-insensitive mode
     * @var bool
     */
    protected bool $caseInsensitive = false;
    /**
     * static value to search for - single string or array of strings
     * @var string|array|null
     */
    protected string|array|null $searchStatic = null;
    /**
     * dynamic value to search for (other source/transform)
     * @var array|null
     */
    protected array|null $searchDynamic = null;
    /**
     * static value to replace with - single string or array of strings
     * @var string|array|null
     */
    protected string|array|null $replaceStatic = null;
    /**
     * dynamic value to replace with (other source/transform)
     * @var array|null
     */
    protected array|null $replaceDynamic = null;

    /**
     * {@inheritDoc}
     */
    public function __construct(array $config)
    {
        parent::__construct($config);

        $this->caseInsensitive = $config['case_insensitive'] ?? $this->caseInsensitive;
        $this->source = $config['source'];
        $this->field = $config['field'];

        $config['search'] = $config['search'] ?? '';
        $config['replace'] = $config['replace'] ?? '';

        if (is_array($search = $config['search'])) {
            if (($search['source'] ?? false) && ($search['field'] ?? false)) {
                $this->searchDynamic = $search;
            } else {
                $this->searchStatic = $search; // array-based
            }
        } else {
            $this->searchStatic = $config['search'];
        }

        if (is_array($replace = $config['replace'])) {
            if (($replace['source'] ?? false) && ($replace['field'] ?? false)) {
                $this->replaceDynamic = $replace;
            } else {
                $this->replaceStatic = $replace; // array-based
            }
        } else {
            $this->replaceStatic = $config['replace'];
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
        $subject = $this->getValue($this->config['source'], $this->config['field'], $value);

        $search = $this->searchStatic;
        if ($this->searchDynamic) {
            $search = $this->getValue($this->searchDynamic['source'], $this->searchDynamic['field'], $value);
        }

        $replace = $this->replaceStatic;
        if ($this->replaceDynamic) {
            $replace = $this->getValue($this->replaceDynamic['source'], $this->replaceDynamic['field'], $value);
        }

        if ($this->caseInsensitive) {
            return str_ireplace($search, $replace, $subject);
        } else {
            return str_replace($search, $replace, $subject);
        }
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
