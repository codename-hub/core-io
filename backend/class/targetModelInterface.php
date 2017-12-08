<?php
namespace codename\core\io;

/**
 * defines an interface to access an underlying model instance
 */
interface targetModelInterface {
  function getModel() : \codename\core\model;
}
