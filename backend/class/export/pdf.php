<?php

namespace codename\core\io\export;

use codename\core\datacontainer;
use codename\core\value\text\fileabsolute;

/**
 * converts html to pdf
 * including CSS Stylesheets and other stuff.
 */
abstract class pdf
{
    /**
     * default configuration values
     * @var array
     */
    protected static array $defaults = [
      'default_font' => 'Helvetica',
      'page_size' => 'A4',
      'page_orientation' => 'portrait',
    ];
    /**
     * [protected description]
     * @var datacontainer
     */
    protected datacontainer $config;

    /**
     * [__construct description]
     * @param array $config [description]
     */
    public function __construct(array $config = [])
    {
        $this->config = new datacontainer($config);

        // set defaults
        foreach (self::$defaults as $key => $value) {
            if (!$this->config->isDefined($key)) {
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
    abstract protected function initClient();

    /**
     * sets the input data (html)
     * @param string $html
     */
    abstract public function setHtml(string $html);

    /**
     * renders/executes the rendering process
     */
    abstract public function render();

    /**
     * gets the absolute file path to the rendered file (output)
     * @return fileabsolute
     */
    abstract public function getFilepath(): fileabsolute;
}
