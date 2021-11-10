<?php
namespace codename\core\io\target\buffered\file;

use codename\core\exception;

use codename\parquet\ParquetWriter;
use codename\parquet\CompressionMethod;

use codename\parquet\data\Schema;
use codename\parquet\data\DataField;
use codename\parquet\data\DataColumn;
use codename\parquet\data\DateTimeDataField;
use codename\parquet\data\DecimalDataField;

class parquet extends \codename\core\io\target\buffered\file
{
  /**
   * @inheritDoc
   */
  public function __construct(string $name, array $config)
  {
    parent::__construct($name, $config);

    $this->bufferEnabled = $config['buffer'] ?? false;
    $this->bufferSize = $config['buffer_size'] ?? null;

    if($c = $config['compression'] ?? null) {
      switch($c) {
        case 'none':
          $this->compression = CompressionMethod::None;
          break;
        case 'gzip':
          $this->compression = CompressionMethod::Gzip;
          break;
        case 'snappy':
          $this->compression = CompressionMethod::Snappy;
          break;
        default:
          throw new exception('Invalid compression method');
      }
    }

  }

  /**
   * [protected description]
   * @var int
   */
  protected $compression = null;

  /**
   * [protected description]
   * @var bool
   */
  protected $bufferEnabled = true;

  /**
   * [protected description]
   * @var int
   */
  protected $bufferSize = null;

  // /**
  //  * @inheritDoc
  //  */
  // public function store(array $data, ?array $tags = null): bool
  // {
  //   print_r($data);
  //   return parent::store($data, $tags);
  // }

  /**
   * @inheritDoc
   */
  public function store(array $data, ?array $tags = null): bool
  {
    $success = parent::store($data, $tags);

    if($this->bufferEnabled && $this->bufferSize && \count($this->bufferArray) >= $this->bufferSize) {
      // immediate write-out and buffer clear
      $this->writeData($this->bufferArray);
      $this->bufferArray = [];
    }

    return $success && true;
  }

  /**
   * @inheritDoc
   */
  protected function storeBufferedData()
  {

    // $tagsChunks = [ $this->tagsArray ];

    // No splitting allowed for this format. At the moment.

    // $dataChunks = [];
    // $tagsChunks = [];
    //
    // if($this->splitCount && (count($this->bufferArray) > $this->splitCount)) {
    //   // we have to split at least one time
    //   $dataChunks = array_chunk($this->bufferArray, $this->splitCount);
    //   $tagsChunks = array_chunk($this->tagsArray, $this->splitCount);
    //
    // } else {
    //   $dataChunks = [ $this->bufferArray ];
    //   $tagsChunks = [ $this->tagsArray ];
    // }

    $resultObjects = [];

    // foreach ($dataChunks as $index => $dataChunk) {
    //
    //   // skip empty chunks
    //   if(count($dataChunk) === 0) {
    //     continue;
    //   }
    //
    //   $tagsChunk = $tagsChunks[$index];
    //
    //   // create a new file handle?
    //   $handle = null;
    //
    //   $path = $this->getNewFilePath();
    //   $handle = $this->getNewFileHandle($path);
    //
    //   $this->internalStoreBufferedData($handle, $dataChunk);
    //
    //   if($tagsChunk) {
    //     $resultObjects[] = new \codename\core\io\value\text\fileabsolute\tagged($path, $tagsChunk);
    //   } else {
    //     $resultObjects[] = new \codename\core\value\text\fileabsolute($path, $tagsChunk);
    //   }
    // }


    // $tagsChunk = $tagsChunks[0];

    $path = $this->getCurrentFilePath();
    $handle = $this->getCurrentFileHandle();

    // check for pending writes
    if(count($this->bufferArray) > 0) {
      $this->writeData($this->bufferArray);
    }

    // finish main writer
    $writer = $this->getParquetWriter();
    $writer->finish();

    fclose($handle);


    // if($tagsChunk) {
    //   $resultObjects[] = new \codename\core\io\value\text\fileabsolute\tagged($path, $tagsChunk);
    // } else {
    $resultObjects[] = new \codename\core\value\text\fileabsolute($path);
    // }

    $this->fileResults = $resultObjects;
  }

  /**
   * Gets the current (temporary?) file HANDLE to work on
   * or creates a new one
   * @return resource
   */
  protected function getCurrentFileHandle() {
    if($this->currentFileHandle === null) {
      $path = $this->getCurrentFilePath();
      $this->currentFileHandle = $this->getNewFileHandle($path);
    }
    return $this->currentFileHandle;
  }

  /**
   * Gets the current (temporary?) file path to work on
   * or creates/reserves a new one
   * @return string [description]
   */
  protected function getCurrentFilePath(): string {
    if($this->currentFilePath === null) {
      $this->currentFilePath = $this->getNewFilePath();
    }
    return $this->currentFilePath;
  }

  /**
   * [protected description]
   * @var string
   */
  protected $currentFilePath = null;

  /**
   * [protected description]
   * @var resource
   */
  protected $currentFileHandle = null;

  /**
   * [getParquetWriter description]
   * @return ParquetWriter [description]
   */
  protected function getParquetWriter() : ParquetWriter {
    if($this->parquetWriter === null) {

      // create a new writer instance
      $handle = $this->getCurrentFileHandle();


      $mapping = $this->getMapping();

      $fields = [];

      foreach($mapping as $key => $config) {
        $dataField = null;
        $isNullable = $config['is_nullable'] ?? false;

        if($config['php_class'] ?? false) {
          if($config['php_class'] === \DateTimeImmutable::class) {
            $dataField = DateTimeDataField::create($key, $config['datetime_format'], $config['is_nullable'], false);
          } else {
            $dataField = DataField::createFromType($key, $config['php_class']);
          }
        } else if($config['php_type'] ?? false) {
          if($config['php_type'] === 'decimal') {
            $dataField = DecimalDataField::create($key, $config['precision'], $config['scale'], false, $config['is_nullable']);
          } else {
            $dataField = DataField::createFromType($key, $config['php_type']);
          }
        } else {
          // guess by first X values, as long as we have some?
          $values = array_column($this->bufferArray, $key);
          $typesFound = [];
          $classesFound = [];

          foreach($values as $index => $value) {

            // only break out > 100 items, if we have already found ANYTHING
            if(count($typesFound) > 0) {
              if($index > 100) break;
            }

            if($value === null) {
              $isNullable = true;
              continue;
            }

            $type = gettype($value);
            $typesFound[] = $type;
            if($type === 'object') {
              $classesFound[] = get_class($value);
            }
          }

          $uniqueTypes = array_values(array_unique($typesFound));

          if(count($uniqueTypes) > 1) {
            // invalid type spec by guessing
            throw new exception('NOT UNIQUE TYPES', exception::$ERRORLEVEL_ERROR, $uniqueTypes); ;
          } else if($uniqueTypes[0] === 'object') {
            // we might have to guess class?

            $uniqueClasses = array_values(array_unique($classesFound));
            if($uniqueClasses[0] === \DateTimeImmutable::class) {
              $dataField = DataField::createFromType($key, \DateTimeImmutable::class);
            } else {
              // TODO: check count/other entries
              throw new exception('UNSUPPORTED TYPE/CLASS', exception::$ERRORLEVEL_ERROR); ;
            }
          } else {

            // print_r($uniqueTypes);

            $dataField = DataField::createFromType($key, $uniqueTypes[0]);
          }
        }
        $dataField->hasNulls = $isNullable;
        $fields[] = $dataField;
      }

      // create schema
      $schema = new Schema($fields);
      // TODO: formatOptions + append mode?
      $this->parquetWriter = new ParquetWriter($schema, $handle);

      // Apply compression method, if defined (!== null, NOTE: there might be (int)0 as value)
      if($this->compression !== null) {
        $this->parquetWriter->compressionMethod = $this->compression;
      }
    }
    return $this->parquetWriter;
  }

  /**
   * current ParquetWriter instance
   * @var ParquetWriter
   */
  protected $parquetWriter = null;

  /**
   * write a row group/data chunk
   * @param  array  $data [description]
   * @return [type]       [description]
   */
  protected function writeData(array $data) {
    $writer = $this->getParquetWriter();
    $rgr = $writer->CreateRowGroup();
    // column-based
    foreach($writer->schema->getDataFields() as $field) {
      $column = new DataColumn($field, array_column($data, $field->name));
      $rgr->WriteColumn($column);
    }
    $rgr->finish();
  }


  // /**
  //  * @inheritDoc
  //  */
  // protected function internalStoreBufferedData($handle, $bufferArray)
  // {
  //   // $mapping = $this->getMapping();
  //   //
  //   // $fields = [];
  //   //
  //   // foreach($mapping as $key => $config) {
  //   //   $dataField = null;
  //   //   $isNullable = $config['is_nullable'] ?? false;
  //   //
  //   //   if($config['php_class'] ?? false) {
  //   //     if($config['php_class'] === \DateTimeImmutable::class) {
  //   //       $dataField = DateTimeDataField::create($key, $config['datetime_format'], $config['is_nullable'], false);
  //   //     } else {
  //   //       $dataField = DataField::createFromType($key, $config['php_class']);
  //   //     }
  //   //   } else if($config['php_type'] ?? false) {
  //   //     if($config['php_type'] === 'decimal') {
  //   //       $dataField = DecimalDataField::create($key, $config['precision'], $config['scale'], false, $config['is_nullable']);
  //   //     } else {
  //   //       $dataField = DataField::createFromType($key, $config['php_type']);
  //   //     }
  //   //   } else {
  //   //     // guess by first X values
  //   //     $values = array_column($bufferArray, $key);
  //   //     $typesFound = [];
  //   //     $classesFound = [];
  //   //
  //   //     foreach($values as $index => $value) {
  //   //
  //   //       // only break out > 100 items, if we have already found ANYTHING
  //   //       if(count($typesFound) > 0) {
  //   //         if($index > 100) break;
  //   //       }
  //   //
  //   //       if($value === null) {
  //   //         $isNullable = true;
  //   //         continue;
  //   //       }
  //   //
  //   //       $type = gettype($value);
  //   //       $typesFound[] = $type;
  //   //       if($type === 'object') {
  //   //         $classesFound[] = get_class($value);
  //   //       }
  //   //     }
  //   //
  //   //     $uniqueTypes = array_values(array_unique($typesFound));
  //   //
  //   //     if(count($uniqueTypes) > 1) {
  //   //       // invalid type spec by guessing
  //   //       throw new exception('NOT UNIQUE TYPES', exception::$ERRORLEVEL_ERROR, $uniqueTypes); ;
  //   //     } else if($uniqueTypes[0] === 'object') {
  //   //       // we might have to guess class?
  //   //
  //   //       $uniqueClasses = array_values(array_unique($classesFound));
  //   //       if($uniqueClasses[0] === \DateTimeImmutable::class) {
  //   //         $dataField = DataField::createFromType($key, \DateTimeImmutable::class);
  //   //       } else {
  //   //         // TODO: check count/other entries
  //   //         throw new exception('UNSUPPORTED TYPE/CLASS', exception::$ERRORLEVEL_ERROR); ;
  //   //       }
  //   //     } else {
  //   //
  //   //       // print_r($uniqueTypes);
  //   //
  //   //       $dataField = DataField::createFromType($key, $uniqueTypes[0]);
  //   //     }
  //   //   }
  //   //   $dataField->hasNulls = $isNullable;
  //   //   $fields[] = $dataField;
  //   // }
  //   //
  //   // // create schema
  //   // $schema = new Schema($fields);
  //   // // TODO: formatOptions + append mode?
  //   // $writer = new ParquetWriter($schema, $handle);
  //
  //   // finish writer?
  //   $writer = $this->getParquetWriter();
  //   $rgr = $writer->CreateRowGroup();
  //   // column-based
  //
  //   foreach($writer->schema->getDataFields() as $field) {
  //     $column = new DataColumn($field, array_column($bufferArray, $field->name));
  //     $rgr->WriteColumn($column);
  //   }
  //
  //   $rgr->finish();
  //   $writer->finish();
  //
  //   // foreach($bufferArray as $buffered) {
  //   //   $data = [];
  //   //   foreach ($this->structure as $rowIndex => $rowConfig) {
  //   //     $data[$rowIndex] = [];
  //   //     foreach($rowConfig as $columnIndex => $mappingKey) {
  //   //       // $value = $value === null ? '' : $value; ??
  //   //       $value = $buffered[$mappingKey];
  //   //       $length = $mapping[$mappingKey]['length'];
  //   //       // TODO:
  //   //       // please change if there is an official version of php lib
  //   //       // https://bugs.php.net/bug.php?id=21317
  //   //       $diff = strlen($value) - mb_strlen($value, 'UTF-8');
  //   //       $value = str_pad($value, $length + $diff, $this->paddingString, $this->paddingMode);
  //   //       if($this->truncate) {
  //   //         $value = mb_substr($value, 0, $length, 'UTF-8');
  //   //       } else {
  //   //         if(mb_strlen($value, 'UTF-8') > $length) {
  //   //           throw new exception();
  //   //         }
  //   //       }
  //   //       $data[$rowIndex][$columnIndex] = $value;
  //   //     }
  //   //     // Add line terminator?
  //   //   }
  //   //
  //   //   $rows = [];
  //   //   foreach($data as $columns) {
  //   //     $rows[] = implode('', $columns);
  //   //   }
  //   //
  //   //   $entry = implode(chr(10), $rows);
  //   //
  //   //   // TODO: add line/final terminating CRLF?
  //   //   $entry .= chr(10);
  //   //
  //   //   if($this->encoding) {
  //   //     $entry = mb_convert_encoding($entry, $this->encoding, 'UTF-8');
  //   //   }
  //   //   $success = fputs($handle, $entry);
  //   // }
  //
  //   fclose($handle);
  // }
}
