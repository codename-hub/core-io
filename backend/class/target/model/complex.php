<?php

namespace codename\core\io\target\model;

use codename\core\app;
use codename\core\exception;
use codename\core\io\target;
use codename\core\io\target\virtualTargetInterface;
use codename\core\io\targetModelInterface;
use codename\core\model;
use ReflectionException;

/**
 * complex model as a target
 */
class complex extends target implements
    targetModelInterface,
    virtualTargetInterface
{
    /**
     * target model
     * @var model
     */
    protected model $model;
    /**
     * store method
     * 'save' or 'replace'
     * @var string
     */
    protected string $method = 'save';
    /**
     * [$uniqueKeys description]
     * @var array
     */
    protected mixed $uniqueKeys = null;
    /**
     * [protected description]
     * @var array
     */
    protected array $virtualStore = [];
    /**
     * whether to store data or not
     * @var bool
     */
    protected bool $virtualStoreEnabled = false;
    /**
     * determines the finished status of this target
     * @var bool
     */
    protected bool $finished = false;

    /**
     * {@inheritDoc}
     * @param string $name
     * @param array $config
     * @throws ReflectionException
     * @throws exception
     */
    public function __construct(string $name, array $config)
    {
        parent::__construct($name, $config);
        $this->model = $this->buildModelStructure($config['structure']);
        $this->method = $config['method'] ?? 'save';
        $this->uniqueKeys = $this->model->getConfig()->get('unique') ?? null;
    }

    /**
     * [buildModelStructure description]
     * @param array $config [description]
     * @return model         [description]
     * @throws ReflectionException
     * @throws exception
     */
    protected function buildModelStructure(array $config): model
    {
        $model = app::getModel($config['model'], $config['app'] ?? '', $config['vendor'] ?? '');
        foreach ($config['join'] as $join) {
            $joinModel = $this->buildModelStructure($join);
            if (($join['type'] ?? false) === 'collection') {
                $model->addCollectionModel($joinModel, $join['modelfield'] ?? null);
            } else {
                $model->addModel($joinModel);
            }
        }
        return $model;
    }

    /**
     * @return model
     */
    public function getModel(): model
    {
        return $this->model;
    }

    /**
     * {@inheritDoc}
     */
    public function getVirtualStoreData(): array
    {
        return $this->virtualStore;
    }

    /**
     * {@inheritDoc}
     */
    public function getVirtualStoreEnabled(): bool
    {
        return $this->virtualStoreEnabled;
    }

    /**
     * {@inheritDoc}
     */
    public function setVirtualStoreEnabled(bool $state): void
    {
        $this->virtualStoreEnabled = $state;
    }

    /**
     * {@inheritDoc}
     * @param array $data
     * @return bool
     * @throws ReflectionException
     * @throws exception
     */
    public function store(array $data): bool
    {
        $normalizedData = $this->handleStore($this->model, $data);

        if ($this->method == 'replace') {
            if ($normalizedData[$this->model->getPrimaryKey()] ?? false) {
                // update based on supplied pkey vale
                if ($this->virtualStoreEnabled) {
                    $this->virtualStore[] = $normalizedData;
                } else {
                    $this->model->saveWithChildren($normalizedData);
                }
            } elseif ($this->uniqueKeys) {
                // detect existing record
                $filtersAdded = false;
                foreach ($this->uniqueKeys as $uniqueKey) {
                    if (is_array($uniqueKey)) {
                        // multiple keys, combined unique key
                        $filters = [];
                        foreach ($uniqueKey as $key) {
                            if ($normalizedData[$key] ?? false) {
                                $filters[] = ['field' => $key, 'operator' => '=', 'value' => $normalizedData[$key]];
                            } else {
                                // irrelevant unique key, one value is null
                                $filters = [];
                                break;
                            }
                        }
                        if (count($filters) > 0) {
                            $filtersAdded = true;
                            $this->model->addFilterCollection($filters);
                        }
                    } else {
                        // single unique key field
                        $filtersAdded = true;
                        $this->model->addFilter($uniqueKey, $normalizedData[$uniqueKey] ?? null);
                    }
                }
                if ($filtersAdded) {
                    $res = $this->model->search()->getResult();
                    if (count($res) === 1) {
                        // update using found PKEY
                        $normalizedData[$this->model->getPrimaryKey()] = $res[0][$this->model->getPrimaryKey()];
                        if ($this->virtualStoreEnabled) {
                            $this->virtualStore[] = $normalizedData;
                        } else {
                            $this->model->saveWithChildren($normalizedData);
                        }
                    } elseif (count($res) === 0) {
                        // insert
                        if ($this->virtualStoreEnabled) {
                            $this->virtualStore[] = $normalizedData;
                        } else {
                            $this->model->saveWithChildren($normalizedData);
                        }
                    } else {
                        // error - multiple results
                        throw new exception('EXCEPTION_TARGET_MODEL_COMPLEX_MULTIPLE_UNIQUE_KEY_RESULTS', exception::$ERRORLEVEL_ERROR, $res);
                    }
                }
            } elseif ($this->virtualStoreEnabled) {
                // normal save
                $this->virtualStore[] = $normalizedData;
            } else {
                $this->model->saveWithChildren($normalizedData);
            }
        } elseif ($this->virtualStoreEnabled) {
            $this->virtualStore[] = $normalizedData;
        } else {
            $this->model->saveWithChildren($normalizedData);
        }
        return true;
    }

    /**
     * handles store() recursively
     *
     * @param model $model [description]
     * @param array $data [description]
     * @return array [type]                   [description]
     */
    protected function handleStore(model $model, array $data): array
    {
        foreach ($model->getNestedCollections() as $collection) {
            // work through each entry, modify on need
            foreach ($data[$collection->field->get()] as &$subData) {
                $subData = $this->handleStore($collection->collectionModel, $subData);
            }
        }
        foreach ($model->getNestedJoins() as $join) {
            // work through each join, modify on need
            // dive deeper, first

            if (self::isRegisteredChild($model, $join->modelField)) {
                // case 1: model/join has a child config - no direct action needed, dive deeper
                $childVirtualField = self::getChildField($model, $join->modelField);
                if (array_key_exists($childVirtualField, $data) && $data[$childVirtualField] !== null) {
                    $data[$childVirtualField] = $this->handleStore($join->model, $data[$childVirtualField]);
                }
            } else {
                // case 2: model/join has no child config - dive deeper and save/normalize
                $data = $this->handleStore($join->model, $data);

                // TODO: save and get lastInsertId, modify data
                // $this->model->save($data);
            }
        }

        if ($this->virtualStoreEnabled) {
            // pseudo-save. don't perform anything
            // $data[$model->getPrimaryKey()] = 'dry-run';
        } else {
            // "save" the data
        }

        return $data;
    }

    /**
     * [isRegisteredChild description]
     * @param model $model [description]
     * @param string $field [description]
     * @return bool                     [description]
     */
    protected static function isRegisteredChild(model $model, string $field): bool
    {
        if ($model->config->exists('children')) {
            foreach ($model->config->get('children') as $childConfig) {
                if ($childConfig['type'] === 'foreign') {
                    if ($childConfig['field'] == $field) {
                        return true;
                    }
                } elseif ($childConfig['type'] === 'collection') {
                    // TODO
                }
            }
        }
        return false;
    }

    /**
     * [getChildField description]
     * @param model $model [description]
     * @param string $field [description]
     * @return string|null                     [description]
     */
    protected static function getChildField(model $model, string $field): ?string
    {
        if ($model->config->exists('children')) {
            foreach ($model->config->get('children') as $child => $childConfig) {
                if ($childConfig['type'] === 'foreign') {
                    if ($childConfig['field'] == $field) {
                        return $child;
                    }
                }
            }
        }
        return null;
    }

    /**
     * {@inheritDoc}
     */
    public function finish(): void
    {
    }
}
