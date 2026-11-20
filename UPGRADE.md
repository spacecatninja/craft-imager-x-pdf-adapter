# Upgrading to Imager X PDF Adapter 2 (Craft 6)

This guide lists what changed in version 2 of the PDF adapter for Imager X, and what you need to update in your project and custom code. It applies when upgrading from version 1 (Craft 4 or 5, and Imager X 4, 5 or 6).

Templates aren't affected: `craft.imagerx.transformImage()` with a PDF, the `adapterParams` transform parameter and `craft.pdfadapter.load()` work as before. Projects need to move and convert the config file, if they have one. The class changes only affect custom code that uses the plugin's classes directly.

## Requirements

- Craft CMS 6.0.0 or later
- Imager X 7.0.0-beta.2 or later
- PHP 8.5 or later, with Imagick that can open PDFs
- The plugin doesn't need the Yii 2 adapter (`craftcms/yii2-adapter`).

## Upgrade steps

1. Upgrade your project to Craft 6, and Imager X to version 7.
2. Update the plugin: `composer require spacecatninja/imager-x-pdf-adapter:^2.0.0`.
3. If you have a config file, move it to `config/craft/imager-x-pdf-adapter.php`, and convert it (see below).
4. Review the breaking changes below, and update your code.

## Breaking changes

### The config file

Craft 6 reads plugin config files from `config/craft/`, as plain Laravel config. Move `config/imager-x-pdf-adapter.php` to `config/craft/imager-x-pdf-adapter.php`. If it uses the multi-environment `'*'` format, return a flat array instead, and use `env()` instead of `craft\helpers\App::env()`. The settings themselves are unchanged.

Before:

```php
return [
    '*' => [
        'defaultDensity' => 144,
    ],
    'production' => [
        'defaultDensity' => 200,
    ],
];
```

After:

```php
return [
    'defaultDensity' => env('APP_ENV') === 'production' ? 200 : 144,
];
```

Config files are loaded before Laravel knows the environment, so use `env()` for per-environment values, not `app()->environment()`.

### Namespace and classes

The plugin moved from `spacecatninja\imagerxpdfadapter` to the `SpaceCatNinja\ImagerXPdfAdapter` namespace, with Laravel-style folders.

| Before | After |
| --- | --- |
| `spacecatninja\imagerxpdfadapter\PDFAdapter` | `SpaceCatNinja\ImagerXPdfAdapter\PDFAdapter` |
| `spacecatninja\imagerxpdfadapter\models\Settings` | `SpaceCatNinja\ImagerXPdfAdapter\Settings` |
| `spacecatninja\imagerxpdfadapter\adapters\PDF` | `SpaceCatNinja\ImagerXPdfAdapter\Adapters\PDF` |
| `spacecatninja\imagerxpdfadapter\services\PdfLoaderService` | `SpaceCatNinja\ImagerXPdfAdapter\Services\PdfLoaderService` |
| `spacecatninja\imagerxpdfadapter\variables\PDFAdapterVariable` | `SpaceCatNinja\ImagerXPdfAdapter\Twig\PDFAdapterVariable` |

`PDF` implements Imager X 7's `SpaceCatNinja\ImagerX\Adapters\ImagerAdapterInterface`, and its `$sourceModel` is a `SpaceCatNinja\ImagerX\Data\LocalSourceImageModel`. `PdfLoaderService` no longer extends `craft\base\Component`.

### Settings and the loader service

`PDFAdapter::getInstance()->getSettings()` and the plugin's `loader` component were removed. Resolve the settings and the service from the container instead.

Before:

```php
use spacecatninja\imagerxpdfadapter\PDFAdapter;

$density = PDFAdapter::getInstance()->getSettings()->defaultDensity;
$pdf = PDFAdapter::getInstance()->loader->loadPdf($asset, ['page' => 2]);
```

After:

```php
use SpaceCatNinja\ImagerXPdfAdapter\Services\PdfLoaderService;
use SpaceCatNinja\ImagerXPdfAdapter\Settings;

$density = app(Settings::class)->defaultDensity;
$pdf = app(PdfLoaderService::class)->loadPdf($asset, ['page' => 2]);
```
