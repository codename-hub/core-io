<?php
namespace codename\core\io\tests\transform\get;

class strreplaceTest extends \codename\core\io\tests\transform\abstractTransformTest
{

  /**
   * Testing transforms for Erors
   */
  public function testValueValidCase1(): void {
    $transform = $this->getTransform('get_strreplace', [
      'source'  => 'source',
      'field'   => 'example_source_field',
      'search'  => [
        'source'  => 'source',
        'field'   => 'example_search_field',
      ],
      'replace' => [
        'source'  => 'source',
        'field'   => 'example_replace_field',
      ],
    ]);
    $result = $transform->transform([
      'example_source_field'  => 'AbCdEfGhIjKlMnOpQrStUvWxYz',
      'example_search_field'  => 'KlM',
      'example_replace_field' => 'klm',
    ]);

    // Make sure it stays an array
    $this->assertEquals('AbCdEfGhIjklmnOpQrStUvWxYz', $result );
  }

  /**
   * Testing transforms for Erors
   */
  public function testValueValidCase2(): void {
    $transform = $this->getTransform('get_strreplace', [
      'source'            => 'source',
      'field'             => 'example_source_field',
      'search'            => 'KlM',
      'replace'           => 'klm',
      'case_insensitive'  => true,
    ]);
    $result = $transform->transform([
      'example_source_field'  => 'AbCdEfGhIjKlMnOpQrStUvWxYz',
    ]);

    // Make sure it stays an array
    $this->assertEquals('AbCdEfGhIjklmnOpQrStUvWxYz', $result );
  }


  /**
   * Test Spec output (simple case)
   */
  public function testSpecification(): void {
    $transform = $this->getTransform('get_strreplace', [
      'source'  => 'source',
      'field'   => 'example_source_field',
      'mode'    => 'lower',
    ]);
    $this->assertEquals(
      [
        'type'    => 'transform',
        'source'  => [ 'source.example_source_field' ]
      ],
      $transform->getSpecification()
    );
  }

}
