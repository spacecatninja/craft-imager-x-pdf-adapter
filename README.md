Imager X PDF Adapter plugin for Craft CMS
===

A plugin for transforming PDFs using Imager X.   
Also, an example of [how to make a custom file adapter for Imager X](https://imager-x.spacecat.ninja/extending.html#file-adapters).

## Requirements

This plugin requires Imager X 4.1.0+, Craft CMS 4.0.0+, PHP 8.0+ and Imagick with support for opening PDFs. 

## Installation

To install the plugin, follow these instructions:

1. Install with composer via `composer require spacecatninja/imager-x-pdf-adapter` from your project directory.
2. Install the plugin in the Craft Control Panel under Settings → Plugins, or from the command line via `./craft install/plugin imager-x-pdf-adapter`.

---

## Usage

Install and configure the adapter as described below. 

You can now transform PDF files simply by padding a PDF file to Imager's `transformImage` method:

```
{% set transforms = craft.imagerx.transformImage(myPdfAsset, { width: 200 }) %}
```

You can pass configuration parameters to the adapter, using the `adapterParams` transform parameter:

```
{% set transforms = craft.imagerx.transformImage(myPdfAsset, { width: 200, adapterParams: { density: 72, page: 5 } }) %}
```

You can also create the adapter as a separate step, with the advantage of being able to better control settings, 
and inspecting the PDF.

```
{% set pdf = craft.pdfadapter.load(asset, { density: 300 }) %}

# pages: {{ pdf.getNumPages() }}<br>

{% do pdf.setPage(5) %}
{% set transformPage5 = craft.imagerx.transformImage(pdf, { width: 600 }) %}

{% do pdf.setPage(pdf.getNumPages()) %}
{% set transformLastPage = craft.imagerx.transformImage(pdf, { width: 600 }) %}
```

### Auto generation

The PDF adapter works perfectly with the [auto generation functionality](https://imager-x.spacecat.ninja/usage/generate.html) 
in Imager. The only thing you need to do, is add `pdf` to the list of safe file formats
to transform, using the config setting ['safeFileFormats'](https://imager-x.spacecat.ninja/configuration.html#safefileformats-array).

```
'safeFileFormats' => ['jpg', 'jpeg', 'gif', 'png', 'pdf']
```

## Configuring

You can configure the transformer by creating a file in your config folder called
`imager-x-pdf-adapter.php`, and override settings as needed.

### defaultDensity [int]
Default: `144`  
Sets the default density/dpi to use when converting from pdf to bitmap image. 

The higher the number, the larger the resulting file will be, and the better the 
quality. But it'll also require more resources, and opening PDFs is a resource 
intensive endeavour, so be careful. 

### defaultFormat [string]
Default: `'png'`  
Sets the default format of the temporary bitmap image that the adapter generates. You 
can of course transform this to whatever format you'd like later, using Imager.

### cacheEnabled [bool]
Default: `true`  
Enables/disables caching of generated images. Only disable this if testing, as it
will seriously impact performance.

### cacheDuration [bool|int]
Default: `false`  
Sets the cacheDuration that's used if `cacheEnabled` is `true`. By default, forever. 
Clearing the Imager runtime cache will also clear this cache.


---


Price, license and support
---
The plugin is released under the MIT license. It requires Imager X, which is a commercial plugin [available in the Craft plugin store](https://plugins.craftcms.com/imager-x). 
