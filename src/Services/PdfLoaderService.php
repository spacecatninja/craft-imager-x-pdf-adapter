<?php
/**
 * PDF Adapter for Imager X
 *
 * @link      https://www.spacecat.ninja/
 * @copyright Copyright (c) 2022 André Elvan
 */
namespace SpaceCatNinja\ImagerXPdfAdapter\Services;

use craft\base\Component;
use craft\elements\Asset;

use SpaceCatNinja\ImagerXPdfAdapter\Adapters\PDF;


/**
 * PdfService Service
 *
 * @author    SpaceCatNinja
 * @package   ImagerXPDFAdapter
 * @since     1.0.0
 */
class PdfLoaderService extends Component
{
    public function loadPdf(Asset|string $asset, array $opts = []): ?PDF
    {
        return new PDF($asset, $opts);
    }
}
