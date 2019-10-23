<?php

namespace App\Helpers;

class Image
{
    private $_info;
    private $_image;

    function __construct($image_location)
    {
        $this->_info = getimagesize($image_location);
        if ($this->_info[2] == IMAGETYPE_JPEG) {
            $this->_image = imagecreatefromjpeg($image_location);
        } else if ($this->_info[2] == IMAGETYPE_PNG) {
            $this->_image = imagecreatefrompng($image_location);
            imagealphablending($this->_image, true);
        }
        else $this->_image = null;
    }

    /**
     * @return int
     */
    function Width()
    {
        return $this->_info[0];
    }

    /**
     * @return int
     */
    function Height()
    {
        return $this->_info[1];
    }

    function SaveResized($location, $max_width, $max_height, $force_size = false, $image_type = false)
    {
        if ($this->_image === null) return;
        $new_dims = Image::GetResizeDimensions($this->Width(), $this->Height(), $max_width, $max_height);
        $actual_dims = $force_size ? array($max_width, $max_height) : $new_dims;
        $new_image = imagecreatetruecolor($actual_dims[0], $actual_dims[1]);
        if ($this->_info[2] == IMAGETYPE_PNG) {
            imagealphablending($new_image, false);
            $transparent = imagecolorallocatealpha($new_image, 0, 0, 0, 127);
            imagefill($new_image, 0, 0, $transparent);
            imagesavealpha($new_image, true);
        } else {
            imagefill($new_image, 0, 0, imagecolorallocate($new_image, 255, 255, 255));
        }
        // Ubuntu doesn't include a version of GD that supports imageantialias
        if (function_exists('imageantialias')) {
            imageantialias($new_image, true);
        }
        $dx = ($actual_dims[0] - $new_dims[0]) / 2;
        $dy = ($actual_dims[1] - $new_dims[1]) / 2;
        imagecopyresampled($new_image, $this->_image, $dx, $dy, 0, 0, $new_dims[0], $new_dims[1], $this->Width(), $this->Height());
        if (!$image_type) $image_type = $this->_info[2];
        if ($image_type == IMAGETYPE_JPEG) {
            imagejpeg($new_image, $location, 80);
        } else if ($image_type == IMAGETYPE_PNG) {
            imagepng($new_image, $location);
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

    static function MakeThumbnails($image_location, $image_sizes = array(), $destination_folder = null, $filename = null, $delete_existing = false)
    {
        $info = pathinfo($image_location);
        if ($filename !== null) $info = pathinfo($info['dirname'] . '/' . $filename);
        if ($destination_folder === null) $destination_folder = $info['dirname'];
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
            $name = $pre . $info['filename'] . $suf . '.' . $info['extension'];
            $save = rtrim($destination_folder, '/') . '/' . $name;
            if ($delete_existing && file_exists($save)) {
                unlink($save);
            }
            $dir = pathinfo($save, PATHINFO_DIRNAME);
            if (!is_dir($dir)) {
                mkdir($dir, 0777, true);
            }
            if (!file_exists($save)) {
                $image->SaveResized($save, $w, $h, isset($size['force-size']) && $size['force-size'] === true, isset($size['type']) ? $size['type'] : false);
            }
            $ret[] = $name;
        }
        $image->destroy();
        return $ret;
    }
}
