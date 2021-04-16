<?php
namespace codename\core\io\tests\validator;
/**
 * base class for structure validators
 */
class structure extends \codename\core\io\tests\validator {

  /**
   * simple non-text value test
   * @return void
   */
  public function testValueNotAArray() {
    $this->assertEquals('VALIDATION.VALUE_NOT_A_ARRAY', $this->getValidator()->validate('')[0]['__CODE'] );
  }

  /**
   * simple non-text value test
   * @return void
   */
  public function testValueIsNull() {
    $this->assertEmpty($this->getValidator()->validate(null));
  }

  /**
   * simple non-text value test
   * @return void
   */
  public function testValueIsNullNotAllowed() {
    $validator = new \codename\core\validator\structure(false);
    $errors = $validator->validate(null);

    $this->assertNotEmpty($errors);
    $this->assertCount(1, $errors);
    $this->assertEquals('VALIDATION.VALUE_IS_NULL', $errors[0]['__CODE']);
  }

  /**
   * simple non-text value test
   * @return void
   */
  public function testValueIsValid() {
    $validator = new \codename\core\validator\structure();
    $this->assertTrue($validator->isValid(null));
  }

}
