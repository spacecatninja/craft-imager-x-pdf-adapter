<?php
/**
 * PDF Adapter for Imager X
 *
 * @link      https://www.spacecat.ninja/
 * @copyright Copyright (c) 2022 André Elvan
 */

namespace spacecatninja\imagerxpdfadapter;

use craft\base\Model;
use craft\base\Plugin;
use craft\web\twig\variables\CraftVariable;

use spacecatninja\imagerxpdfadapter\adapters\PDF;
use spacecatninja\imagerxpdfadapter\models\Settings;
use spacecatninja\imagerxpdfadapter\services\PdfLoaderService;
use spacecatninja\imagerxpdfadapter\variables\PDFAdapterVariable;

use yii\base\Event;

/**
 * @author    SpaceCatNinja
 * @package   ImagerXPDFAdapter
 * @since     1.0.0
 *
 * @property  PdfLoaderService $loader
 *
 */
class PDFAdapter extends Plugin
{
    public function init(): void
    {
        parent::init();

        // Register services
        $this->setComponents([
            'loader' => PdfLoaderService::class,
        ]);

        // Register our variables
        Event::on(CraftVariable::class, CraftVariable::EVENT_INIT,
            static function(Event $event) {
                /** @var CraftVariable $variable */
                $variable = $event->sender;
                $variable->set('pdfadapter', PDFAdapterVariable::class);
            }
        );
        
        // Register adapter in Imager X
        Event::on(\spacecatninja\imagerx\ImagerX::class,
            \spacecatninja\imagerx\ImagerX::EVENT_REGISTER_ADAPTERS,
            static function (\spacecatninja\imagerx\events\RegisterAdaptersEvent $event) {
                $event->adapters['pdf'] = PDF::class;
            }
        );

    }

    /**
     * @inheritdoc
     */
    protected function createSettingsModel(): ?Settings
    {
        return new Settings();
    }

}
