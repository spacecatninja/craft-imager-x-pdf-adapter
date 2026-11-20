<?php
/**
 * PDF Adapter for Imager X
 *
 * @link      https://www.spacecat.ninja/
 * @copyright Copyright (c) 2022 André Elvan
 */

namespace SpaceCatNinja\ImagerXPdfAdapter\Adapters;

use CraftCms\Cms\Asset\Elements\Asset;
use CraftCms\Cms\Cms;
use CraftCms\Cms\Support\Config;
use CraftCms\Cms\Support\Facades\Path;
use CraftCms\Cms\Support\File;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use ImagickException;
use SpaceCatNinja\ImagerX\Adapters\ImagerAdapterInterface;
use SpaceCatNinja\ImagerX\Data\LocalSourceImageModel;
use SpaceCatNinja\ImagerX\Exceptions\ImagerException;
use SpaceCatNinja\ImagerXPdfAdapter\Settings;

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
        $settings = app(Settings::class);

        $this->density = $opts['density'] ?? $settings->defaultDensity;
        $this->format = $opts['format'] ?? $settings->defaultFormat;
        $this->page = $opts['page'] ?? 1;

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
            Log::error('An error occured when trying to open file with PDF Adapter: '.$imagerException->getMessage());
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
            Log::error('An error occured when trying to open file with PDF Adapter: '.$imagerException->getMessage());

            return 0;
        }

        $duration = Config::durationInSeconds(Cms::config()->cacheDuration) ?: null;

        return Cache::remember(self::PAGECOUNT_CACHE_KEY.'_'.base64_encode($this->sourceModel->getFilePath()), $duration, function() {
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
            Log::error('An error occured when trying to open file with PDF Adapter: '.$imagerException->getMessage());
            return;
        }

        $this->transformPath = $this->sourceModel->transformPath;

        $cachePath = Path::runtime().DIRECTORY_SEPARATOR.'imager'.DIRECTORY_SEPARATOR.'pdf-adapter'.$this->sourceModel->transformPath.'/';

        if (!is_dir($cachePath)) {
            try {
                File::ensureDirectoryExists($cachePath);
            } catch (\Throwable) {
                // just ignore
            }

            if (!is_dir($cachePath)) {
                Log::error('Could not create path: '.$cachePath);

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
            Log::error('An error occured when trying to create rasterized image of PDF: '.$imagickException);
        }
    }

    protected function getTempFilename(string $basename): string
    {
        return strtr('$basename_$page-$density.$format', ['$basename' => $basename, '$page' => $this->page, '$density' => $this->density, '$format' => $this->format]);
    }

    protected function shouldCreateTempFile(string $path): bool
    {
        $settings = app(Settings::class);

        return !$settings->cacheEnabled ||
            !file_exists($path) ||
            (($settings->cacheDuration !== false) && (File::lastModified($path) + $settings->cacheDuration < time()));
    }
}
