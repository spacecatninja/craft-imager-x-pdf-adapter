<?php
/**
 * PDF Adapter for Imager X
 *
 * @link      https://www.spacecat.ninja/
 * @copyright Copyright (c) 2022 André Elvan
 */

namespace spacecatninja\imagerxpdfadapter\adapters;

use Craft;
use craft\elements\Asset;
use craft\helpers\FileHelper;

use ImagickException;
use spacecatninja\imagerx\adapters\ImagerAdapterInterface;
use spacecatninja\imagerx\exceptions\ImagerException;
use spacecatninja\imagerx\models\LocalSourceImageModel;
use spacecatninja\imagerxpdfadapter\models\Settings;
use spacecatninja\imagerxpdfadapter\PDFAdapter;
use yii\base\Exception;

class PDF implements ImagerAdapterInterface
{
    public const PAGECOUNT_CACHE_KEY = 'pagecountCacheKey';

    public Asset|string|null $source = null;
    public LocalSourceImageModel|null $sourceModel = null;

    public int $page = 1;
    public int $density = 144;
    public string $format = 'png';

    private bool $isReady = false;
    private string $path = '';
    private string $transformPath = '';

    public function __construct(Asset|string|null $asset, array $opts = [])
    {
        /* @var Settings $settings */
        $settings = PDFAdapter::getInstance()?->getSettings();

        if ($settings) {
            $this->density = $opts['density'] ?? $settings->defaultDensity;
            $this->format = $opts['format'] ?? $settings->defaultFormat;
            $this->page = $opts['page'] ?? 1;
        }

        if ($asset) {
            $this->load($asset);
        }
    }

    /*
     * -- Public interface methods -----------------------------------
     */

    public function getPath(): string
    {
        if (!$this->isReady) {
            $this->getPDFBitmap();
        }

        return $this->path;
    }

    public function getTransformPath(): string
    {
        if (!$this->isReady) {
            $this->getPDFBitmap();
        }

        return $this->transformPath;
    }

    /*
     * -- Other public methods -----------------------------------
     */

    public function load(Asset|string $asset): void
    {
        $this->source = $asset;

        try {
            $this->sourceModel = new LocalSourceImageModel($asset);
        } catch (ImagerException $imagerException) {
            Craft::error('An error occured when trying to open file with PDF Adapter: '.$imagerException->getMessage(), __METHOD__);
            $this->sourceModel = null;
        }
    }


    public function getNumPages(): int
    {
        if ($this->sourceModel === null) {
            return 0;
        }

        try {
            $this->sourceModel->getLocalCopy();
        } catch (ImagerException $imagerException) {
            Craft::error('An error occured when trying to open file with PDF Adapter: '.$imagerException->getMessage(), __METHOD__);

            return 0;
        }

        return Craft::$app->getCache()->getOrSet(self::PAGECOUNT_CACHE_KEY.'_'.base64_encode($this->sourceModel->getFilePath()), function() {
            $im = new \Imagick();
            $im->pingImage($this->sourceModel->getFilePath());

            return $im->getNumberImages();
        });
    }

    public function setPage(int $page): void
    {
        $this->page = $page > 0 ? $page : 1;
    }

    /*
     * -- Protected methods -----------------------------------
     */

    protected function getPDFBitmap(): void
    {
        if ($this->sourceModel === null) {
            return;
        }

        try {
            $this->sourceModel->getLocalCopy();
        } catch (ImagerException $imagerException) {
            Craft::error('An error occured when trying to open file with PDF Adapter: '.$imagerException->getMessage(), __METHOD__);
            return;
        }
        
        $this->transformPath = $this->sourceModel->transformPath;
        
        try {
            $cachePath = Craft::$app->getPath()->getRuntimePath().DIRECTORY_SEPARATOR.'imager'.DIRECTORY_SEPARATOR.'pdf-adapter'.$this->sourceModel->transformPath.'/';
        } catch (Exception $exception) {
            Craft::error('An error occured when trying to open file with PDF Adapter: '.$exception->getMessage(), __METHOD__);
            return;
        }

        if (!realpath($cachePath)) {
            try {
                FileHelper::createDirectory($cachePath);
            } catch (\Exception) {
                // just ignore
            }

            if (!realpath($cachePath)) {
                Craft::error('Could not create path: '.$cachePath, __METHOD__);

                return;
            }
        }
        
        $this->path = $cachePath.$this->getTempFilename($this->sourceModel->basename);

        if (!$this->shouldCreateTempFile($this->path)) {
            return;
        }

        try {
            $im = new \Imagick();
            $im->setResolution($this->density, $this->density);
            $im->readimage($this->sourceModel->getFilePath().'['.($this->page - 1).']');
            $im->setImageFormat($this->format);
            $im->writeImage($this->path);
            $im->clear();
            $im->destroy();
        } catch (ImagickException $imagickException) {
            Craft::error('An error occured when trying to create rasterized image of PDF: '.$imagickException, __METHOD__);
        }
    }

    protected function getTempFilename(string $basename): string
    {
        return strtr('$basename_$page-$density.$format', ['$basename' => $basename, '$page' => $this->page, '$density' => $this->density, '$format' => $this->format]);
    }

    protected function shouldCreateTempFile(string $path): bool
    {
        /* @var Settings $settings */
        $settings = PDFAdapter::getInstance()?->getSettings();

        return !$settings->cacheEnabled ||
            !file_exists($path) ||
            (($settings->cacheDuration !== false) && (FileHelper::lastModifiedTime($path) + $settings->cacheDuration < time()));
    }
}
