<?php
/**
 * PDF Adapter for Imager X
 *
 * @link      https://www.spacecat.ninja/
 * @copyright Copyright (c) 2022 André Elvan
 */

namespace SpaceCatNinja\ImagerXPdfAdapter;

use CraftCms\Cms\Plugin\Plugin;
use CraftCms\Cms\Plugin\PluginSettings;
use CraftCms\Cms\Twig\Variables\CraftVariable;
use SpaceCatNinja\ImagerX\Adapters\Adapters;
use SpaceCatNinja\ImagerXPdfAdapter\Adapters\PDF;
use SpaceCatNinja\ImagerXPdfAdapter\Twig\PDFAdapterVariable;

/**
 * @author    SpaceCatNinja
 * @package   ImagerXPDFAdapter
 * @since     1.0.0
 */
class PDFAdapter extends Plugin
{
    public function register(): void
    {
        parent::register();

        $this->app->singleton(Settings::class, fn() => $this->getSettings());
    }

    public function boot(Adapters $adapters): void
    {
        // Register adapter in Imager X
        $adapters->register('pdf', PDF::class);

        // Register our variable, craft.pdfadapter
        CraftVariable::macro('pdfadapter', static fn() => app(PDFAdapterVariable::class));
    }

    protected static function createSettings(): ?PluginSettings
    {
        return new Settings();
    }
}
