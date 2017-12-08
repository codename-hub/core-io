<?php
namespace codename\core\io\target\model;

use \codename\core\app;
use codename\core\exception;

/**
 * complex model as a target
 */
class complex extends \codename\core\io\target
  implements \codename\core\io\targetModelInterface,
  \codename\core\io\target\virtualTargetInterface {

  /**
   * target model
   * @var \codename\core\model
   */
  protected $model = null;

  /**
   * @inheritDoc
   */
  public function __construct(string $name, array $config)
  {
    parent::__construct($name, $config);
    $this->model = $this->buildModelStructure($config['structure']);
    $this->method = $config['method'] ?? 'save';
    $this->uniqueKeys = $this->model->getConfig()->get('unique') ?? null;
  }

  /**
   * store method
   * 'save' or 'replace'
   * @var string
   */
  protected $method = 'save';

  /**
   * [$uniqueKeys description]
   * @var array
   */
  protected $uniqueKeys = null;

  /**
   * [protected description]
   * @var array
   */
  protected $virtualStore = [];

  /**
   * @inheritDoc
   */
  public function getVirtualStoreData(): array
  {
    return $this->virtualStore;
  }

  /**
   * whether to store data or not
   * @var bool
   */
  protected $virtualStoreEnabled = false;

  /**
   * @inheritDoc
   */
  public function setVirtualStoreEnabled(bool $state)
  {
    $this->virtualStoreEnabled = $state;
  }

  /**
   * @inheritDoc
   */
  public function getVirtualStoreEnabled(): bool
  {
    return $this->virtualStoreEnabled;
  }

  /**
   * [buildModelStructure description]
   * @param  array                $config [description]
   * @return \codename\core\model         [description]
   */
  protected function buildModelStructure(array $config) : \codename\core\model {
    $model = app::getModel($config['model'], $config['app'] ?? '', $config['vendor'] ?? '');
    foreach($config['join'] as $join) {
      $joinModel = $this->buildModelStructure($join);
      if(($join['type'] ?? false) === 'collection') {
        $model->addCollectionModel($joinModel, $join['modelfield'] ?? null);
      } else {
        $model->addModel($joinModel);
      }
    }
    return $model;
  }

  /**
  * @inheritDoc
  */
  public function getModel() : \codename\core\model
  {
    return $this->model;
  }

  /**
   * @inheritDoc
   */
  public function store(array $data) : bool
  {
    // \codename\core\app::getResponse()->setData(
    //     'model_complex_store',
    //     array_merge(
    //       \codename\core\app::getResponse()->getData('model_complex_store') ?? [],
    //       [ $data ]
    //     )
    // );

    $normalizedData = $this->handleStore($this->model, $data);

    // if($this->virtualStoreEnabled) {
    //   $this->virtualStore[] = $normalizedData; // $this->model->normalizeData($data);
    // } else {
    //
    //
    //
    //
    // }

    if($this->method == 'replace') {
      // perform a "manual" replace
      // $normalizedData = $this->model->normalizeData($data);

      if($normalizedData[$this->model->getPrimarykey()] ?? false) {
        // update based on supplied pkey vale
        if($this->virtualStoreEnabled) {
          $this->virtualStore[] = $normalizedData;
        } else {
          $this->model->saveWithChildren($normalizedData);
        }
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
              if($this->virtualStoreEnabled) {
                $this->virtualStore[] = $normalizedData;
              } else {
                $this->model->saveWithChildren($normalizedData);
              }
            } else if(count($res) === 0) {
              // insert
              if($this->virtualStoreEnabled) {
                $this->virtualStore[] = $normalizedData;
              } else {
                $this->model->saveWithChildren($normalizedData);
              }
            } else {
              // error - multiple results
              throw new exception('EXCEPTION_TARGET_MODEL_COMPLEX_MULTIPLE_UNIQUE_KEY_RESULTS', exception::$ERRORLEVEL_ERROR, $res);
            }
          }
        } else {
          // normal save
          if($this->virtualStoreEnabled) {
            $this->virtualStore[] = $normalizedData;
          } else {
            $this->model->saveWithChildren($normalizedData);
          }
        }
      }
    } else {
      if($this->virtualStoreEnabled) {
        $this->virtualStore[] = $normalizedData;
      } else {
        $this->model->saveWithChildren($normalizedData);
      }
    }
    return true;
    // $this->model->save($newData);
  }

  /**
   * handles store() recursively
   *
   * @param  \codename\core\model $model [description]
   * @param  array             $data  [description]
   * @return [type]                   [description]
   */
  protected function handleStore(\codename\core\model $model, array $data) {
    foreach($model->getNestedCollections() as $collection) {
      // work through each entry, modify on need
      foreach($data[$collection->field->get()] as &$subData) {
        // \codename\core\app::getResponse()->setData('model_complex_collection_handleStore', array_merge(
        //   \codename\core\app::getResponse()->getData('model_complex_collection_handleStore') ?? [],
        //   [
        //     [
        //       'model' => $model->getIdentifier(),
        //       'collectionModel' => $collection->collectionModel->getIdentifier(),
        //       'subData' => $subData
        //     ]
        //   ]
        // ));
        $subData = $this->handleStore($collection->collectionModel, $subData);
      }
    }
    foreach($model->getNestedJoins() as $join) {
      // work through each join, modify on need
      // dive deeper, first

      // \codename\core\app::getResponse()->setData('model_complex_joins_dive', array_merge(
      //   \codename\core\app::getResponse()->getData('model_complex_joins_dive') ?? [],
      //   [
      //     [
      //       'model' => $model->getIdentifier(),
      //       'join' => $join->model->getIdentifier(),
      //       'data' => $data,
      //       'registered_child' => self::isRegisteredChild($model, $join->modelField)
      //     ]
      //   ]
      // ));

      if(self::isRegisteredChild($model, $join->modelField)) {
        // case 1: model/join has a child config - no direct action needed, dive deeper
        $childVirtualField = self::getChildField($model, $join->modelField);
        if(array_key_exists($childVirtualField, $data) && $data[$childVirtualField] !== null) {
          $data[$childVirtualField] = $this->handleStore($join->model, $data[$childVirtualField]);
        }
      } else {
        // case 2: model/join has no child config - dive deeper and save/normalize
        $data = $this->handleStore($join->model, $data);

        // TODO: save and get lastInsertId, modify data
        // $this->model->save($data);
      }
    }

    if($this->virtualStoreEnabled) {
      // pseudo-save. don't perform anything
      // $data[$model->getPrimarykey()] = 'dry-run';
    } else {
      // "save" the data
    }

    return $data;
  }


  /**
   * [isRegisteredChild description]
   * @param  \codename\core\model $model [description]
   * @param  string               $field [description]
   * @return bool                     [description]
   */
  protected static function isRegisteredChild(\codename\core\model $model, string $field) : bool {
    if($model->config->exists('children')) {
      foreach($model->config->get('children') as $child => $childConfig) {
        if($childConfig['type'] === 'foreign') {
          if($childConfig['field'] == $field) {
            return true;
          }
        } else if($childConfig['type'] === 'collection') {
          // TODO
          // if($childConfig['field'] == $field) {
          //   return true;
          // }
        }
      }
    }
    return false;
  }

  /**
   * [getChildField description]
   * @param  \codename\core\model $model [description]
   * @param  string               $field [description]
   * @return string|null                     [description]
   */
  protected static function getChildField(\codename\core\model $model, string $field) {
    if($model->config->exists('children')) {
      foreach($model->config->get('children') as $child => $childConfig) {
        if($childConfig['type'] === 'foreign') {
          if($childConfig['field'] == $field) {
            return $child;
          }
        }
      }
    }
    return null;
  }

  /**
   * determines the finished status of this target
   * @var bool
   */
  protected $finished = false;

  /**
   * @inheritDoc
   */
  public function finish()
  {
    return; // ?
  }


}
