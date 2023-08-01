<?php

namespace codename\core\io\transform\convert;

use codename\core\exception;
use codename\core\io\transform\convert;
use NumberFormatter;

/**
 * convert a string to specific number format
 */
class numberformat extends convert
{
    /**
     * [EXCEPTION_CORE_IO_TRANSFORM_CONVERT_NUMBERFORMAT_INVALID_TYPE description]
     * @var string
     */
    public const EXCEPTION_CORE_IO_TRANSFORM_CONVERT_NUMBERFORMAT_INVALID_TYPE = 'EXCEPTION_CORE_IO_TRANSFORM_CONVERT_NUMBERFORMAT_INVALID_TYPE';
    /**
     * [protected description]
     * @var null|NumberFormatter
     */
    protected ?NumberFormatter $numberFormatter = null;
    /**
     * numberformatter locale to use - or source!
     * @var array|string
     */
    protected array|string $locale;
    /**
     * numberformatter style to use - or source!
     * @var array|string
     */
    protected array|string $style;
    /**
     * [protected description]
     * @var array
     */
    protected array $numberFormatterInstances = [];

    /**
     * {@inheritDoc}
     * @param array $config
     * @throws exception
     */
    public function __construct(array $config)
    {
        parent::__construct($config);
        $this->locale = $this->config['locale'];
        $this->style = $this->config['style'];

        // initialize numberformatter, only if locale & style are not arrays/objects
        // and therefore static
        if (!is_array($this->locale) && !is_array($this->style)) {
            $this->numberFormatter = new NumberFormatter($this->locale, self::getNumberFormatterStyle($this->style));
        }

        if (($this->numberFormatter ?? false) && array_key_exists('fraction_digits', $this->config)) {
            $this->numberFormatter->setAttribute(NumberFormatter::FRACTION_DIGITS, $this->config['fraction_digits']);
        }

        if (($this->numberFormatter ?? false) && array_key_exists('min_fraction_digits', $this->config)) {
            $this->numberFormatter->setAttribute(NumberFormatter::MIN_FRACTION_DIGITS, $this->config['min_fraction_digits']);
        }

        if (($this->numberFormatter ?? false) && array_key_exists('max_fraction_digits', $this->config)) {
            $this->numberFormatter->setAttribute(NumberFormatter::MAX_FRACTION_DIGITS, $this->config['max_fraction_digits']);
        }

        if (($this->numberFormatter ?? false) && ($roundingModeStr = $this->config['rounding_mode'] ?? null)) {
            $roundingMode = match ($roundingModeStr) {
                'ceiling' => NumberFormatter::ROUND_CEILING,
                'down' => NumberFormatter::ROUND_DOWN,
                'floor' => NumberFormatter::ROUND_FLOOR,
                'half_down' => NumberFormatter::ROUND_HALFDOWN,
                'half_even', 'symmetric' => NumberFormatter::ROUND_HALFEVEN,
                'half_up', 'financial' => NumberFormatter::ROUND_HALFUP,
                'up' => NumberFormatter::ROUND_UP,
                default => throw new exception('INVALID_ROUNDING_MODE', exception::$ERRORLEVEL_ERROR, $roundingModeStr),
            };
            $this->numberFormatter->setAttribute(NumberFormatter::ROUNDING_MODE, $roundingMode);
        }

        if (($this->numberFormatter ?? false) && array_key_exists('grouping_separator_symbol', $this->config)) {
            $this->numberFormatter->setSymbol(NumberFormatter::GROUPING_SEPARATOR_SYMBOL, $this->config['grouping_separator_symbol']);
        }
    }

    /**
     * [getNumberFormatterStyle description]
     * @param string $style [description]
     * @return int            [description]
     * @throws exception
     */
    protected static function getNumberFormatterStyle(string $style): int
    {
        return match ($style) {
            'decimal' => NumberFormatter::DECIMAL,
            default => throw new exception('EXCEPTION_CORE_IO_TRANSFORM_CONVERT_NUMBERFORMAT_INVALID_TYPE', exception::$ERRORLEVEL_ERROR, $style),
        };
    }

    /**
     * {@inheritDoc}
     * @param mixed $value
     * @return mixed
     * @throws exception
     */
    public function internalTransform(mixed $value): mixed
    {
        if (!$this->numberFormatter) {
            //
            // if numberformatter isn't set,
            // create one from dynamic settings
            //
            // locale may be defined through a pipeline option
            if (is_array($this->locale)) {
                if ($this->locale['source'] === 'option') {
                    $locale = $this->getValue($this->locale['source'], $this->locale['field'], null);
                } else {
                    throw new exception('TRANSFORM_NUMBERFORMAT_CONFIG_LOCALE_INVALID_SOURCE', exception::$ERRORLEVEL_ERROR, $this->locale);
                }
            } else {
                $locale = $this->config['locale'];
            }

            if (is_array($this->style)) {
                if ($this->style['source'] === 'option') {
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

        // TODO: Handle source or transform
        $parsed = $numberFormatter->parse($this->getValue($this->config['source'], $this->config['field'], $value));
        if ($parsed === false) {
            // error
            $this->errorstack->addError('convert_numberformat', 'INVALID_PARSE', [
              'config' => $this->config,
              'value' => $value,
            ]);
        }
        return $parsed;
    }

    /**
     * [getNumberFormatter description]
     * @param string $locale [description]
     * @param string $style [description]
     * @return NumberFormatter [type]         [description]
     * @throws exception
     */
    protected function getNumberFormatter(string $locale, string $style): NumberFormatter
    {
        if (!isset($this->numberFormatterInstances[$locale . '-' . $style])) {
            $this->numberFormatterInstances[$locale . '-' . $style] = new NumberFormatter($locale, self::getNumberFormatterStyle($style));
        }
        return $this->numberFormatterInstances[$locale . '-' . $style];
    }

    /**
     * {@inheritDoc}
     */
    public function getSpecification(): array
    {
        return [
          'type' => 'transform',
          'source' => ["{$this->config['source']}.{$this->config['field']}"],
        ];
    }
}
