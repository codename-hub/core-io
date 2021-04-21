<?php
namespace codename\core\io\transform\convert;

use codename\core\exception;

/**
 * convert a string to specific number format
 */
class numberformat extends \codename\core\io\transform\convert {

  /**
   * [protected description]
   * @var \NumberFormatter
   */
  protected $numberFormatter;

  /**
   * numberformatter locale to use - or source!
   * @var string
   */
  protected $locale = null;

  /**
   * numberformatter style to use - or source!
   * @var string
   */
  protected $style = null;

  /**
   * @inheritDoc
   */
  public function __construct(array $config)
  {
    parent::__construct($config);
    $this->locale = $this->config['locale'];
    $this->style = $this->config['style'];

    // initialize numberformatter, only if locale & style are not arrays/objects
    // and therefore static
    if(!is_array($this->locale) && !is_array($this->style)) {
      $this->numberFormatter = new \NumberFormatter($this->locale, self::getNumberFormatterStyle($this->style));
    }

    if($this->numberFormatter && ($this->config['fraction_digits'] ?? null)) {
      $this->numberFormatter->setAttribute(\NumberFormatter::FRACTION_DIGITS, $this->config['fraction_digits']);
    }

    if($this->numberFormatter && array_key_exists('grouping_separator_symbol',$this->config)) {
      $this->numberFormatter->setSymbol(\NumberFormatter::GROUPING_SEPARATOR_SYMBOL, $this->config['grouping_separator_symbol']);
    }
  }

  /**
   * [getNumberFormatter description]
   * @param  string $locale [description]
   * @param  string $style  [description]
   * @return [type]         [description]
   */
  protected function getNumberFormatter(string $locale, string $style) {
    if(!isset($this->numberFormatterInstances[$locale.'-'.$style])) {
      $this->numberFormatterInstances[$locale.'-'.$style] = new \NumberFormatter($locale, self::getNumberFormatterStyle($style));
    }
    return $this->numberFormatterInstances[$locale.'-'.$style];
  }

  /**
   * [protected description]
   * @var \NumberFormatter[]
   */
  protected $numberFormatterInstances = [];

  /**
   * [getNumberFormatterStyle description]
   * @param  string $style  [description]
   * @return int            [description]
   */
  protected static function getNumberFormatterStyle(string $style) {
    switch ($style) {
      case 'decimal':
        return \NumberFormatter::DECIMAL;
      default:
        throw new exception('EXCEPTION_CORE_IO_TRANSFORM_CONVERT_NUMBERFORMAT_INVALID_TYPE', exception::$ERRORLEVEL_ERROR, $style);
    }
  }

  /**
   * [EXCEPTION_CORE_IO_TRANSFORM_CONVERT_NUMBERFORMAT_INVALID_TYPE description]
   * @var string
   */
  const EXCEPTION_CORE_IO_TRANSFORM_CONVERT_NUMBERFORMAT_INVALID_TYPE = 'EXCEPTION_CORE_IO_TRANSFORM_CONVERT_NUMBERFORMAT_INVALID_TYPE';

  /**
   * @inheritDoc
   */
  public function internalTransform($value)
  {
    $numberFormatter = null;

    if(!$this->numberFormatter) {
      //
      // if numberformatter isn't set,
      // create one from dynamic settings
      //
      $locale = null;
      $style = null;
      // locale may be defined through a pipeline option
      if(is_array($this->locale)) {
        if($this->locale['source'] === 'option') {
          $locale = $this->getValue($this->locale['source'], $this->locale['field'], null);
        } else {
          throw new exception('TRANSFORM_NUMBERFORMAT_CONFIG_LOCALE_INVALID_SOURCE', exception::$ERRORLEVEL_ERROR, $this->locale);
        }
      } else {
        $locale = $this->config['locale'];
      }

      if(is_array($this->style)) {
        if($this->style['source'] === 'option') {
          $style = $this->getValue($this->style['source'], $this->style['field'], null);
        } else {
          throw new exception('TRANSFORM_NUMBERFORMAT_CONFIG_STYLE_INVALID_SOURCE', exception::$ERRORLEVEL_ERROR, $this->style);
        }
      } else {
        $style = $this->config['style'];
      }

      $numberFormatter = $this->getNumberFormatter($locale, $style);
    } else {
      // fallback/use static numberformatter already created during .ctor
      $numberFormatter = $this->numberFormatter;
    }

    // doesn't work:
    // return floatval($value);
    // TODO: Handle source or transform
    $parsed = $numberFormatter->parse($this->getValue($this->config['source'], $this->config['field'], $value));
    if($parsed === false) {
      // error
      $this->errorstack->addError('convert_numberformat', 'INVALID_PARSE', [
        'config' => $this->config,
        'value' => $value
      ]);
    }
    return $parsed;
  }

  /**
   * @inheritDoc
   */
  public function getSpecification(): array
  {
    return [
      'type' => 'transform',
      'source' => [ "{$this->config['source']}.{$this->config['field']}" ]
    ];
  }
}
