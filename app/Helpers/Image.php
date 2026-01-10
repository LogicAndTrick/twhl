<?php

namespace App\Helpers;

class Image
{
    const OUTPUT_FILE_EXTENSIONS = array(
        \IMAGETYPE_AVIF => 'avif',
        \IMAGETYPE_JPEG => 'jpg',
        \IMAGETYPE_WEBP => 'webp',
    );
    private bool $_opaque_input = false;
    private bool $_lossy_input = false;
    private int $_height = 0;
    private int $_width = 0;
    private \GdImage | null $_image = null;

    function __construct($image_location)
    {
        [$this->_width, $this->_height, $inputType] = getimagesize($image_location);
        switch ($inputType) {
            case \IMAGETYPE_AVIF:
                $this->_image = imagecreatefromavif($image_location);
                $this->_lossy_input = true; // AVIFs are usually lossily compressed
                break;
            case \IMAGETYPE_GIF:
                $this->_image = imagecreatefromgif($image_location);
                break;
            case \IMAGETYPE_JPEG:
                $this->_image = imagecreatefromjpeg($image_location);
                $this->_lossy_input = true; // JPEGs are always lossily compressed
                $this->_opaque_input = true; // JPEGs do not have an alpha channel
                break;
            case \IMAGETYPE_PNG:
                $this->_image = imagecreatefrompng($image_location);
                break;
            case \IMAGETYPE_WEBP:
                $this->_image = imagecreatefromwebp($image_location);
                break;
        }
        if (!$this->_opaque_input) {
            imagealphablending($this->_image, true);
        }
    }

    /**
     * @return int
     */
    function Width()
    {
        return $this->_width;
    }

    /**
     * @return int
     */
    function Height()
    {
        return $this->_height;
    }

    private function DetermineBestOutputFormat(bool $force_lossy = false) {
        $lossy = $this->_lossy_input || $force_lossy;
        if ($lossy) {
            if ($this->_opaque_input) {
                // PHP's AVIF encoder is mediocre, so prefer JPEG output for recompressing
                // opaque lossily compressed input
                return \IMAGETYPE_JPEG;
            }
            // AVIF is good for lossy compression of images with an alpha channel
            return \IMAGETYPE_AVIF;
        }
        // WebP has excellent lossless compression and supports alpha channels
        return \IMAGETYPE_WEBP;
    }

    function SaveResized(string $location, int $output_format, int $max_width, int $max_height, bool $force_size = false)
    {
        if ($this->_image === null) return;
        $new_dims = Image::GetResizeDimensions($this->Width(), $this->Height(), $max_width, $max_height);
        $actual_dims = $force_size ? array($max_width, $max_height) : $new_dims;
        $new_image = imagecreatetruecolor($actual_dims[0], $actual_dims[1]);
        imagealphablending($new_image, false);
        $transparent = imagecolorallocatealpha($new_image, 0, 0, 0, 127);
        imagefill($new_image, 0, 0, $transparent);
        imagesavealpha($new_image, true);
        // Ubuntu doesn't include a version of GD that supports imageantialias
        if (function_exists('imageantialias')) {
            imageantialias($new_image, true);
        }
        $dx = ($actual_dims[0] - $new_dims[0]) / 2;
        $dy = ($actual_dims[1] - $new_dims[1]) / 2;
        imagecopyresampled($new_image, $this->_image, $dx, $dy, 0, 0, $new_dims[0], $new_dims[1], $this->Width(), $this->Height());
       
        if ($output_format == \IMAGETYPE_AVIF) {
            imageavif($new_image, $location, 75);
        } else if ($output_format == \IMAGETYPE_JPEG) {
            imagejpeg($new_image, $location, 80);
        } else if ($output_format == \IMAGETYPE_WEBP) {
            imagewebp($new_image, $location, \IMG_WEBP_LOSSLESS);
        }
        imagedestroy($new_image);
    }

    function destroy()
    {
        if ($this->_image === null) return;
        imagedestroy($this->_image);
    }

    static $vault_image_sizes = array(
        array('width' => 160, 'height' => 160, 'prefix' => 'thumb/', 'suffix' => ''),
        array('width' => 320, 'height' => 320, 'prefix' => 'small/', 'suffix' => ''),
        array('width' => 640, 'height' => 640, 'prefix' => 'medium/', 'suffix' => ''),
        array('width' => 1024, 'height' => 1024, 'prefix' => 'large/', 'suffix' => ''),
        array('width' => 1920, 'height' => 1920, 'prefix' => 'full/', 'suffix' => '', 'force' => true),
    );

    static $comp_image_sizes = array(
        array('width' => 320, 'height' => 320, 'prefix' => 'thumb/', 'suffix' => ''),
        array('width' => 1920, 'height' => 1920, 'prefix' => 'full/', 'suffix' => '', 'force' => true),
    );

    static $avatar_image_sizes = array(
        array('width' => 20, 'height' => 20, 'prefix' => 'inline/', 'suffix' => '', 'force' => true, 'force-size' => true),
        array('width' => 45, 'height' => 45, 'prefix' => 'small/', 'suffix' => '', 'force' => true, 'force-size' => true),
        array('width' => 100, 'height' => 100, 'prefix' => 'full/', 'suffix' => '', 'force' => true),
    );

    /**
     * Takes the dimensions of an image and resizes them to a
     * maximum width and height, maintaining the aspect ratio
     * @param int $width The width of the current image
     * @param int $height The height of the current image
     * @param int $max_width The maximum width of the resized image
     * @param int $max_height The maximum height of the resized image
     * @return array the new width and height values to resize to
     */
    static function GetResizeDimensions($width, $height, $max_width, $max_height)
    {
        $xr = $max_width / $width;
        $yr = $max_height / $height;
        if ($width <= $max_width && $height <= $max_height) return array($width, $height); // No resize needed
        else if ($width <= $max_width || $yr < $xr) return array(ceil($yr * $width), $max_height); // Too high, scale width to keep aspect ratio
        else return array($max_width, ceil($xr * $height)); // Too wide, scale height to keep aspect ratio
    }

    static function MakeThumbnails(string $image_location, array $image_sizes, string $destination_folder, string $filename, bool $delete_existing = false, bool $force_lossy_compression = false)
    {
        if (count($image_sizes) == 0) $image_sizes = Image::$vault_image_sizes;
        $image = new Image($image_location);
        $ret = array();
        foreach ($image_sizes as $size) {
            $w = $size['width'];
            $h = $size['height'];
            if ($w >= $image->Width() && $h >= $image->Height() && (!isset($size['force']) || $size['force'] !== true)) {
                $ret[] = '';
                continue;
            }
            $pre = !isset($size['prefix']) || $size['prefix'] == null ? '' : $size['prefix'];
            $suf = !isset($size['suffix']) || $size['suffix'] == null ? '' : $size['suffix'];
            $output_format = $image->DetermineBestOutputFormat($force_lossy_compression);
            $extension = self::OUTPUT_FILE_EXTENSIONS[$output_format];
            $name = $pre . pathinfo($filename, PATHINFO_BASENAME) . $suf . '.' . $extension;
            $save = rtrim($destination_folder, '/') . '/' . $name;
            if ($delete_existing && file_exists($save)) {
                unlink($save);
            }
            $dir = pathinfo($save, PATHINFO_DIRNAME);
            if (!is_dir($dir)) {
                mkdir($dir, 0777, true);
            }
            if (!file_exists($save)) {
                $image->SaveResized($save, $output_format, $w, $h, isset($size['force-size']) && $size['force-size'] === true);
            }
            $ret[] = $name;
        }
        $image->destroy();
        return $ret;
    }
}
