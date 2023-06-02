<?php

namespace codename\core\io\export\pdf;

use codename\core\exception;
use codename\core\io\export\pdf;
use codename\core\value\text\fileabsolute;
use DOMException;
use Dompdf\Options;
use ReflectionException;

/**
 * dompdf implementation / client
 * @see https://github.com/dompdf/dompdf
 */
class dompdf extends pdf
{
    /**
     * [protected description]
     * @var null|\Dompdf\Dompdf
     */
    protected ?\Dompdf\Dompdf $dompdf = null;
    /**
     * path to file which was last created by render()
     * @var null|fileabsolute
     */
    protected ?fileabsolute $outputFile = null;

    /**
     * {@inheritDoc}
     */
    public function setHtml(string $html): void
    {
        // @TODO: clean html?
        $this->dompdf->loadHtml($html);
    }

    /**
     * {@inheritDoc}
     * @throws ReflectionException
     * @throws DOMException
     * @throws \Dompdf\Exception
     * @throws exception
     */
    public function render(): void
    {
        $this->dompdf->render();

        $generatedPdfData = $this->dompdf->output();

        // generate tempfile
        $outputPath = tempnam(sys_get_temp_dir(), 'dompdf_');

        if (!file_put_contents($outputPath, $generatedPdfData)) {
            throw new exception('EXCEPTION_EXPORT_PDF_DOMPDF_COULD_NOT_WRITE_FILE', exception::$ERRORLEVEL_ERROR, [$outputPath]);
        } else {
            $this->outputFile = new fileabsolute($outputPath);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function getFilepath(): fileabsolute
    {
        return $this->outputFile;
    }

    /**
     * {@inheritDoc}
     */
    protected function initClient(): void
    {
        $options = new Options();

        // translate some configs
        $options->set('defaultFont', $this->config->getData('default_font'));

        $options->set('isRemoteEnabled', $this->config->getData('remote_enabled') ?? false);
        $options->set('isHtml5ParserEnabled', true);

        if ($this->config->getData('chroot') ?? false) {
            $options->set('chroot', $this->config->getData('chroot'));
        }
        if ($this->config->getData('dpi') ?? false) {
            $options->set('dpi', $this->config->getData('dpi'));
        }
        if ($this->config->getData('tempDir') ?? false) {
            $options->set('tempDir', $this->config->getData('tempDir'));
        }
        if ($this->config->getData('fontDir') ?? false) {
            $options->set('fontDir', $this->config->getData('fontDir'));
        }
        if ($this->config->getData('fontCache') ?? false) {
            $options->set('fontCache', $this->config->getData('fontCache'));
        }

        $this->dompdf = new \Dompdf\Dompdf($options);

        // set page size and orientation
        $this->dompdf->setPaper($this->config->getData('page_size'), $this->config->getData('page_orientation'));
    }
}
