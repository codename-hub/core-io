<?php
namespace codename\core\io\validator\structure\config\import;

/**
 * Validating import definitions
 */
class transform extends \codename\core\validator\structure\config {

  /**
   * Contains a list of array keys that MUST exist in the validated array
   * @var array
   */
  public $arrKeys = array(
  );

  /**
   * @inheritDoc
   */
  public function validate($value): array
  {
    if(count(parent::validate($value)) != 0) {
        return $this->errorstack->getErrors();
    }


    return $this->getErrors();
  }
}
