<?php
namespace codename\core\io\validator\structure\config;
use \codename\core\app;

/**
 * Validating import definitions
 */
class import extends \codename\core\validator\structure\config {

  /**
   * Contains a list of array keys that MUST exist in the validated array
   * @var array
   */
  public $arrKeys = array(
    'source',
    'target'
  );

  /**
   * @inheritDoc
   */
  public function validate($value): array
  {
    if(count(parent::validate($value)) != 0) {
        return $this->errorstack->getErrors();
    }

    if(count($sourceErrors = (new import\source)->reset()->validate($value['source'])) > 0) {
      $this->errorstack->addErrors($sourceErrors);
      return $this->errorstack->getErrors();
    }

    if(!empty($value['transform'])) {
      if(count($transformErrors = (new import\transform)->reset()->validate($value['transform'])) > 0) {
        $this->errorstack->addErrors($transformErrors);
        return $this->errorstack->getErrors();
      }
    }

    if(count($targetErrors = (new import\target)->reset()->validate($value['target'])) > 0) {
      $this->errorstack->addErrors($targetErrors);
      return $this->errorstack->getErrors();
    }
    // TODO: Detail validation!

    return $this->getErrors();
  }
}
