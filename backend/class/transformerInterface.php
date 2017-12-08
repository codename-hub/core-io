<?php
namespace codename\core\io;

interface transformerInterface {
  
  /**
   * [getTransformInstance description]
   * @param  string                      $name [description]
   * @return \codename\core\io\transform       [description]
   */
  function getTransformInstance(string $name) : \codename\core\io\transform;
}
