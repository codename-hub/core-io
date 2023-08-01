<?php

namespace codename\core\io;

use codename\core\config;
use codename\core\config\json;
use codename\core\errorstack;
use codename\core\exception;
use codename\core\helper\deepaccess;
use codename\core\io\datasource\buffered;
use codename\core\io\datasource\remap;
use codename\core\io\target\virtualTargetInterface;
use codename\core\model;
use codename\core\model\schematic\sql;
use codename\core\model\schemeless\dynamic;
use codename\core\response\cli;
use codename\core\transaction;
use PDO;
use ReflectionException;

use function count;
use function is_array;

/**
 * [pipeline description]
 */
class pipeline implements transformerInterface
{
    /**
     * [EXCEPTION_CORE_IO_PIPELINE_BEGINTRANSACTIONS_CALLED_TWICE description]
     * @var string
     */
    public const EXCEPTION_CORE_IO_PIPELINE_BEGINTRANSACTIONS_CALLED_TWICE = 'EXCEPTION_CORE_IO_PIPELINE_BEGINTRANSACTIONS_CALLED_TWICE';
    /**
     * [EXCEPTION_CORE_IO_PIPELINE_GETTRANSFORM_INSTANCE_OF_WRONG_BASE_CLASS description]
     * @var string
     */
    public const EXCEPTION_CORE_IO_PIPELINE_GETTRANSFORM_INSTANCE_OF_WRONG_BASE_CLASS = 'EXCEPTION_CORE_IO_PIPELINE_GETTRANSFORM_INSTANCE_OF_WRONG_BASE_CLASS';
    /**
     * [EXCEPTION_CORE_IO_PIPELINE_GETTRANSFORM_NOTFOUND description]
     * @var string
     */
    public const EXCEPTION_CORE_IO_PIPELINE_GETTRANSFORM_NOTFOUND = 'EXCEPTION_CORE_IO_PIPELINE_GETTRANSFORM_NOTFOUND';
    /**
     * [EXCEPTION_CORE_IO_PIPELINE_GETTRANSFORM_MISSING_CLASS description]
     * @var string
     */
    public const EXCEPTION_CORE_IO_PIPELINE_GETTRANSFORM_MISSING_CLASS = 'EXCEPTION_CORE_IO_PIPELINE_GETTRANSFORM_MISSING_CLASS';
    /**
     * [EXCEPTION_CORE_IO_PIPELINE_GETTARGET_CONFIG_MISSING_TARGET description]
     * @var string
     */
    public const EXCEPTION_CORE_IO_PIPELINE_GETTARGET_CONFIG_MISSING_TARGET = 'EXCEPTION_CORE_IO_PIPELINE_GETTARGET_CONFIG_MISSING_TARGET';
    /**
     * [EXCEPTION_CORE_IO_PIPELINE_GETTARGET_MISSING_CLASS description]
     * @var string
     */
    public const EXCEPTION_CORE_IO_PIPELINE_GETTARGET_MISSING_CLASS = 'EXCEPTION_CORE_IO_PIPELINE_GETTARGET_MISSING_CLASS';
    /**
     * debug flag
     * @var bool
     */
    public bool $debug = false;
    /**
     * pipeline configuration
     * @var config|json|null
     */
    protected json|config|null $config = null;
    /**
     * data sources for specifier(s)
     * @var null|datasource
     */
    protected ?datasource $datasource = null;
    /**
     * [protected description]
     * @var array
     */
    protected array $transforms = [];
    /**
     * [protected description]
     * @var array
     */
    protected array $preprocessors = [];
    /**
     * [protected description]
     * @var bool
     */
    protected bool $datasourceBuffering = false;
    /**
     * [protected description]
     * @var int
     */
    protected int $datasourceBufferSize = 1000;
    /**
     * options
     * @var null|config
     */
    protected ?config $options = null;
    /**
     * stores the count of  items in the current datasource
     * @var null|int
     */
    protected ?int $itemCount = null;
    /**
     * stores the current index that is processed
     * @var null|int
     */
    protected ?int $itemIndex = null;
    /**
     * [protected description]
     * @var int
     */
    protected int $storedItemCount = 0;
    /**
     * callback for tracking status/progress
     * @var callable|null
     */
    protected $processCallback = null;
    /**
     * targets (as model instances)
     * @var model[]
     */
    protected array $targetModelInstances = [];
    /**
     * the bottom limit (start, index) of pipeline source
     * @var null|int
     */
    protected ?int $startIndex = null;
    /**
     * the top limit (end, index) of pipeline source
     * @var null|int
     */
    protected ?int $endIndex = null;
    /**
     * whether to throw an exception if an erroneous dataset is detected
     * @var bool
     */
    protected bool $throwExceptionOnErroneousData = false;
    /**
     * whether to track errors in a global pipeline errorstack
     * @var bool
     */
    protected bool $errorstackEnabled = false;
    /**
     * the global pipeline errorstack
     * @var null|errorstack
     */
    protected ?errorstack $errorstack = null;
    /**
     * whether to skip erroneous processes
     * which means: if any of the target fails/runs into erroneous state
     * do not handle the whole source entry it originates from
     * so neither of the targets gets to really STORE data for this entry.
     * @var bool
     */
    protected bool $skipErroneous = false;
    /**
     * track erroneous entries to be stored in targets
     * @var bool
     */
    protected bool $trackErroneous = false;
    /**
     * [protected description]
     * @var array
     */
    protected array $erroneousEntries = [];
    /**
     * active PDO connections
     * for handling open transactions
     * on a per-database connection basis
     * @var array
     */
    protected array $activeConnections = [];
    /**
     * [protected description]
     * @var null|transaction
     */
    protected ?transaction $transaction = null;
    /**
     * determines, if the pipeline is running in dryRun mode
     * (no real stores/saves happening)
     *
     * @var bool
     */
    protected bool $dryRun = true;
    /**
     * @var array
     */
    protected array $target = [];

    /**
     * @param string|null $config_pipeline_file [pipeline config / definition]
     * @param array|null $configData [optional, explicit configuration]
     * @throws ReflectionException
     * @throws exception
     */
    public function __construct(string $config_pipeline_file = null, array $configData = null)
    {
        if ($config_pipeline_file !== null) {
            // NOTE: 2018-08-30 changed json config to use appstack, but no inheritance between config file hierarchies
            // load a config file
            $this->config = (new json($config_pipeline_file, true, false));
        } elseif ($configData !== null) {
            // load a config object/array
            $this->config = new config($configData);
        }

        if ($this->config->get('config>datasource_buffering')) {
            $this->setDatasourceBuffering(true, $this->config->get('config>datasource_buffer_size') ?? 1000);
        }
    }

    /**
     * enables buffering of the main datasource
     * has to be enabled before setting the datasource
     *
     * @param bool $state [description]
     * @param int $bufferSize [description]
     */
    public function setDatasourceBuffering(bool $state, int $bufferSize = 1000): void
    {
        $this->datasourceBuffering = $state;
        $this->datasourceBufferSize = $bufferSize;
    }

    /**
     * [getConfig description]
     * @return config [description]
     */
    public function getConfig(): config
    {
        return $this->config;
    }

    /**
     * [getDatasource description]
     * @return datasource [description]
     */
    public function getDatasource(): datasource
    {
        return $this->datasource;
    }

    /**
     * [setDatasource description]
     * @param datasource $datasource [description]
     * @throws exception
     */
    public function setDatasource(datasource $datasource): void
    {
        if ($datasource instanceof setPipelineInstanceInterface) {
            $datasource->setPipelineInstance($this);
        }

        //
        // If datasource buffering is enabled
        // encapsulate the datasource
        //
        if ($this->datasourceBuffering) {
            $this->datasource = new buffered($datasource, $this->datasourceBufferSize);
        } else {
            $this->datasource = $datasource;
        }

        $this->itemCount = $this->datasource->currentProgressLimit();
    }

    /**
     * [setOptions description]
     * @param array $options [description]
     */
    public function setOptions(array $options): void
    {
        $this->options = new config($options);
    }

    /**
     * [getItemCount description]
     * @return int [description]
     */
    public function getItemCount(): int
    {
        return $this->itemCount;
    }

    /**
     * @return int
     */
    public function getItemIndex(): int
    {
        return $this->datasource->currentProgressPosition();
    }

    /**
     * returns the number of how many times store() on a target has been called
     * @return int [description]
     */
    public function getStoredItemCount(): int
    {
        return $this->storedItemCount;
    }

    /**
     * [setProcessCallback description]
     * @param callable $callback [description]
     */
    public function setProcessCallback(callable $callback): void
    {
        $this->processCallback = $callback;
    }

    /**
     * create a datasource matching the configured type
     * @param mixed $args
     * @return  datasource [the freshly created datasource]
     * @throws ReflectionException
     * @throws exception
     */
    public function createDatasource(mixed $args): datasource
    {
        $class = app::getInheritedClass('datasource_' . $this->config->get('source>type'));
        $datasource = new $class($args);

        if ($datasource instanceof setPipelineInstanceInterface) {
            $datasource->setPipelineInstance($this);
        }

        $datasource->setConfig($this->config->get('source>config'));
        return $datasource;
    }

    /**
     * enable or disable debugging
     * @param bool $debug [true/false]
     */
    public function setDebug(bool $debug): void
    {
        $this->debug = $debug;
    }

    /**
     * hard limit the pipeline source
     * @param int|null $startIndex [start index or null for none]
     * @param int|null $endIndex [end index or null for none]
     */
    public function setLimit(?int $startIndex, ?int $endIndex): void
    {
        $this->startIndex = $startIndex;
        $this->endIndex = $endIndex;
    }

    /**
     * perform pipeline processes/transforms
     * @return void [type] [description]
     * @throws ReflectionException
     * @throws exception
     */
    public function run(): void
    {
        if (app::getResponse() instanceof cli) {
            echo("[PIPELINE] starting pipeline..." . chr(10));
        }

        if ($this->errorstackEnabled) {
            $this->errorstack = new errorstack('PIPELINE');
        } else {
            $this->errorstack = null;
        }

        // first step: validate source(s)!
        $sourceConfig = $this->config->get('source');

        if (isset($sourceConfig['type'])) {
            // $sourceConfig['type']
            // explicitly check datasource type
        }

        // explicitly configure datasource
        if (isset($sourceConfig['config']) && !($this->datasource instanceof remap)) {
            $this->datasource->setConfig($sourceConfig['config']);
        }

        if (!empty($sourceConfig['schema']) && !empty($sourceConfig['model'])) {
            $dynamicModel = new dynamic();
            $dynamicModel->setConfig($sourceConfig['schema'], $sourceConfig['model']);

            $datasource = $this->datasource;

            //
            // TODO: FIX!
            //
            $i = 0;
            $max = 5500;

            $start = microtime(true);

            foreach ($datasource as $data) {
                $dynamicModel->validate($data);
                $errors = $dynamicModel->getErrors();

                if (count($errors) > 0) {
                    echo("<pre>" . print_r($data, true) . "\nErrors: " . print_r($errors, true) . "</pre>");
                }

                $dynamicModel->reset();

                $i++;

                if ($i >= $max) {
                    break;
                }
            }
            //todo: save loop
            $end = microtime(true);
            echo("Validated $i entries in " . ($end - $start) . " seconds.");
        }

        // @TODO: cancel if $datasource is null or invalid


        // pre-instance transforms
        $this->createTransforms();

        // simply target names
        $targets = array_keys($this->config->get('target'));

        // start at item index
        $itemIndex = 0;

        // count stored items cross-target
        $storedItemTargets = 0;


        $start = microtime(true);

        $timings = [];
        $timingsMaxIndex = null;
        $timingsMax = 0.0;
        $timingsMaxData = null;

        // reset global item index
        $this->itemIndex = 0;
        $this->storedItemCount = 0;

        $this->beginTransactions($targets);

        //
        // do preprocessing
        //
        if ($this->config->exists('preprocess')) {
            foreach ($this->config->get('preprocess') as $name => $config) {
                $preprocessor_class = app::getInheritedClass('process_' . $config['type']);
                $preprocessor = new $preprocessor_class($config['config']);
                if ($preprocessor instanceof process) {
                    $preprocessor->setPipelineInstance($this);

                    if (app::getResponse() instanceof cli) {
                        echo("[PIPELINE] starting preprocessor '$name'..." . chr(10));
                    }

                    $preprocessor->run();
                } else {
                    throw new exception('EXCEPTION_CORE_IO_PIPELINE_PREPROCESSOR_INVALID_CLASS', exception::$ERRORLEVEL_FATAL, $preprocessor_class);
                }
            }
        }

        if (app::getResponse() instanceof cli) {
            echo("[PIPELINE] starting main pipeline..." . chr(10));
        }

        // create a dummy transform for accessing pipeline internals
        $dummyTransform = new transform\dummy([]);
        $dummyTransform->setPipelineInstance($this);
        $dummyTransform->setTransformerInstance($this);

        // reset this thing:
        $this->erroneousEntries = [];

        // get some tagging information, if available
        $targetTags = [];
        foreach ($targets as $target) {
            if ($tagMappings = $this->config->get('target>' . $target . '>tags')) {
                $targetTags[$target] = $tagMappings;
            }
        }

        foreach ($this->datasource as $data) {
            //
            // Debugging
            //
            $dataStart = null;
            if ($this->debug) {
                $dataStart = microtime(true);
            }

            $itemIndex++;

            // increase global item index
            $this->itemIndex++;


            //
            // skip  first x items
            //
            if ($this->startIndex != null && $itemIndex < $this->startIndex) {
                continue;
            }

            //
            // omit if we have a null value in-between
            //
            if ($data == null) {
                continue;
            }

            //
            // reset all transforms (cache + errorstack)
            // this is very important
            // as we're not performing a multi-value, hash-based in-memory caching
            // but instead, simply store the last transform result, respectively
            //
            foreach ($this->transforms as $transform) {
                $transform->reset();
            }

            // buffer each target, before calling store()
            // which means: we wait for each target to complete
            // data generation/transformation,
            // so we're able to cancel a whole source entry,
            // if any target fails to get the data correctly.
            $targetDataBuffer = [];
            $targetTagsBuffer = [];
            $targetErroneousBuffer = [];

            // handle targets
            foreach ($targets as $targetname) {
                $target = $this->getTarget($targetname);

                //
                // continue, if the current dataset doesn't match the source filters for this target
                // before beginning the pipeline run
                //
                if ($target->matchesSourceFilters($data) === false) {
                    continue;
                }

                // perform the final mapping
                $mapped = [];

                // optional: for use with tagging
                $tags = null;

                $erroneous = [
                  'erroneous' => false,
                    // 'value' => null, // set later
                  'errors' => [],
                ];
                $errorMap = [];

                foreach ($target->getMapping() as $map => $mapconfig) {
                    $processed = null;

                    if ($mapconfig['type'] == 'source') {
                        $processed = $data[$mapconfig['field']] ?? null;
                    }

                    if ($mapconfig['type'] == 'source_deep') {
                        $processed = deepaccess::get($data, $mapconfig['field']);
                    }

                    if ($mapconfig['type'] == 'transform') {
                        if (!isset($this->transforms[$mapconfig['field']])) {
                            throw new exception('EXCEPTION_CORE_IO_PIPELINE_MISSING_TRANSFORM', exception::$ERRORLEVEL_ERROR, $mapconfig['field']);
                        }
                        $transformInstance = $this->transforms[$mapconfig['field']];
                        $processed = $transformInstance->transform($data);

                        // track errorstate and errors
                        if (count($transformInstance->getErrors()) > 0) {
                            if (!$erroneous['erroneous']) {
                                $erroneous['erroneous'] = true;
                            }
                            $erroneous['errors'] = array_merge($erroneous['errors'], $transformInstance->getErrors());
                        }

                        // do not reset errors here - we handle it when we start handling a new data item
                        // $transformInstance->resetErrors();
                    }

                    if ($mapconfig['type'] == 'transform_deep') {
                        $field = $mapconfig['field'][0];
                        $path = array_slice($mapconfig['field'], 1);

                        if (!isset($this->transforms[$field])) {
                            throw new exception('EXCEPTION_CORE_IO_PIPELINE_MISSING_TRANSFORM', exception::$ERRORLEVEL_ERROR, $field);
                        }
                        $transformInstance = $this->transforms[$field];
                        $transformed = $transformInstance->transform($data);

                        if (count($path) > 0) {
                            $processed = deepaccess::get($transformed, $path);
                        } else {
                            // only one object path item specified - transform name itself
                            $processed = $transformed;
                        }

                        // track errorstate and errors
                        if (count($transformInstance->getErrors()) > 0) {
                            if (!$erroneous['erroneous']) {
                                $erroneous['erroneous'] = true;
                            }
                            $erroneous['errors'] = array_merge($erroneous['errors'], $transformInstance->getErrors());
                        }

                        // do not reset errors here - we handle it when we start handling a new data item
                        // $transformInstance->resetErrors();
                    }

                    if ($mapconfig['type'] == 'constant') {
                        $field = $mapconfig['field'];
                        if (is_array($field)) {
                            $processed = deepaccess::get($this->config->get('constants'), $field);
                        } else {
                            $processed = $this->config->get('constants>' . $field);
                        }
                    }

                    if ($mapconfig['type'] == 'erroneous') {
                        // add an error value to be handled after this loop
                        $errorMap[$map] = $mapconfig;
                    } else {
                        $mapped[$map] = $processed;
                    }
                }

                if ($erroneous['erroneous']) {
                    //
                    // set error value
                    // and handle error maps
                    //
                    foreach ($errorMap as $map => $mapconfig) {
                        if (isset($erroneous[$mapconfig['field']])) {
                            $mapped[$map] = $erroneous[$mapconfig['field']] ?? null; // set or null
                        } elseif ($mapconfig['field'] == 'data') {
                            // $mapped[$map] = $data;
                            $dataUtf8 = [];
                            foreach ($data as $k => $v) {
                                if (mb_check_encoding($k, 'UTF-8') === false) {
                                    $k = utf8_encode($k);
                                }
                                if (!is_array($v) && mb_check_encoding($v, 'UTF-8') === false) {
                                    $v = utf8_encode($v);
                                }
                                $dataUtf8[$k] = $v;
                            }
                            $mapped[$map] = $dataUtf8;
                        } elseif ($mapconfig['field'] == 'errorstack') {
                            $mapped[$map] = $erroneous;
                        }
                    }

                    // also keep track of erroneous states
                    $targetErroneousBuffer[$targetname] = $erroneous;

                    if ($this->errorstackEnabled) {
                        $this->errorstack->addErrors($erroneous['errors']);
                    }

                    if ($this->throwExceptionOnErroneousData) {
                        throw new exception('EXCEPTION_PIPELINE_ERRONEOUS_DATA', exception::$ERRORLEVEL_ERROR, $erroneous);
                    }
                } else {
                    //
                    // if not erroneous, map null values
                    //
                    foreach ($errorMap as $map => $mapconfig) {
                        if (($mapconfig['field'] ?? null) === 'erroneous') {
                            $mapped[$map] = null;
                        } elseif ($mapconfig['field'] == 'data') {
                            $mapped[$map] = null;
                        }
                    }
                }

                //
                // CHANGED 2019-07-14:
                // tags are now generated/transformed AFTER target pipeline process
                // to get erroneous data, optionally.
                //
                if ($targetTags[$targetname] ?? false) {
                    // tags are not part of the final mapped data
                    // instead, store() is invoked with it as the 2nd parameter
                    $tags = $tags ?? [];

                    // use the previously created dummy transform to get the value
                    foreach ($targetTags[$targetname] as $map => $config) {
                        $tags[$map] = $dummyTransform->getInternalPipelineValue($config['type'], $config['field'], $data);
                    }
                }

                //
                // continue, if the current dataset doesn't match the target data filters
                // after receiving the complete, transformed result
                //
                if ($target->matchesTargetFilters($mapped) === false) {
                    continue;
                }

                // save!

                $storedItemTargets++;

                if ($this->dryRun) {
                    if ($this->debug && $erroneous['erroneous']) {
                        $end = microtime(true);
                        echo("<pre>");
                        echo("ERRONEOUS");
                        print_r($mapped);
                        echo("Item: $itemIndex Time: " . ($end - $start));
                        echo("</pre>");
                    }
                }
                $targetDataBuffer[$targetname] = $mapped;
                $targetTagsBuffer[$targetname] = $tags;
            }


            $anyErroneous = false;
            if ($this->skipErroneous || $this->trackErroneous) {
                foreach ($targets as $targetName) {
                    if ($targetErroneousBuffer[$targetName]['erroneous'] ?? null) {
                        // we have at least ONE erroneous entry
                        $anyErroneous = true;

                        if ($this->trackErroneous) {
                            $this->erroneousEntries[$targetName][] = [
                              'data' => $targetDataBuffer[$targetName] ?? null,
                              'tags' => $targetTagsBuffer[$targetName] ?? null,
                              'erroneous' => $targetErroneousBuffer[$targetName] ?? null,
                            ];
                        }
                    }
                }
            }

            //
            // if not advised to skip the entries,
            // store the data
            //
            if ((!$anyErroneous) || (!$this->skipErroneous)) {
                //
                // Final loop to ->store() the data
                //
                foreach ($targets as $targetName) {
                    if (!($targetDataBuffer[$targetName] ?? null)) {
                        continue;
                    }
                    if ($this->dryRun) {
                        // only use target, if virtual
                        $targetInstance = $this->getTarget($targetName);
                        if (($targetInstance instanceof virtualTargetInterface)) {
                            if ($targetInstance->getVirtualStoreEnabled()) {
                                $targetInstance->store($targetDataBuffer[$targetName], $targetTagsBuffer[$targetName]);
                                $this->storedItemCount++;
                            }
                        } else {
                            $targetInstance->store($targetDataBuffer[$targetName], $targetTagsBuffer[$targetName]);
                            $this->storedItemCount++;
                        }
                    } else {
                        $this->getTarget($targetName)->store($targetDataBuffer[$targetName], $targetTagsBuffer[$targetName]);
                        $this->storedItemCount++;
                    }
                }
            }

            if ($this->endIndex != null && $itemIndex > $this->endIndex) {
                break;
            }


            if ($this->debug) {
                $timings[$itemIndex] = (microtime(true) - $dataStart);

                if ($timings[$itemIndex] > $timingsMax) {
                    $timingsMaxIndex = $itemIndex;
                    $timingsMax = $timings[$itemIndex];
                    $timingsMaxData = [
                      'source' => $data,
                        // 'transforms' => $this->transforms
                      'transforms' => (array_map(function (string $key, transform $item) {
                          return [
                            'name' => $key,
                            'cacheHash' => $item->cacheHash,
                            'cacheValue' => $item->cacheValue,
                            'durationMeasured_msec' => round($item->durationMeasured * 1000, 4),
                            'durations_measured' => $item->durationsMeasured,
                            'durationsMeasured_avg_msec' => count($item->durationsMeasured) > 0 ? round((array_sum($item->durationsMeasured) / count($item->durationsMeasured)) * 1000, 4) : null,
                            'debugInfo' => $item->debugInfo,
                          ];
                      }, array_keys($this->transforms), $this->transforms)),
                    ];
                }
            }

            // call the callback to track progress or so.
            if ($this->processCallback != null) {
                $cb = $this->processCallback;
                $cb($this);
            }
        }

        if ($this->options && $this->options->get('preview')) {
            $this->rollbackTransactions();
        } else {
            $this->endTransactions();
        }

        if ($this->debug) {
            $end = microtime(true);
            app::getResponse()->setData('pipeline_run', ($end - $start));
            app::getResponse()->setData('pipeline_items', ($itemIndex));
            app::getResponse()->setData('pipeline_avg_items_per_sec', ($storedItemTargets / ($end - $start)));

            app::getResponse()->setData('timings_max', (max($timings)));
            app::getResponse()->setData('timings_max_index', $timingsMaxIndex);
            app::getResponse()->setData('timings_max_running', ($timingsMax));
            app::getResponse()->setData('timings_max_data', $timingsMaxData);

            // find the slowest transforms
            $transformsStats = $timingsMaxData['transforms'];
            $sortSuccess = usort($transformsStats, function (array $a, array $b) {
                // echo("<br>Compare $a['durationsMeasured_avg_msec'] <> ")
                return bccomp(($a['durationsMeasured_avg_msec'] ?? 0), ($b['durationsMeasured_avg_msec'] ?? 0), 8);
            });
            app::getResponse()->setData('timings_transforms_sorted', $sortSuccess ? $transformsStats : 'error');

            app::getResponse()->setData('timings_avg', (array_sum($timings) / $itemIndex));
        }
    }

    /**
     * resets and creates a new collection of named transforms
     * @return void
     * @throws ReflectionException
     * @throws exception
     */
    protected function createTransforms(): void
    {
        $this->transforms = [];
        if ($this->config->get('transform')) {
            foreach ($this->config->get('transform') as $name => $transform) {
                $this->transforms[$name] = $this->getTransform($transform);
            }
        }
    }

    /**
     * [getTransform description]
     * @param  [type]    $transformconfig [description]
     * @return transform                  [description]
     * @throws ReflectionException
     * @throws exception
     */
    protected function getTransform($transformconfig): transform
    {
        $class = app::getInheritedClass('transform_' . $transformconfig['type']);
        if (class_exists($class)) {
            $transform = new $class($transformconfig['config']);
            if ($transform instanceof transform) {
                $transform->setPipelineInstance($this);
                $transform->setTransformerInstance($this);
            } else {
                throw new exception(self::EXCEPTION_CORE_IO_PIPELINE_GETTRANSFORM_INSTANCE_OF_WRONG_BASE_CLASS, exception::$ERRORLEVEL_ERROR, $transformconfig['type']);
            }
            return $transform;
        } else {
            throw new exception(self::EXCEPTION_CORE_IO_PIPELINE_GETTRANSFORM_MISSING_CLASS, exception::$ERRORLEVEL_ERROR, $transformconfig['type']);
        }
    }

    /**
     * open transactions for a given collection of targets (by name)
     * @param string[] $targets [array of target names]
     * @return void
     * @throws ReflectionException
     * @throws exception
     */
    protected function beginTransactions(array $targets): void
    {
        // disallow double-calling beginTransactions
        if (count($this->activeConnections) > 0) {
            throw new exception(self::EXCEPTION_CORE_IO_PIPELINE_BEGINTRANSACTIONS_CALLED_TWICE, exception::$ERRORLEVEL_FATAL, $this->config);
        }

        $this->transaction = new transaction('pipeline', []);

        foreach ($targets as $targetname) {
            $target = $this->getTarget($targetname);
            if ($target instanceof targetModelInterface) {
                $model = $target->getModel();

                // only mysql and pgsql supported
                if ($model instanceof sql) {
                    $found = false;

                    $this->transaction->addTransactionable($model);

                    foreach ($this->activeConnections as $conn) {
                        if ($conn === $model->getConnection()->getConnection()) {
                            $found = true;
                            break;
                        }
                    }

                    if (!$found) {
                        $conn = $model->getConnection()->getConnection();
                        if (!$conn->inTransaction()) {
                            // $conn->beginTransaction();

                            // SQLite:  PRAGMA foreign_keys = OFF;
                            // MySQL:   SET foreign_key_checks = 0;

                            // Determine driver via connection
                            $driver = $model->getConnection()->driver;

                            if ($driver == 'mysql') {
                                $conn->exec('SET foreign_key_checks = 0;');
                                // $conn->exec('SET unique_checks = 0;'); // Disabled!
                                $conn->exec('SET autocommit = 0;');
                            } elseif ($driver == 'sqlite') {
                                // SQLite autocommit is handled automatically
                                // by beginning, ending or rollback of a transaction
                                $conn->exec('PRAGMA foreign_keys = OFF;');
                            } else {
                                // Throw an exception to avoid uninitiated transaction run
                                throw new exception('EXCEPTION_PIPELINE_BEGINTRANSACTIONS_UNSUPPORTED_CONNECTION_DRIVER', exception::$ERRORLEVEL_ERROR, $driver);
                            }

                            $this->activeConnections[] = $conn;
                        } else {
                            throw new exception('EXCEPTION_PIPELINE_BEGINTRANSACTIONS_ALREADY_ACTIVE_TRANSACTION', exception::$ERRORLEVEL_ERROR);
                        }
                    }
                }
            }
        }

        $this->transaction->start();
    }

    /**
     * get a target instance
     * by name
     *
     * @param string $name [description]
     * @return target       [description]
     * @throws ReflectionException
     * @throws exception
     */
    public function getTarget(string $name): target
    {
        if (!isset($this->target[$name])) {
            // auto-init target
            if ($this->config->exists('target>' . $name)) {
                $config = $this->config->get('target>' . $name);

                $class = \codename\core\app::getInheritedClass('target_' . $config['type']);

                if (class_exists($class)) {
                    $this->target[$name] = new $class($name, $config);
                } else {
                    throw new exception(self::EXCEPTION_CORE_IO_PIPELINE_GETTARGET_MISSING_CLASS, exception::$ERRORLEVEL_ERROR, [
                      'type' => $config['type'],
                      'class' => $class,
                    ]);
                }
            } else {
                throw new exception(self::EXCEPTION_CORE_IO_PIPELINE_GETTARGET_CONFIG_MISSING_TARGET, exception::$ERRORLEVEL_ERROR, $name);
            }
        }
        return $this->target[$name];
    }

    /**
     * rollback all open transactions
     * @return void
     * @throws exception
     */
    protected function rollbackTransactions(): void
    {
        foreach ($this->activeConnections as $conn) {
            $conn->rollback();

            $driver = $conn->getAttribute(PDO::ATTR_DRIVER_NAME);
            if ($driver == 'mysql') {
                // Autocommit re-enabling on MySQL
                $conn->exec('SET autocommit = 1;');
                // CHANGED: wasn't commented-in before...
                $conn->exec('SET foreign_key_checks = 1;');
            } elseif ($driver == 'sqlite') {
                // NOTE: For SQLite, we do not re-enable autocommit, as it is done per transaction
                // Re-enable FKEY checks
                $conn->exec('PRAGMA foreign_keys = ON;');
            } else {
                // Throw an exception to avoid rollback failure?
                throw new exception('EXCEPTION_PIPELINE_ROLLBACKTRANSACTIONS_UNSUPPORTED_CONNECTION_DRIVER', exception::$ERRORLEVEL_ERROR, $driver);
            }
        }
        $this->activeConnections = [];
    }

    /**
     * end/commit all open transactions
     * @return void
     * @throws exception
     */
    protected function endTransactions(): void
    {
        // end transactions for all connections that
        // we're included in the previous call to $this->beginTransactions()

        $this->transaction->end();

        foreach ($this->activeConnections as $conn) {
            // transactions should have ended
            // $conn->commit();

            $driver = $conn->getAttribute(PDO::ATTR_DRIVER_NAME);

            if ($driver == 'mysql') {
                // Autocommit re-enabling on MySQL
                $conn->exec('SET autocommit = 1;');
                // CHANGED: wasn't commented-in before...
                $conn->exec('SET foreign_key_checks = 1;');
            } elseif ($driver == 'sqlite') {
                // NOTE: For SQLite, we do not re-enable autocommit, as it is done per transaction
                // Re-enable FKEY checks
                $conn->exec('PRAGMA foreign_keys = ON;');
            } else {
                // error or skip?
                // Throw an exception to avoid uninitiated transaction run
                throw new exception('EXCEPTION_PIPELINE_ENDTRANSACTIONS_UNSUPPORTED_CONNECTION_DRIVER', exception::$ERRORLEVEL_ERROR, $driver);
            }
        }
        $this->activeConnections = [];
    }

    /**
     * enables exception throwing on detecting an erroneous dataset
     * @param bool $state [description]
     */
    public function setThrowExceptionOnErroneousData(bool $state = true): void
    {
        $this->throwExceptionOnErroneousData = $state;
    }

    /**
     * enable/disable the pipeline errorstack
     * @param bool $state [description]
     */
    public function setErrorstackEnabled(bool $state = true): void
    {
        $this->errorstackEnabled = $state;
    }

    /**
     * [getErrorstack description]
     * @return errorstack [description]
     */
    public function getErrorstack(): errorstack
    {
        return $this->errorstack;
    }

    /**
     * whether to skip erroneous processes
     * which means: if any of the target fails/runs into erroneous state
     * do not handle the whole source entry it originates from,
     * so neither of the targets gets to really STORE data for this entry.
     * @param bool $state [description]
     */
    public function setSkipErroneous(bool $state = true): void
    {
        $this->skipErroneous = $state;
    }

    /**
     * whether to skip erroneous processes
     * which means: if any of the target fails/runs into erroneous state
     * do not handle the whole source entry it originates from,
     * so neither of the targets gets to really STORE data for this entry.
     * @param bool $state [description]
     */
    public function setTrackErroneous(bool $state = true): void
    {
        $this->trackErroneous = $state;
    }

    /**
     * returns collected erroneous entries
     * @return array [description]
     */
    public function getErroneousEntries(): array
    {
        return $this->erroneousEntries;
    }

    /**
     * gets the dryRun state of this instance
     * @return bool [dry run state]
     */
    public function getDryRun(): bool
    {
        return $this->dryRun;
    }

    /**
     * enables or disables the dryRun option
     *
     * @param bool $dryRun [description]
     */
    public function setDryRun(bool $dryRun = true): void
    {
        $this->dryRun = $dryRun;
    }

    /**
     * [setTarget description]
     * @param string $name [description]
     * @param target $target [description]
     */
    public function setTarget(string $name, target $target): void
    {
        $this->target[$name] = $target;
    }

    /**
     * [getOption description]
     * @param string $key [description]
     * @return mixed        [description]
     */
    public function getOption(string $key): mixed
    {
        return $this->options?->get($key);
    }

    /**
     * returns the current pipeline specification
     * @return array
     * @throws ReflectionException
     * @throws exception
     */
    public function getSpecification(): array
    {
        $this->createTransforms();

        $spec = [];

        // loop through each target
        foreach ($this->config->get('target') as $targetName => $targetConfig) {
            $sources = [];
            // parse mappings
            foreach ($targetConfig['mapping'] as $mapName => $mapConfig) {
                $targetMappingName = "target.$targetName.$mapName";

                // sources - for mappings, this is either source, transform or erroneous (at the moment)
                $field = is_array($mapConfig['field']) ? implode('.', $mapConfig['field']) : $mapConfig['field'];
                $targetMappingSources = [
                  "{$mapConfig['type']}.$field",
                ];

                $spec[$targetMappingName] = [
                  'type' => 'target.mapping',
                  'source' => $targetMappingSources,
                ];
                $sources[] = $targetMappingName;
            }
            $spec["target.$targetName"] = [
              'type' => 'target',
              'source' => $sources,
            ];
        }

        // loop through each transform
        if ($this->config->exists('transform')) {
            foreach ($this->config->get('transform') as $transformName => $transformConfig) {
                $spec["transform.$transformName"] = $this->getTransformInstance($transformName)->getSpecification();
            }
        }

        foreach ($spec as $specItem) {
            foreach ($specItem['source'] as $specItemSource) {
                if (str_starts_with($specItemSource, 'source.')) {
                    $spec[$specItemSource] = [
                      'type' => 'source',
                    ];
                }
                if (str_starts_with($specItemSource, 'model.')) {
                    $spec[$specItemSource] = [
                      'type' => 'model',
                    ];
                }
            }
        }

        $spec['erroneous.erroneous'] = [
          'type' => 'erroneous',
        ];
        $spec['erroneous.data'] = [
          'type' => 'erroneous',
        ];

        return $spec;
    }

    /**
     * get a transform instance
     * by name
     * (has to be already initialized during ->run() )
     *
     * @param string $name [transform name from config]
     * @return transform       [transform instance]
     * @throws exception
     */
    public function getTransformInstance(string $name): transform
    {
        if (!isset($this->transforms[$name])) {
            throw new exception(self::EXCEPTION_CORE_IO_PIPELINE_GETTRANSFORM_NOTFOUND, exception::$ERRORLEVEL_ERROR, $name);
        }
        return $this->transforms[$name];
    }

    /**
     * [getTargetModel description]
     * @param string $targetName [description]
     * @param string $model [description]
     * @param string $app [description]
     * @param string $vendor [description]
     * @return model              [description]
     * @throws ReflectionException
     * @throws exception
     */
    protected function getTargetModel(string $targetName, string $model = '', string $app = '', string $vendor = ''): model
    {
        if (!isset($this->targetModelInstances[$targetName])) {
            $this->targetModelInstances[$targetName] = app::getModel($model, $app, $vendor);
        }
        return $this->targetModelInstances[$targetName];
    }
}
