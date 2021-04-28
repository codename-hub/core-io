<?php
namespace codename\core\io\datasource;

use codename\core\exception;

/**
 * raw importer
 * via fscanf
 */
class raw extends \codename\core\io\datasource {

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
   * @var array
   */
  protected $format = null;

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
  protected $mappings = [];

  /**
   * pre-computed format config
   * @var array
   */
  public $computedFormat = null;

  /**
   * pre-computed mappings config
   * @var array
   */
  public $computedMappings = null;

  /**
   * [__construct description]
   * @param string  $filepath [path to file]
   * @param array   $config   [configuration of this datasource]
   */
  public function __construct(string $filepath, array $config = array())
  {
    $this->setConfig($config);

    if (($this->handle = @fopen($filepath, "r")) !== false)
    {
      // load success
    }
    else
    {
      error_clear_last();
      throw new exception('FILE_COULD_NOT_BE_OPENED', exception::$ERRORLEVEL_ERROR,array($filepath));
    }
  }

  /**
   * [getSprintfMap description]
   * @param  array  $config [description]
   * @return string         [sprintf/sscanf compatible string]
   */
  protected function getSprintfMap(array $config) {
    if($config['type'] == 'fixed') {
      return '%'.$config['length']."[^".chr(10)."]";
    } else {
      throw new exception('CORE_IO_DATASOURCE_INVALID_MAP_CONFIG', exception::$ERRORLEVEL_ERROR, $config);
    }
  }

  /**
   * @inheritDoc
   */
  public function setConfig(array $config)
  {
    $this->format = $config['format'] ?? [];
    $this->mappings = $config['mappings'] ?? [];

    $computedMap = array_map(function($item) {
      return $this->getSprintfMap($item);
    }, $this->format['map'] ?? []);

    $this->computedFormat = [
      'format'  => isset($this->format['map']) ? implode('', array_values( $computedMap )) : '',
      'map'     => isset($this->format['map']) ? array_keys( $this->format['map'] ) : [],
      'convert' => $this->format['convert'] ?? false,
      'trim'    => $this->format['trim'] ?? false
    ];

    $this->computedMappings = [];
    if(count($this->mappings) > 0) {
      foreach($this->mappings as $conditionKey => $mappingConfig) {
        $this->computedMappings[$conditionKey] = [];
        foreach($mappingConfig as $conditionValue => $map) {
          $computedSubMap = array_map(function($item) {
            return $this->getSprintfMap($item);
          }, $map['map'] ?? []);
          $this->computedMappings[$conditionKey][$conditionValue] = [
            'format' => implode('', array_values($computedSubMap)),
            'map'    => array_keys($map['map']),
            'convert' => $map['convert'] ?? false,
            'trim'    => $map['trim'] ?? false
          ];
        }
      }
    }

    $this->useComputedMappings = (count($this->computedMappings) > 0);
  }

  /**
   * [protected description]
   * @var bool
   */
  protected $useComputedMappings;

  /**
   * @inheritDoc
   */
  public function current()
  {
    return $this->current;
  }

  /**
   * [protected description]
   * @var [type]
   */
  protected $current = null;

  /**
   * [getRawCurrent description]
   * @return [type] [description]
   */
  public function getRawCurrent() {
    return $this->rawCurrent;
  }

  /**
   * @inheritDoc
   */
  public function next()
  {
    $rawvalue = fgets($this->handle);

    $this->rawCurrent = $rawvalue;

    if($rawvalue) {

      $readvalue = $rawvalue;

      // value being worked on
      $value = [];

      $scanvalue = sscanf($readvalue, $this->computedFormat['format']);

      foreach($this->computedFormat['map'] as $mapIndex => $mapField) {
        if($this->computedFormat['convert']) {
          // TODO: move to field mapping
          $value[$mapField] = mb_convert_encoding($scanvalue[$mapIndex], $this->computedFormat['convert']['to'], $this->computedFormat['convert']['from'] ?? mb_internal_encoding());
        } else {
          $value[$mapField] = $scanvalue[$mapIndex];
        }

        if($this->computedFormat['trim']) {
          $value[$mapField] = trim($value[$mapField]);
        }

      }

      if(!$this->useComputedMappings) {

        // current working value is our output value
        $this->current = $value;

      } else {
        // recursive parsing

        $found = false;

        foreach($this->computedMappings as $mapIndex => $mapConfig) {
          $mappedValue = $value[$mapIndex];

          // we found a map config
          if(!empty($mapConfig[$mappedValue])) {

            // convert encoding, if configured
            // if($mapConfig[$mappedValue]['convert']) {
            //   $readvalue = mb_convert_encoding($readvalue, $mapConfig[$mappedValue]['convert']['to'], $mapConfig[$mappedValue]['convert']['from']);
            // }

            $formatted = sscanf($readvalue, $mapConfig[$mappedValue]['format']);

            $value = [];
            foreach($mapConfig[$mappedValue]['map'] as $mapIndex => $mapField) {
              // convert encoding, if configured
              if($mapConfig[$mappedValue]['convert']) {
                $value[$mapField] = mb_convert_encoding($formatted[$mapIndex], $mapConfig[$mappedValue]['convert']['to'], $mapConfig[$mappedValue]['convert']['from'] ?? mb_internal_encoding());
              } else {
                $value[$mapField] = $formatted[$mapIndex];
              }

              if($mapConfig[$mappedValue]['trim']) {
                $value[$mapField] = trim($value[$mapField]);
              }

            }
            $this->current = $value;
            $found = true;
            break;
          } else {
            // value not found
            // mapping invalid. move on to next value?
            // $this->next();
            // $this->current = null;
          }
        }

        if(!$found) {
          $this->current = null;
        }
      }
    } else {
      // end of file?
      $this->current = $rawvalue;
    }

    if ($this->valid())
    {
        $this->index++;
    }
  }

  /**
   * [protected description]
   * @var [type]
   */
  protected $index = 0;

  /**
   * @inheritDoc
   */
  public function key()
  {
    return $this->index;
  }

  /**
   * @inheritDoc
   */
  public function valid()
  {
    return ($this->current !== FALSE);
  }

  /**
   * @inheritDoc
   */
  public function rewind()
  {
    fseek($this->handle, 0);
    $this->index = 0;
    $this->current = false;
    $this->next();
  }

  /**
   * @inheritDoc
   */
  public function currentProgressPosition(): int
  {
    return ftell($this->handle);
  }

  /**
   * @inheritDoc
   */
  public function currentProgressLimit(): int
  {
    return fstat($this->handle)['size'];
  }
}
