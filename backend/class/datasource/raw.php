<?php

namespace codename\core\io\datasource;

use codename\core\exception;
use codename\core\io\datasource;

/**
 * raw importer
 * via fscanf
 */
class raw extends datasource
{
    /**
     * pre-computed format config
     * @var null|array
     */
    public ?array $computedFormat = null;
    /**
     * pre-computed mappings config
     * @var null|array
     */
    public ?array $computedMappings = null;
    /**
     * input interpretation format as array
     * [
     *  'map'     => [
     *    'output-key-1'  =>  'partial-sprintf-cmd',
     *    'output-key-2'  =>  'partial-sprintf-cmd'
     *  ]
     * ]
     * @see http://php.net/manual/de/function.fscanf.php
     * @see http://php.net/manual/de/function.sprintf.php
     * @var null|array
     */
    protected ?array $format = null;
    /**
     * mappings
     * optional
     * - array index specifies the fscanf output array --index--
     * - subarray: matching condition in key (fscanf output array --index value--)
     *  - subarray item value: new sprintf formatting
     *  [
     *    'inputkey' => [
     *      'someval' => [
     *       'map'     => [
     *          'mapkey1' => 'sprinf-cmd',
     *          'mapkey2' => 'sprinf-cmd'
     *       ]
     *      ],
     *      ...
     *    ]
     *  ]
     * @var array
     */
    protected array $mappings = [];
    /**
     * [protected description]
     * @var bool
     */
    protected bool $useComputedMappings;
    /**
     * [protected description]
     * @var bool|array|null [type]
     */
    protected null|bool|array $current = null;
    /**
     * [protected description]
     * @var int [type]
     */
    protected int $index = 0;
    /**
     * @var resource|false
     */
    protected $handle;
    /**
     * @var string|false
     */
    protected string|false $rawCurrent;

    /**
     * [__construct description]
     * @param string $filepath [path to file]
     * @param array $config [configuration of this datasource]
     * @throws exception
     */
    public function __construct(string $filepath, array $config = [])
    {
        $this->setConfig($config);

        if (($this->handle = @fopen($filepath, "r")) !== false) {
            // load success
        } else {
            error_clear_last();
            throw new exception('FILE_COULD_NOT_BE_OPENED', exception::$ERRORLEVEL_ERROR, [$filepath]);
        }
    }

    /**
     * {@inheritDoc}
     * @param array $config
     * @throws exception
     */
    public function setConfig(array $config): void
    {
        $this->format = $config['format'] ?? [];
        $this->mappings = $config['mappings'] ?? [];

        $computedMap = array_map(function ($item) {
            return $this->getSprintfMap($item);
        }, $this->format['map'] ?? []);

        $this->computedFormat = [
          'format' => isset($this->format['map']) ? implode('', array_values($computedMap)) : '',
          'map' => isset($this->format['map']) ? array_keys($this->format['map']) : [],
          'convert' => $this->format['convert'] ?? false,
          'trim' => $this->format['trim'] ?? false,
        ];

        $this->computedMappings = [];
        if (count($this->mappings) > 0) {
            foreach ($this->mappings as $conditionKey => $mappingConfig) {
                $this->computedMappings[$conditionKey] = [];
                foreach ($mappingConfig as $conditionValue => $map) {
                    $computedSubMap = array_map(function ($item) {
                        return $this->getSprintfMap($item);
                    }, $map['map'] ?? []);
                    $this->computedMappings[$conditionKey][$conditionValue] = [
                      'format' => implode('', array_values($computedSubMap)),
                      'map' => array_keys($map['map']),
                      'convert' => $map['convert'] ?? false,
                      'trim' => $map['trim'] ?? false,
                    ];
                }
            }
        }

        $this->useComputedMappings = (count($this->computedMappings) > 0);
    }

    /**
     * [getSprintfMap description]
     * @param array $config [description]
     * @return string         [sprintf/sscanf compatible string]
     * @throws exception
     */
    protected function getSprintfMap(array $config): string
    {
        if ($config['type'] == 'fixed') {
            return '%' . $config['length'] . "[^" . chr(10) . "]";
        } else {
            throw new exception('CORE_IO_DATASOURCE_INVALID_MAP_CONFIG', exception::$ERRORLEVEL_ERROR, $config);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function current(): mixed
    {
        return $this->current;
    }

    /**
     * [getRawCurrent description]
     * @return mixed
     */
    public function getRawCurrent(): mixed
    {
        return $this->rawCurrent;
    }

    /**
     * {@inheritDoc}
     */
    public function key(): mixed
    {
        return $this->index;
    }

    /**
     * {@inheritDoc}
     */
    public function rewind(): void
    {
        fseek($this->handle, 0);
        $this->index = 0;
        $this->current = false;
        $this->next();
    }

    /**
     * {@inheritDoc}
     */
    public function next(): void
    {
        $rawvalue = fgets($this->handle);

        $this->rawCurrent = $rawvalue;

        if ($rawvalue) {
            $readvalue = $rawvalue;

            // value being worked on
            $value = [];

            $scanvalue = sscanf($readvalue, $this->computedFormat['format']);

            foreach ($this->computedFormat['map'] as $mapIndex => $mapField) {
                if ($this->computedFormat['convert']) {
                    // TODO: move to field mapping
                    $value[$mapField] = mb_convert_encoding($scanvalue[$mapIndex], $this->computedFormat['convert']['to'], $this->computedFormat['convert']['from'] ?? mb_internal_encoding());
                } else {
                    $value[$mapField] = $scanvalue[$mapIndex];
                }

                if ($this->computedFormat['trim']) {
                    $value[$mapField] = trim($value[$mapField]);
                }
            }

            if (!$this->useComputedMappings) {
                // current working value is our output value
                $this->current = $value;
            } else {
                // recursive parsing

                $found = false;

                foreach ($this->computedMappings as $mapIndex => $mapConfig) {
                    $mappedValue = $value[$mapIndex];

                    // we found a map config
                    if (!empty($mapConfig[$mappedValue])) {
                        $formatted = sscanf($readvalue, $mapConfig[$mappedValue]['format']);

                        $value = [];
                        foreach ($mapConfig[$mappedValue]['map'] as $mapIndex => $mapField) {
                            // convert encoding, if configured
                            if ($mapConfig[$mappedValue]['convert']) {
                                $value[$mapField] = mb_convert_encoding($formatted[$mapIndex], $mapConfig[$mappedValue]['convert']['to'], $mapConfig[$mappedValue]['convert']['from'] ?? mb_internal_encoding());
                            } else {
                                $value[$mapField] = $formatted[$mapIndex];
                            }

                            if ($mapConfig[$mappedValue]['trim']) {
                                $value[$mapField] = trim($value[$mapField]);
                            }
                        }
                        $this->current = $value;
                        $found = true;
                        break;
                    } else {
                        // value not found
                        // mapping invalid. move on to next value?
                    }
                }

                if (!$found) {
                    $this->current = null;
                }
            }
        } else {
            // end of file?
            $this->current = $rawvalue;
        }

        if ($this->valid()) {
            $this->index++;
        }
    }

    /**
     * {@inheritDoc}
     */
    public function valid(): bool
    {
        return ($this->current !== false);
    }

    /**
     * {@inheritDoc}
     */
    public function currentProgressPosition(): int
    {
        return ftell($this->handle);
    }

    /**
     * {@inheritDoc}
     */
    public function currentProgressLimit(): int
    {
        return fstat($this->handle)['size'];
    }
}
