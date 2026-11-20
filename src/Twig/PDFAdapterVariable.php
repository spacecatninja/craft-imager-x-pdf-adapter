<?php
/**
 * PDF Adapter for Imager X
 *
 * @link      https://www.spacecat.ninja/
 * @copyright Copyright (c) 2022 André Elvan
 */

namespace SpaceCatNinja\ImagerXPdfAdapter\Twig;

use CraftCms\Cms\Asset\Elements\Asset;
use SpaceCatNinja\ImagerXPdfAdapter\Adapters\PDF;
use SpaceCatNinja\ImagerXPdfAdapter\Services\PdfLoaderService;

/**
 * The `craft.pdfadapter` variable.
 *
 * @author    SpaceCatNinja
 * @package   ImagerXPDFAdapter
 * @since     1.0.0
 */
class PDFAdapterVariable
{
    public function load(Asset|string $asset, array $opts = []): ?PDF
    {
        return app(PdfLoaderService::class)->loadPdf($asset, $opts);
    }
}
