<?php
namespace codename\core\io\transform;

/**
 * getter for deep-accessing array elements
 */
class deepaccess extends \codename\core\io\transform
{
  /**
   * accessor/structure dive
   * [ key, subkey, subsubkey, finalkey ]
   * @var array
   */
  protected $path = null;

  /**
   * @inheritDoc
   */
  public function __construct(array $config)
  {
    parent::__construct($config);

    if(is_array($config['path'])) {
      $this->path = $config['path'];
    } else {
      $this->path = explode('.', $config['path']);
    }
  }

  /**
   * @inheritDoc
   */
  public function internalTransform($value)
  {
    $v = null;

    // Fallback to 'source' if none provided
    if(($this->config['source'] ?? 'source') == 'source' && !isset($this->config['field'])) {
      $v = $value;
    } else {
      $v = $this->getValue($this->config['source'] ?? 'source', $this->config['field'], $value);
    }

    $dive = \codename\core\io\helper\deepaccess::get($v, $this->path);

    if($dive === null && ($this->config['required'] ?? false)) {
      $this->errorstack->addError('VALUE_NULL', 0, [
        'config' => $this->config,
        'value' => $value
      ]);
    }

    return $dive;
  }

  /**
   * @inheritDoc
   */
  public function getSpecification() : array
  {
    throw new \LogicException('Not implemented'); // TODO
  }
}
