<?php
namespace codename\core\io\tests\validator\structure\config;

/**
 * I will test the import validator
 * @package codename\core
 * @since 2016-11-02
 */
class import extends \codename\core\io\tests\validator\structure {

    /**
     * Testing validators for Erors
     * @return void
     */
    public function testValueMissingArrKeys() {
        $errors = $this->getValidator()->validate([]);

        $this->assertNotEmpty($errors);
        $this->assertCount(2, $errors);
        $this->assertEquals('VALIDATION.ARRAY_MISSING_KEY', $errors[0]['__CODE'] );
        $this->assertEquals('VALIDATION.ARRAY_MISSING_KEY', $errors[1]['__CODE'] );
    }

    /**
     * Testing validators for Erors
     * @return void
     */
    public function testValueInvalidKeySource() {
      $config = [
        'source'  => '',
        'target'  => [],
      ];
      $this->assertNotEmpty($this->getValidator()->validate($config));
    }

    /**
     * Testing validators for Erors
     * @return void
     */
    public function testValueInvalidKeyTarget() {
      $config = [
        'source'  => [],
        'target'  => '',
      ];
      $this->assertNotEmpty($this->getValidator()->validate($config));
    }

    /**
     * Testing validators for Erors
     * @return void
     */
    public function testValueInvalidKeyTransform() {
      $config = [
        'source'    => [],
        'target'    => [],
        'transform' => 'example',
      ];
      $this->assertNotEmpty($this->getValidator()->validate($config));
    }

    /**
     * Testing validators for Erors
     * @return void
     */
    public function testValueValid() {
        $config = [
          'source'  => [],
          'target'  => [],
        ];
        $this->assertEmpty($this->getValidator()->validate($config));
    }

}
