<?php

namespace codename\core\io\target;

use codename\core\app;
use codename\core\exception;
use codename\core\io\target;
use codename\core\io\targetModelInterface;
use ReflectionException;

/**
 * model as a target
 */
class model extends target implements targetModelInterface
{
    /**
     * target model
     * @var \codename\core\model
     */
    protected \codename\core\model $model;

    /**
     * store method
     * 'save' or 'replace'
     * @var string
     */
    protected string $method = 'save';
    /**
     * [$uniqueKeys description]
     * @var null|array
     */
    protected ?array $uniqueKeys = null;

    /**
     * @param string $name
     * @param array $config
     * @throws ReflectionException
     * @throws exception
     */
    public function __construct(string $name, array $config)
    {
        parent::__construct($name, $config);
        $this->model = app::getModel($config['model'], $config['app'] ?? '', $config['vendor'] ?? '');
        $this->method = $config['method'] ?? 'save';

        $this->uniqueKeys = $this->model->getConfig()->get('unique') ?? null;
    }

    /**
     * [getModel description]
     * @return \codename\core\model [description]
     */
    public function getModel(): \codename\core\model
    {
        return $this->model;
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
        // TODO: validate?
        // TODO: wrap in a try/catch and return true/false depending on error or success
        if ($this->method == 'replace') {
            // perform a "manual" replace
            $normalizedData = $this->model->normalizeData($data);

            if ($normalizedData[$this->model->getPrimaryKey()] ?? false) {
                // update based on supplied pkey vale
                $this->model->save($normalizedData);
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
                        $this->model->save($normalizedData);
                    } elseif (count($res) === 0) {
                        // insert
                        $this->model->save($normalizedData);
                    } else {
                        // error - multiple results
                        throw new exception('EXCEPTION_TARGET_MODEL_MULTIPLE_UNIQUE_KEY_RESULTS', exception::$ERRORLEVEL_ERROR, $res);
                    }
                } elseif ($this->config['ignore_unique'] ?? false) {
                    //
                    // no unique key filters active, needs "ignore_unique"
                    //
                    $this->model->save($normalizedData);
                }
            } else {
                // normal save
                $this->model->save($normalizedData);
            }
        } else {
            $this->model->save($this->model->normalizeData($data));
        }
        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function finish(): void
    {
    }
}
