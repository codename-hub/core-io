<?php namespace codename\core\io;

use codename\core\exception;

/**
 * defines a target
 */
abstract class target {

  /**
   * target name
   * @var string
   */
  public $name = null;

  /**
   * filters that are executed on the source data
   * @var array[]
   */
  protected $sourceFilters = [];

  /**
   * filters that are executed on the target data
   * @var array[]
   */
  protected $targetFilters = [];

  /**
   * [__construct description]
   * @param string  $name   [description]
   * @param array   $config [description]
   */
  public function __construct(string $name, array $config)
  {
    $this->name = $name;
    $this->config = $config;

    if(isset($this->config['source_filter'])) {
      $this->sourceFilters = $this->config['source_filter'];
      $this->sourceFilterFunctions = self::buildFilterFunctions($this->sourceFilters);
    }

    if(isset($this->config['target_filter'])) {
      $this->targetFilters = $this->config['target_filter'];
      $this->targetFilterFunctions = self::buildFilterFunctions($this->targetFilters);
    }
  }

  /**
   * builds an array of executable filter functions
   *
   * @param  array  $filters [description]
   * @return callable[]
   */
  protected static function buildFilterFunctions(array $filters) : array {
    $filterFunctions = [];
    foreach($filters as $filter) {
      switch ($filter['operator']) {
        case '=':
          if($filter['value'] === null) {
            //
            // We want the value to be null,
            // so we return !isset(value) - positive, if not set or null.
            //
            $filterFunctions[] = function(array $data) use ($filter) {
              return (!isset($data[$filter['field']]));
            };
          } else {
            //
            // We want the value to be not null and specify an explicit value.
            // So we check for the value being set (via isset() and perform a simple == comparison)
            //
            $filterFunctions[] = function(array $data) use ($filter) {
              return (isset($data[$filter['field']]) && ($data[$filter['field']] == $filter['value']));
            };
          }
          // LEGACY/Old code:
          // $filterFunctions[] = function(array $data) use ($filter) {
          //   return (!isset($data[$filter['field']]) && $filter['value'] == null) || (isset($data[$filter['field']]) && ($data[$filter['field']] == $filter['value']));
          // };
          break;
        case '!=':
          if($filter['value'] === null) {
            //
            // We want the value to be != null,
            // so we check for isset - and this already fulfills our requirements (and returns true)
            //
            $filterFunctions[] = function(array $data) use ($filter) {
              return (isset($data[$filter['field']]));
            };
          } else {
            //
            // We want the value to be not equal to a specific value.
            // This is the more complicated on.
            // By definition, 1!=2, at least for integers. We also perform this for strings.
            // But it depends on the personal point of view, if we want the value != 123
            // and we provide NULL. For some RDBMS, this might not be defined.
            // But we interpret it as a "falsy" value.
            // The !=NULL case is handled above.
            //
            // So, we want the value to be SOME value (except NULL)
            // - isset() kicks out the NULLs
            // - and we simply compare via != later on.
            //
            // filter.value != VALUE
            $filterFunctions[] = function(array $data) use ($filter) {
              return (isset($data[$filter['field']]) && $data[$filter['field']] != $filter['value']);
            };
          }
          // LEGACY/Old code:
          // $filterFunctions[] = function(array $data) use ($filter) {
          //   // unset == null => !false, otherwise detail check
          //   return (!(!isset($data[$filter['field']]) && $filter['value'] == null)) || (isset($data[$filter['field']]) && $data[$filter['field']] != $filter['value']);
          // };
          break;
        default:
          # code...
          break;
      }
    }
    return $filterFunctions;
  }

  /**
   * callable source filter functions
   * @var callable[]
   */
  protected $sourceFilterFunctions = [];

  /**
   * callable target filter functions
   * @var callable[]
   */
  protected $targetFilterFunctions = [];

  /**
   * [protected description]
   * @var array
   */
  protected $config = [];

  /**
   * [store description]
   * @param  array      $data [description]
   * @return bool         [success]
   */
  public abstract function store(array $data) : bool;

  // public static $filterCalledCount = 0.0;
  // public static $filterCalledDuration = 0.0;


  /**
   * returns true, if the source filters matches the current dataset
   * (before transforming stuff)
   * @param  array $data [description]
   * @return bool        [description]
   */
  public function matchesSourceFilters(array $data) : bool {
    $matches = true;
    foreach($this->sourceFilterFunctions as $filter) {
      $matches = $filter($data);
      if(!$matches) {
        return $matches;
      }
    }
    return $matches;
  }

  /**
   * returns true, if the target filters matches the current dataset
   * (after applying transforms and stuff)
   * @param  array $data [description]
   * @return bool        [description]
   */
  public function matchesTargetFilters(array $data) : bool {
    $matches = true;
    foreach($this->targetFilterFunctions as $filter) {
      $matches = $filter($data);
      if(!$matches) {
        return $matches;
      }
    }
    return $matches;
  }

  /**
   * [matches description]
   * @param  array $data [description]
   * @return bool        [description]
   */
  public function matches(array $data) : bool {

    throw new exception('OBSOLETE');

    // handle filters as conditions
    $matches = true;

    // $start = microtime(true);
    // foreach($this->filters as $filter) {
    //
    //   if($filter['operator'] == '=') {
    //     $matches = ($data[$filter['field']] ?? null) == $filter['value'];
    //   } else if($filter['operator'] == '!=') {
    //     $matches = ($data[$filter['field']] ?? null) != $filter['value'];
    //   } else {
    //     die("ERROR");
    //   }
    //
    //   if(!$matches) {
    //     break;
    //   }
    // }
    // $end = microtime(true);
    // self::$filterCalledCount += count($this->filters);
    // self::$filterCalledDuration += ($end - $start);

    foreach($this->filterFunctions as $filter) {

      // $start = microtime(true);
      $matches = $filter($data);
      // $end = microtime(true);

      // self::$filterCalledCount++;
      // self::$filterCalledDuration += ($end-$start);

      if(!$matches) {
        return $matches;
      }
    }

    return $matches;
  }

  /**
   * [getMapping description]
   * @return array [description]
   */
  public function getMapping() : array {
    return $this->config['mapping'];
  }

  /**
   * close/finish with this target
   *
   * @return void
   */
  public abstract function finish();

}
