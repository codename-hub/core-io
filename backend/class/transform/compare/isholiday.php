<?php
namespace codename\core\io\transform\compare;

use codename\core\app;

/**
 * [isequal description]
 */
class isholiday extends \codename\core\io\transform\compare {

  /**
   * @inheritDoc
   */
  public function internalTransform($value)
  {
    $countryConfig = $this->config['country'];
    $dateConfig = $this->config['date'];

    $countryValue = $this->getValue($countryConfig['source'], $countryConfig['field'], $value);
    $dateValue = $this->getValue($dateConfig['source'], $dateConfig['field'], $value);

    $result = app::getModel('holidays')
      ->addFilter('holidays_country', $countryValue)
      ->addFilter('holidays_orderitemcommission_type', 'energy') //TODO
      ->addFilter('holidays_check_date', $dateValue)
      ->addFilter('holidays_can_change', false)
      ->search()->getResult();

    return (count($result) === 1 ? true : false);
  }

  /**
   * @inheritDoc
   */
  public function getSpecification(): array
  {
    return [
      'type' => 'transform',
      // TODO: implement transform as a source!
      'source' => [ "source.{$this->config['country']['field']}", "source.{$this->config['date']['field']}" ]
    ];
  }

}
