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
    parent::validate($value);

    if(count($this->getErrors()) == 0) {
      if(count($sourceErrors = (new import\source)->validate($value['source'])) > 0) {
        $this->errorstack->addErrors($sourceErrors);
      }

      if(!empty($value['transform'])) {
        if(count($transformErrors = (new import\transform)->validate($value['transform'])) > 0) {
          $this->errorstack->addErrors($transformErrors);
        }
      }

      if(count($targetErrors = (new import\target)->validate($value['target'])) > 0) {
        $this->errorstack->addErrors($targetErrors);
      }
    }
    // TODO: Detail validation!

    return $this->getErrors();
  }
}