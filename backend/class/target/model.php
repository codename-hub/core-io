<?php
namespace codename\core\io\target;

use \codename\core\app;
use codename\core\exception;

/**
 * model as a target
 */
class model extends \codename\core\io\target implements \codename\core\io\targetModelInterface {

  /**
   * target model
   * @var \codename\core\model
   */
  protected $model = null;

  /**
   * store method
   * 'save' or 'replace'
   * @var string
   */
  protected $method = 'save';

  /**
   * @param string  $name
   * @param array   $config
   */
  public function __construct(string $name, array $config)
  {
    parent::__construct($name, $config);
    $this->model = app::getModel($config['model'], $config['app'] ?? '', $config['vendor'] ?? '');
    $this->method = $config['method'] ?? 'save';

    $this->uniqueKeys = $this->model->getConfig()->get('unique') ?? null;
  }

  /**
   * [$uniqueKeys description]
   * @var array
   */
  protected $uniqueKeys = null;

  /**
   * @inheritDoc
   */
  public function store(array $data) : bool
  {
    // TODO: validate?
    // TODO: wrap in a try/catch and return true/false depending on error or success
    if($this->method == 'replace') {
      // perform a "manual" replace
      $normalizedData = $this->model->normalizeData($data);

      if($normalizedData[$this->model->getPrimarykey()] ?? false) {
        // update based on supplied pkey vale
        $this->model->save($normalizedData);
      } else {
        if($this->uniqueKeys) {
          // detect existing record
          $filtersAdded = false;
          foreach($this->uniqueKeys as $uniqueKey) {
            if(is_array($uniqueKey)) {
              // multiple keys, combined unique key
              $filters = [];
              foreach($uniqueKey as $key) {
                if($normalizedData[$key] ?? false) {
                  $filters[] = [ 'field' => $key, 'operator' => '=', 'value' => $normalizedData[$key]];
                } else {
                  // irrelevant unique key, one value is null
                  $filters = [];
                  break;
                }
              }
              if(count($filters) > 0) {
                $filtersAdded = true;
                $this->model->addFilterCollection($filters, 'AND');
              }
            } else {
              // single unique key field
              $filtersAdded = true;
              $this->model->addFilter($uniqueKey, $normalizedData[$uniqueKey]);
            }
          }
          if($filtersAdded) {
            $res = $this->model->search()->getResult();
            if(count($res) === 1) {
              // update using found PKEY
              $normalizedData[$this->model->getPrimarykey()] = $res[0][$this->model->getPrimarykey()];
              $this->model->save($normalizedData);
            } else if(count($res) === 0) {
              // insert
              $this->model->save($normalizedData);
            } else {
              // error - multiple results
              throw new exception('EXCEPTION_TARGET_MODEL_MULTIPLE_UNIQUE_KEY_RESULTS', exception::$ERRORLEVEL_ERROR, $res);
            }
          } else {
            //
            // no unique key filters active, needs "ignore_unique"
            //
            if($this->config['ignore_unique'] ?? false) {
              $this->model->save($normalizedData);
            }
          }
        } else {
          // normal save
          $this->model->save($normalizedData);
        }
      }
    } else {
      $this->model->save($this->model->normalizeData($data));
    }
    return true;
  }

  /**
   * [getModel description]
   * @return \codename\core\model [description]
   */
  public function getModel() : \codename\core\model{
    return $this->model;
  }

  /**
   * @inheritDoc
   */
  public function finish()
  {
    return; // end transactions?
  }

}
