<?php
/**
 * PDF Adapter for Imager X
 *
 * @link      https://www.spacecat.ninja/
 * @copyright Copyright (c) 2022 André Elvan
 */

namespace SpaceCatNinja\ImagerXPdfAdapter\Twig;

use Craft;
use craft\elements\Asset;
use SpaceCatNinja\ImagerXPdfAdapter\Adapters\PDF;
use SpaceCatNinja\ImagerXPdfAdapter\PDFAdapter;

/**
 * PDFAdapterVariable Variable
 *
 * @author    SpaceCatNinja
 * @package   ImagerXPDFAdapter
 * @since     1.0.0
 */

class PDFAdapterVariable
{
    public function load(Asset|string $asset, array $opts = []): ?PDF
    {
        return PDFAdapter::getInstance()->loader->loadPdf($asset, $opts);
    }
}
