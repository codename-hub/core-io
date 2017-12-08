<?php
namespace codename\core\io;

/**
 * [interface description]
 * @var [type]
 */
interface targetStoreTagInterface
{
  /**
   * [store description]
   * @param  array        $data [description]
   * @param  array|null   $tags [description]
   * @return bool         [description]
   */
  function store(array $data, ?array $tags = null) : bool;
  
}
