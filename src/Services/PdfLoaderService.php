<?php
/**
 * PDF Adapter for Imager X
 *
 * @link      https://www.spacecat.ninja/
 * @copyright Copyright (c) 2022 André Elvan
 */

namespace SpaceCatNinja\ImagerXPdfAdapter\Services;

use CraftCms\Cms\Asset\Elements\Asset;
use SpaceCatNinja\ImagerXPdfAdapter\Adapters\PDF;

/**
 * PdfLoaderService Service
 *
 * @author    SpaceCatNinja
 * @package   ImagerXPDFAdapter
 * @since     1.0.0
 */
class PdfLoaderService
{
    public function loadPdf(Asset|string $asset, array $opts = []): ?PDF
    {
        return new PDF($asset, $opts);
    }
}
