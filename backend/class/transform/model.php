<?php

namespace codename\core\io\transform;

use codename\core\app;
use codename\core\exception;
use codename\core\io\transform;
use codename\core\model\plugin\filter\custom;
use ReflectionException;

/**
 * [model description]
 */
abstract class model extends transform
{
    /**
     * [protected description]
     * @var \codename\core\model
     */
    protected \codename\core\model $model;

    /**
     * {@inheritDoc}
     * @param array $config
     * @throws ReflectionException
     * @throws exception
     */
    public function __construct(array $config)
    {
        parent::__construct($config);
        $this->model = $this->buildModelStructure($this->config);
        if (isset($this->config['group'])) {
            foreach ($this->config['group'] as $group) {
                $this->model->addGroup($group);
            }
        }
        if ($calculatedFields = $this->config['calculated_fields'] ?? false) {
            foreach ($calculatedFields as $calculatedField) {
                $this->model->addCalculatedField($calculatedField['field'], $calculatedField['calculation']);
            }
        }
        if (isset($this->config['order'])) {
            foreach ($this->config['order'] as $order) {
                $this->model->addOrder($order['field'], $order['order']);
            }
        }
    }

    /**
     * [buildModelStructure description]
     * @param array $config [description]
     * @return \codename\core\model         [description]
     * @throws ReflectionException
     * @throws exception
     */
    protected function buildModelStructure(array $config): \codename\core\model
    {
        $model = app::getModel($config['model'], $config['app'] ?? '', $config['vendor'] ?? '');

        if ($config['virtualFieldResult'] ?? false) {
            $model->setVirtualFieldResult(true);
        }

        if ($fields = $config['fields'] ?? null) {
            $model->hideAllFields();
            foreach ($fields as $field) {
                $model->addField($field);
            }
        }

        if ($joins = $config['join'] ?? null) {
            foreach ($joins as $join) {
                $joinModel = $this->buildModelStructure($join);
                if (($join['type'] ?? false) === 'collection') {
                    $model->addCollectionModel($joinModel, $join['modelfield'] ?? null);
                } else {
                    $model->addModel($joinModel);
                }
            }
        }

        return $model;
    }

    /**
     * {@inheritDoc}
     */
    public function getSpecification(): array
    {
        $sources = [
          "model.{$this->config['model']}",
        ];

        if (isset($this->config['filter'])) {
            foreach ($this->config['filter'] as $filter) {
                if (isset($filter['value']['source'])) {
                    $field = is_array($filter['value']['field']) ? implode('.', $filter['value']['field']) : $filter['value']['field'];
                    $sources[] = "{$filter['value']['source']}.$field";
                } else {
                    // add pure value sources?
                }
            }
        }

        if (isset($this->config['filtercollection'])) {
            foreach ($this->config['filtercollection'] as $filtercollection) {
                foreach ($filtercollection['filters'] as $filter) {
                    if ($filter['value'] && isset($filter['value']['source'])) {
                        $field = is_array($filter['value']['field']) ? implode('.', $filter['value']['field']) : $filter['value']['field'];
                        $sources[] = "{$filter['value']['source']}.$field";
                    } else {
                        // add pure value sources?
                    }
                }
            }
        }

        return [
          'type' => 'transform',
          'source' => $sources,
        ];
    }

    /**
     * [doQuery description]
     * @param mixed $value [description]
     * @return array|null        [description]
     * @throws ReflectionException
     * @throws exception
     */
    protected function doQuery(mixed $value): ?array
    {
        $this->model->reset();
        if ($this->config['filter'] ?? false) {
            foreach ($this->config['filter'] as $filter) {
                // either use the value directly or get it from the current dataset
                if ($filter['value'] && isset($filter['value']['source'])) {
                    $useValue = $this->getValue($filter['value']['source'], $filter['value']['field'], $value);
                    if ($useValue == null) {
                        //
                        // NOTE/CHANGED 2020-04-28: Added option "allow_null" to explicitly allow NULL values
                        // for filtering here
                        //
                        if ($filter['value']['allow_null'] ?? false) {
                            // Do nothing
                        } else {
                            if ($this->config['required'] ?? false) {
                                $this->errorstack->addError('VALUE_NULL', 0, [
                                  'config' => $this->config,
                                  'value' => $value,
                                ]);
                            }
                            return null;
                        }
                    }
                } else {
                    $useValue = $filter['value'];
                }
                $this->model->addFilter($filter['field'], $useValue, $filter['operator']);
            }
        }

        if ($this->config['custom_filter'] ?? false) {
            foreach ($this->config['custom_filter'] as $customFilter) {
                // either use the value directly or get it from the current dataset
                if (isset($customFilter['value']['source'])) {
                    $useValue = $this->getValue($customFilter['value']['source'], $customFilter['value']['field'], $value);
                    if ($useValue == null) {
                        if ($customFilter['value']['allow_null'] ?? false) {
                            // Do nothing
                        } else {
                            if ($this->config['required'] ?? false) {
                                $this->errorstack->addError('VALUE_NULL', 0, [
                                  'config' => $this->config,
                                  'value' => $value,
                                ]);
                            }
                            return null;
                        }
                    }
                } else {
                    $useValue = $customFilter['value'];
                }

                $filterPlugin = new custom(
                    \codename\core\value\text\modelfield\dummy::getInstance(
                        $customFilter['field']
                    ),
                    $useValue,
                    $customFilter['operator']
                );
                $this->model->addFilterPlugin($filterPlugin);
            }
        }

        if ($this->config['aggregate_filter'] ?? false) {
            foreach ($this->config['aggregate_filter'] as $filter) {
                if ($filter['value'] && isset($filter['value']['source'])) {
                    $useValue = $this->getValue($filter['value']['source'], $filter['value']['field'], $value);
                    if ($useValue == null) {
                        if ($this->config['required'] ?? false) {
                            $this->errorstack->addError('VALUE_NULL', 0, [
                              'config' => $this->config,
                              'value' => $value,
                            ]);
                        }
                        return null;
                    }
                } else {
                    $useValue = $filter['value'];
                }
                $this->model->addAggregateFilter($filter['field'], $useValue, $filter['operator']);
            }
        }

        if ($this->config['flagfilter'] ?? false) {
            $flags = $this->model->config->get('flag');

            $flagStates = $this->config['flagfilter']['states'] ?? null;
            if ($flagStates) {
                if (is_array($flagStates) && ($flagStates['source'] ?? false)) {
                    // dynamic source
                    $flagStates = $this->getValue($flagStates['source'], $flagStates['field'], $value);
                }

                foreach ($flagStates as $flagName => $state) {
                    if ($state) {
                        $this->model->withFlag($flags[$flagName]);
                    } else {
                        $this->model->withoutFlag($flags[$flagName]);
                    }
                }
            }

            $withFlag = $this->config['flagfilter']['with_flag'] ?? null;
            if ($withFlag) {
                if (is_array($withFlag) && ($withFlag['source'] ?? false)) {
                    // dynamic source
                    $withFlag = $this->getValue($withFlag['source'], $withFlag['field'], $value);
                }

                $withFlag = is_array($withFlag) ? $withFlag : [$withFlag];
                foreach ($withFlag as $flag) {
                    $this->model->withFlag($flags[$flag]);
                }
            }

            $withoutFlag = $this->config['flagfilter']['without_flag'] ?? null;
            if ($withoutFlag) {
                if (is_array($withoutFlag) && ($withoutFlag['source'] ?? false)) {
                    // dynamic source
                    $withoutFlag = $this->getValue($withoutFlag['source'], $withoutFlag['field'], $value);
                }

                $withoutFlag = is_array($withoutFlag) ? $withoutFlag : [$withoutFlag];
                foreach ($withoutFlag as $flag) {
                    $this->model->withFlag($flags[$flag]);
                }
            }
        }

        if (isset($this->config['filtercollection'])) {
            foreach ($this->config['filtercollection'] as $name => $filtercollection) {
                $filters = [];
                foreach ($filtercollection['filters'] as $filter) {
                    $useValue = null;
                    if ($filter['value'] && isset($filter['value']['source'])) {
                        $useValue = $this->getValue($filter['value']['source'], $filter['value']['field'], $value);
                        if ($useValue == null) {
                            if ($this->config['required']) {
                                $this->errorstack->addError('VALUE_NULL', 0, [
                                  'config' => $this->config,
                                  'value' => $value,
                                ]);
                            }
                        }
                    } else {
                        $useValue = $filter['value'];
                    }
                    $filters[] = [
                      'field' => $filter['field'],
                      'value' => $useValue,
                      'operator' => $filter['operator'],
                    ];
                }
                $this->model->addFilterCollection($filters, $filtercollection['group_operator'], $name, $filtercollection['conjunction']);
            }
        }

        /**
         * DON'T YOU EVER DARE TO ADD GROUPS EVERY TIME THIS CODE RUNS
         *
         * In case you still do, please follow these guidelines:
         *
         * 1. Pick the nearest piece of wood or wooden objects
         * 2. Grab with both hands in front of the facial area
         * 3. Slap until bleed.
         */
        /* if(isset($this->config['group'])) {
          foreach($this->config['group'] as $group) {
            $this->model->addGroup($group);
          }
        }*/

        if (isset($this->config['debug']) && $this->config['debug']) {
            echo("<pre>");
            print_r($this->model);
            echo("</pre>");
            // die();
        }

        return $this->model->search()->getResult();
    }
}
