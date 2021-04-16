<?php
namespace codename\core\io\tests\transform;

class containsTest extends abstractTransformTest
{

  /**
   * Testing transforms for Erors
   */
  public function testValueInvalid(): void {
    $transform = $this->getTransform('contains', [
      'collection'  => [ 'source' => 'source', 'field' => 'example_collection_source_field' ],
      'item'        => [ 'source' => 'source', 'field' => 'example_item_source_field' ],
    ]);
    $result = $transform->transform([
      'example_collection_source_field' => [
        'test',
        'test1',
        'test2',
      ],
      'example_item_source_field'       => 'test3'
    ]);
    // Make sure it stays an array
    $this->assertFalse($result);
  }

  /**
   * Testing transforms for Erors
   */
  public function testValueValid(): void {
    $transform = $this->getTransform('contains', [
      'collection'  => [ 'source' => 'source', 'field' => 'example_collection_source_field' ],
      'item'        => [ 'source' => 'source', 'field' => 'example_item_source_field' ],
    ]);
    $result = $transform->transform([
      'example_collection_source_field' => [
        'test',
        'test1',
        'test2',
      ],
      'example_item_source_field'       => 'test1'
    ]);
    // Make sure it stays an array
    $this->assertTrue($result);
  }

  /**
   * Testing transforms for Erors
   */
  public function testValueNotSourcFields(): void {
    $this->expectException(\codename\core\exception::class);

    $transform = $this->getTransform('contains', [
      'collection'  => 'example_collection_source_field',
      'item'        => 'example_item_source_field',
    ]);
    $result = $transform->transform([]);
  }

  /**
   * Testing transforms for Erors
   */
  public function testValueCollectionNotAArray(): void {
    $transform = $this->getTransform('contains', [
      'collection'  => [ 'source' => 'source', 'field' => 'example_collection_source_field' ],
      'item'        => 'example_item_source_field',
    ]);
    $result = $transform->transform([
      'example_collection_source_field' => 'test',
    ]);
    $this->assertNull($result);
  }

  /**
   * Test Spec output (simple case)
   */
  public function testSpecification(): void {
    $transform = $this->getTransform('contains', [
      'collection'  => [ 'source' => 'source', 'field' => 'example_collection_source_field' ],
      'item'        => [ 'source' => 'source', 'field' => 'example_item_source_field' ],
    ]);
    $this->assertEquals(
      [
        'type'    => 'transform',
        'source'  => [ 'source.example_item_source_field', 'source.example_collection_source_field' ]
      ],
      $transform->getSpecification()
    );
  }

}
