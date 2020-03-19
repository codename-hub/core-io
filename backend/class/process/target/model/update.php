<?php
namespace codename\core\io\process\target\model;

use codename\core\app;
use codename\core\exception;

/**
 * [update description]
 */
class update extends \codename\core\io\process\target\model {

  /**
   * @inheritDoc
   */
  public function run()
  {
    $model = $this->getModel();
    // apply filters
    foreach($this->config['filter'] as $filter) {
      if($filter['value'] && isset($filter['value']['source'])) {
        if($filter['value']['source'] == 'transform') {
          $useValue = $this->getPipelineInstance()->getTransformInstance($filter['value']['field'])->transform(null);
        } else {
          throw new exception('EXCEPTION_CORE_IO_PROCESS_TARGET_MODEL_UPDATE_UNSUPPORTED_SOURCE', exception::$ERRORLEVEL_FATAL, $filter);
        }
      } else {
        $useValue = $filter['value'];
      }
      $model->addFilter($filter['field'], $useValue, $filter['operator']);
    }

    $data = [];
    foreach($this->config['data'] as $field => $dataEntry) {
      if(is_array($dataEntry)) {
        if($dataEntry['source'] == 'option') {
          $data[$field] = $this->getPipelineInstance()->getOption($dataEntry['field']);
        } elseif($dataEntry['source'] == 'transform') {
          $data[$field] = $this->getPipelineInstance()->getTransformInstance($dataEntry['field'])->transform(null);
        } else {
          throw new exception('EXCEPTION_CORE_IO_PROCESS_TARGET_MODEL_UPDATE_UNSUPPORTED_SOURCE', exception::$ERRORLEVEL_FATAL, [ $field, $dataEntry ]);
        }
      } else {
        $data[$field] = $dataEntry;
      }
    }

    // perform update
    if(!$this->getPipelineInstance()->getDryRun()) {
      $model->update($data);
    } else {
      // do nothing
      app::getLog('devlog')->debug("target_model_update: " . print_r($data, true));
      // app::getResponse()->setData('debug_process', $useValue);
    }
  }
}
