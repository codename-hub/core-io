<?php
namespace codename\core\io\transform\convert;

/**
 * convert a string (date) to another date format
 */
class datetime extends \codename\core\io\transform\convert {

  /**
   * @inheritDoc
   */
  public function internalTransform($value)
  {
    $v = $this->getValue($this->config['source'], $this->config['field'], $value);

    if($v === null) {
      if(isset($this->config['required']) && $this->config['required']) {
        $this->errorstack->addError('VALUE_NULL', 0, [
          'config' => $this->config,
          'value' => $value
        ]);
      }
      return null;
    } else {
      $dt = false;
      if(is_array($this->config['source_format'])) {
        foreach($this->config['source_format'] as $sourceFormat) {
          $dt = \DateTime::createFromFormat($sourceFormat, $v);
          if($dt !== false) {
            // first successful match
            break;
          }
        }
      } else {
        $dt = \DateTime::createFromFormat($this->config['source_format'], $v);
      }
      if($dt !== false) {
        if($this->config['modify'] ?? false) {
          $dt->modify($this->config['modify']);
        }
        return $dt->format($this->config['target_format']);
      } else {
        // NOTE: we have to log this error to the errorstack either way
        // as we have a value (!= null) that leads to an internal conversion error
        $this->errorstack->addError('convert_datetime', 'INVALID_FORMAT', [
          'config' => $this->config,
          'value' => $value
        ]);
        return null;
      }
    }
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
