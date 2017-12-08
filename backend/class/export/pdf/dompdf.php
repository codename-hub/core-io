<?php
namespace codename\core\io\export\pdf;

use \codename\core\exception;

/**
 * dompdf implementation / client
 * @see https://github.com/dompdf/dompdf
 */
class dompdf extends \codename\core\io\export\pdf {

  /**
   * [protected description]
   * @var \Dompdf\Dompdf
   */
  protected $dompdf = null;

  /**
   * @inheritDoc
   */
  protected function initClient()
  {
    $options = new \Dompdf\Options();

    // translate some configs
    $options->set('defaultFont', $this->config->getData('default_font'));

    $this->dompdf = new \Dompdf\Dompdf();

    $this->dompdf->set_option('isRemoteEnabled', $this->config->getData('remote_enabled') ?? false);
    $this->dompdf->set_option('isHtml5ParserEnabled', true);
    // set page size and orientation
    $this->dompdf->setPaper($this->config->getData('page_size'), $this->config->getData('page_orientation'));
  }

  /**
   * @inheritDoc
   */
  public function setHtml(string $html)
  {
    // @TODO: clean html?
    $this->dompdf->loadHtml($html);
  }

  /**
   * @inheritDoc
   */
  public function render()
  {
    $this->dompdf->render();

    $generatedPdfData = $this->dompdf->output();

    // generate tempfile
    $outputPath = tempnam(sys_get_temp_dir(), 'dompdf_');

    if(!file_put_contents($outputPath, $generatedPdfData)) {
      throw new exception('EXCEPTION_EXPORT_PDF_DOMPDF_COULD_NOT_WRITE_FILE', exception::$ERRORLEVEL_ERROR, array($outputPath));
    } else {
      $this->outputFile = new \codename\core\value\text\fileabsolute($outputPath);
    }
  }

  /**
   * path to file which was last created by render()
   * @var \codename\core\value\text\fileabsolute
   */
  protected $outputFile = null;

  /**
   * @inheritDoc
   */
  public function getFilepath(): \codename\core\value\text\fileabsolute
  {
    return $this->outputFile;
  }

}
