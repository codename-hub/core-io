<?php
namespace codename\core\io\process;

use codename\core\app;

/**
 * [target description]
 */
abstract class target extends \codename\core\io\process {

  /**
   * [getTarget description]
   * @return \codename\core\io\target [description]
   */
  protected function getTarget() : \codename\core\io\target {
    return $this->getPipelineInstance()->getTarget($this->config['target']);
  }

  /**
   * @inheritDoc
   */
  public function getSpecification(): array
  {
    throw new \LogicException('Not implemented'); // TODO
  }
}
