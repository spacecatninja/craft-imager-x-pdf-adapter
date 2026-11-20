<?php
/**
* PDF Adapter for Imager X
 *
 * @link      https://www.spacecat.ninja/
 * @copyright Copyright (c) 2022 André Elvan
  */

namespace SpaceCatNinja\ImagerXPdfAdapter;

use craft\base\Model;

class Settings extends Model
{
    public int $defaultDensity = 144;
    public string $defaultFormat = 'png';
    
    public bool $cacheEnabled = true;
    public int|bool|string $cacheDuration = false;
}
