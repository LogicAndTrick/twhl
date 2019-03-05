<?php

namespace App\Helpers;

use \Symfony\Component\HttpFoundation\File\MimeType\MimeTypeExtensionGuesser;
use \Symfony\Component\HttpFoundation\File\MimeType\MimeTypeGuesserInterface;
use Illuminate\Support\Arr;
 
class ExtensionMimeTypeGuesser extends MimeTypeExtensionGuesser implements MimeTypeGuesserInterface {

    /*
     * protected $defaultExtensions = array(
     *   'image/png' => 'png',
     *   ...
     * );
     */
    public function guess($path)
    {
        $ext = Arr::last(explode('.', $path), function($x) { return true; });
        $key = array_search($ext, $this->defaultExtensions);
        return $key !== false ? $key : null;
    }
}
