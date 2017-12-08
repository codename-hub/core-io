<?php
namespace codename\core\io\export;

use codename\core\value\text\fileabsolute;

/**
 * converts html to pdf
 * including CSS Stylesheets and other stuff.
 */
abstract class pdf {

  /**
   * [protected description]
   * @var \codename\core\datacontainer
   */
  protected $config = null;

  /**
   * default configuration values
   * @var array
   */
  protected static $defaults = [
    'default_font'      => 'Helvetica',
    'pape_size'         => 'A4',
    'page_orientation'  => 'portrait'
  ];

  /**
   * [__construct description]
   * @param array $config [description]
   */
  public function __construct(array $config = array())
  {
    $this->config = new \codename\core\datacontainer($config);

    // set defaults
    foreach(self::$defaults as $key => $value) {
      if(!$this->config->isDefined($key)) {
        $this->config->setData($key, $value);
      }
    }

    // @TODO: check configuration!

    // init!
    $this->initClient();
  }

  /**
   * main initialization routine
   */
  protected abstract function initClient();

  /**
   * sets the input data (html)
   * @param string $html
   */
  public abstract function setHtml(string $html);

  /**
   * renders/executes the rendering process
   */
  public abstract function render();

  /**
   * gets the absolute file path to the rendered file (output)
   * @return fileabsolute
   */
  public abstract function getFilepath() : fileabsolute;

}
