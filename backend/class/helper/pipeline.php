<?php
namespace codename\core\io\helper;

use codename\core\model;
use codename\core\config;

/**
 * pipeline helper functions
 */
class pipeline
{
  /**
   * [createModelToModelPipelineConfig description]
   * @param model         $model [description]
   * @param string        $targetType
   * @return config
   */
  public static function createModelToModelPipelineConfig(model $model, string $targetType, array $includeJoins = []) : config {

    $config = [
      'info' => [
        'name'        => 'generated',
        'description' => null
      ],
      'source' => [
        'type' => 'model',
        'query' => []
      ],
      'transform' => [],
      'target' => [
      ],
    ];

    $targetName = 'generated_target_'.$targetType;
    $config['target'][$targetName] = [
      'type'  => $targetType,
    ];

    if($targetType === 'buffered_file_parquet') {
      // parquet optimizations
      $config['target'][$targetName]['buffer'] = true;
      $config['target'][$targetName]['buffer_size'] = 10000;

      $config['target'][$targetName]['compression'] = 'gzip';
    }

    // use by-ref
    $target = &$config['target']['generated_target_'.$targetType];


    $fieldlist = [];

    $recursiveModels = static::getRecursiveModelList($model);

    foreach($recursiveModels as $index => $currentModel) {
      foreach($currentModel->getFields() as $field) {

        if($currentModel->config->get('datatype>'.$field) === 'virtual') {
          // Skip virtual fields for now.
          continue;
        }

        if(in_array($field, $fieldlist)) {
          // duplicate name in fieldlist - skip
          continue;
        }

        $fieldlist[] = $field;


        // base: source == target field (name)
        $mappingEntry = [
          'type'    => 'source',
          'field'   => $field,
        ];

        if($targetType == 'buffered_file_parquet') {
          $transforms = [];
          $params = static::convertModelfieldToParquetEquivalentParams($currentModel, $field, $mappingEntry, $transforms);

          // prevent joined models' PKEY
          // to be required, if there's NULL ref
          if($index > 0) {
            if($field == $currentModel->getPrimarykey()) {
              $params['is_nullable'] = true; // ?
            }
          }

          if(count($transforms) > 0) {
            $config['transform'] = array_merge($config['transform'], $transforms);
          }
          $mappingEntry = array_merge($mappingEntry, $params);
        }

        $target['mapping'][$field] = $mappingEntry;
      }
    }

    // foreach($model->getFields() as $field) {
    //
    //   if($model->config->get('datatype>'.$field) === 'virtual') {
    //     // Skip virtual fields for now.
    //     continue;
    //   }
    //
    //   if(in_array($field, $fieldlist)) {
    //     // duplicate name in fieldlist?
    //   }
    //
    //
    //
    //   // base: source == target field (name)
    //   $mappingEntry = [
    //     'type'    => 'source',
    //     'field'   => $field,
    //   ];
    //
    //   if($targetType == 'buffered_file_parquet') {
    //     $transforms = [];
    //     $params = static::convertModelfieldToParquetEquivalentParams($model, $field, $mappingEntry, $transforms);
    //
    //     if(count($transforms) > 0) {
    //       $config['transform'] = array_merge($config['transform'], $transforms);
    //     }
    //     $mappingEntry = array_merge($mappingEntry, $params);
    //   }
    //
    //   $target['mapping'][$field] = $mappingEntry;
    // }

    return new \codename\core\config($config);
  }

  /**
   * [getRecursiveModelList description]
   * @param  model  $model [description]
   * @return model[]
   */
  protected static function getRecursiveModelList(model $model): array {
    $result = [ $model ];
    foreach($model->getNestedJoins() as $join) {
      $nested = static::getRecursiveModelList($join->model);
      $result = array_merge($result, $nested);
    }
    return $result;
  }

  /**
   * [convertModelfieldToParquetEquivalentParams description]
   * @param  model  $model [description]
   * @param  string $field [description]
   * @return array         [description]
   */
  protected static function convertModelfieldToParquetEquivalentParams(model $model, string $field, array &$mappingEntry, array &$transforms): array {
    $options = $model->config->get('options>'.$field);
    $datatype = $model->config->get('datatype>'.$field);

    $isPkey = in_array($field, $model->config->get('primary'));

    $isNullable = !$isPkey; // PKEYs are not nullable by default

    if($isNullable) {
      // explicit (not-)nullable state
      $isNullable = !in_array($field, $model->config->get('not_null') ?? []);
    }

    // Additional implicit required => not null. Might be overkill.
    // if($isNullable) {
    //   $isNullable = !in_array($field, $model->config->get('required') ?? []);
    // }

    $phpType = null;
    $phpClass = null;
    $precision = null;
    $scale = null;
    $typeLength = null;
    $logicalType = null;

    $returnval = [
    ];

    switch($datatype) {
      case 'structure':
        // highly dependent on usage...
        // F.e. if FKEY to model, it's the Foreign model's pkey type
        // Otherwise: JSON data
        // Which might be mapped otherwise.
        // For now: fallback to string.

        // TODO: prefixes, if recursion?
        $transformField = $field.'_to_json';
        $transforms[$transformField] = [
          'type'    => 'convert_json',
          'config'  => [
            'source'  => 'source',
            'field'   => $field,
            'mode'    => 'encode',
          ]
        ];
        $logicalType = 'JSON'; // ??
        $mappingEntry['type'] = 'transform';
        $mappingEntry['field'] = $transformField;

        $returnval['php_type'] = 'string';
        break;
      case 'text':
        // check length?
        $returnval['php_type'] = 'string';
        break;
      case 'number_natural':
        // Int32, 64, 96 or arbitrary?
        $returnval['php_type'] = 'integer';
        break;
      case 'text_timestamp':
        $returnval['php_type'] = 'object';
        $returnval['php_class'] = \DateTimeImmutable::class;
        $returnval['datetime_format'] = \jocoon\parquet\data\DateTimeFormat::DateAndTime;

        // TODO: prefixes, if recursion?
        $transformField = $field.'_to_dti';
        $transforms[$transformField] = [
          'type'    => 'convert_datetime',
          'config'  => [
            'source'  => 'source',
            'field'   => $field,
            'source_format' => 'Y-m-d H:i:s',
            'target_format' => 'DateTimeImmutable',
          ]
        ];
        $mappingEntry['type'] = 'transform';
        $mappingEntry['field'] = $transformField;
        // TODO: set Datetime Format (parquet)
        break;
      case 'text_date':
        $returnval['php_type'] = 'object';
        $returnval['php_class'] = \DateTimeImmutable::class;
        $returnval['datetime_format'] = \jocoon\parquet\data\DateTimeFormat::Date;
        // TODO extra data

        // TODO: prefixes, if recursion?
        $transformField = $field.'_to_dti';
        $transforms[$transformField] = [
          'type'    => 'convert_datetime',
          'config'  => [
            'source'  => 'source',
            'field'   => $field,
            'source_format' => 'Y-m-d',
            'target_format' => 'DateTimeImmutable',
          ]
        ];
        // TODO: set Datetime Format (parquet)
        $mappingEntry['type'] = 'transform';
        $mappingEntry['field'] = $transformField;
        break;
      case 'number':
        // Float, Double or Decimal
        $returnval['php_type'] = 'double';
        // $phpType = 'decimal'; // pseudo-type
        // $precision = $options['length'];
        // $scale = $options['precision'];
        break;
      case 'boolean':
        $returnval['php_type'] = 'boolean';
        break;
    }


    $returnval['is_nullable'] = $isNullable;
    // $returnval = [
    //   'php_type'    => $phpType,
    //   'php_class'   => $phpClass,
    //   'precision'   => $precision,
    //   'scale'       => $scale,
    //   'type_length' => $typeLength,
    //   'is_nullable' => $isNullable,
    // ];

    // filter, additionally, to omit all empty/null values
    return array_filter($returnval, function($v) { return $v !== null; });
  }
}
