<?php
namespace codename\core\io\tests\transform;

class explodeTest extends abstractTransformTest
{
  /**
   * Tests for a default delimiter (,)
   */
  public function testDefaultDelimiter(): void {
    $transform = $this->getTransform('explode', [
      'source'  => 'source',
      'field'   => 'example_source_field'
    ]);
    $result = $transform->transform([
      'example_source_field' => 'value1,value2'
    ]);
    $this->assertEquals(['value1','value2'], $result);
  }

  /**
   * Tests a delimiter that is not the default one
   */
  public function testCustomDelimiter(): void {
    $transform = $this->getTransform('explode', [
      'delimiter' => ';',
      'source'  => 'source',
      'field'   => 'example_source_field'
    ]);
    $result = $transform->transform([
      'example_source_field' => 'value1;value2'
    ]);
    $this->assertEquals(['value1','value2'], $result);
  }

  /**
   * Tests explode behaviour, if there is no delimiter match
   */
  public function testNonexistingDelimiter(): void {
    $transform = $this->getTransform('explode', [
      'delimiter' => ';',
      'source'  => 'source',
      'field'   => 'example_source_field'
    ]);
    $result = $transform->transform([
      'example_source_field' => 'value1-value2'
    ]);
    // Make sure it stays an array
    $this->assertEquals(['value1-value2'], $result);
  }

  /**
   * Tests a multichar separator/delimiter
   */
  public function testMulticharDelimiter(): void {
    $transform = $this->getTransform('explode', [
      'delimiter' => ';,',
      'source'  => 'source',
      'field'   => 'example_source_field'
    ]);
    $result = $transform->transform([
      'example_source_field' => 'value1;,value2'
    ]);
    // Make sure it stays an array
    $this->assertEquals(['value1','value2'], $result);
  }

  /**
   * Tests max. amount of exploded items
   */
  public function testLimit(): void {
    $transform = $this->getTransform('explode', [
      'delimiter' => ';',
      'source'  => 'source',
      'field'   => 'example_source_field',
      'limit'   => 3
    ]);
    $result = $transform->transform([
      'example_source_field' => 'value1;value2;value3;value4'
    ]);
    // Make sure it stays an array
    $this->assertEquals(['value1','value2','value3;value4'], $result);
  }

  /**
   * [testDelimiterDynamic description]
   */
  public function testDelimiterDynamic(): void {
    $transform = $this->getTransform('explode', [
      'delimiter' => [
        'source'  => 'source',
        'field'   => 'example_delimiter',
      ],
      'source'  => 'source',
      'field'   => 'example_source_field'
    ]);
    $result = $transform->transform([
      'example_delimiter'     => '#',
      'example_source_field'  => 'value1#value2'
    ]);
    // Make sure it stays an array
    $this->assertEquals(['value1','value2'], $result);
  }

  /**
   * Test Spec output (simple case)
   */
  public function testSpecification(): void {
    $transform = $this->getTransform('explode', [
      'source'  => 'source',
      'field'   => 'example_source_field'
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
