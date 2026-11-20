# Imager X PDF Adapter Changelog

## 2.0.0 - Unreleased

> [!IMPORTANT]
> Version 2 requires Craft CMS 6, Imager X 7 and PHP 8.5, the config file moves to `config/craft/`, and there are breaking changes for code that uses the plugin's classes. See [UPGRADE.md](UPGRADE.md).

### Changed
- The plugin now requires Craft CMS 6, Imager X 7.0.0-beta.2 or later, and PHP 8.5, and uses Craft 6's native plugin APIs. It doesn't need the Yii 2 adapter.
- The config file is now `config/craft/imager-x-pdf-adapter.php`, and is a plain Laravel config file.
- Classes moved to the `SpaceCatNinja\ImagerXPdfAdapter` namespace, and to Laravel-style folders.
- The adapter is now registered with Imager X's `Adapters` registry.

### Removed
- Removed the plugin's `loader` component. Use `app(PdfLoaderService::class)` instead of `PDFAdapter::getInstance()->loader`.

### Fixed
- Fixed the PDF page being rasterized again on every call to `getPath()` and `getTransformPath()`. Imager X calls both for each transform, so with `cacheEnabled` off, each page was rasterized twice.
- Fixed `getNumPages()` throwing an exception for a missing or unreadable file. It now logs the error and returns `0`.

## 1.1.1 - 2026-05-23

### Added
- Added support for Imager X 6.0

## 1.1.0 - 2024-02-09

### Added
- Added support for Craft 5

## 1.0.0 - 2022-06-16

### Added
- Initial public release
