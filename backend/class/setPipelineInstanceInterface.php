<?php
namespace codename\core\io;

/**
 * an interface  to enable
 * ->setPipelineInstance() on an object
 */
interface setPipelineInstanceInterface {

  /**
   * sets the corresponding pipeline instance
   * 
   * @param  \codename\core\io\pipeline $instance [description]
   */
  public function setPipelineInstance(\codename\core\io\pipeline $instance);

}
