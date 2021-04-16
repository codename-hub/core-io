<?php
namespace codename\core\io\tests\validator\structure\config\import;

/**
 * I will test the transform validator
 * @package codename\core
 * @since 2016-11-02
 */
class transform extends \codename\core\io\tests\validator {

    /**
     * simple non-text value test
     * @return void
     */
    public function testValueNotAArray() {
      $this->assertEquals('VALIDATION.VALUE_NOT_A_ARRAY', $this->getValidator()->validate('')[0]['__CODE'] );
    }

    /**
     * Testing validators for Erors
     * @return void
     */
    public function testValueMissingArrKeys() {
        $this->assertEmpty($this->getValidator()->validate([]));
    }

}
