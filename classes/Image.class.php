<?php
/**
 * Class to handle images.
 *
 * @author     Lee Garner <lee@leegarner.com>
 * @copyright  Copyright (c) 2012-2018 Lee Garner <lee@leegarner.com>
 * @package    lglib
 * @version    1.0.9
 * @license    http://opensource.org/licenses/gpl-2.0.php
 *              GNU Public License v2 or later
 * @filesource
 */
namespace LGLib;

/**
 *  Image-handling class
 */
class Image
{
    /**
     * Calculate dimensions to resize an image, preserving the aspect ratio.
     *
     * @param   string  $origpath   Original image path
     * @param   integer $width      New width, in pixels
     * @param   integer $height     New height, in pixels
     * @param   boolean $expand     True to allow expanding the image
     * @return  mixed       array of dimensions, or false on error
     */
    public static function reDim($orig_path, $width=0, $height=0, $expand=false)
    {
        $dimensions = @getimagesize($orig_path);
        if ($dimensions === false) {
            return false;
        }

        $s_width = $dimensions[0];
        $s_height = $dimensions[1];
        $mime_type = $dimensions['mime'];

        // get both sizefactors that would resize one dimension correctly
        if ($width > 0 && ($s_width > $width || $expand)) {
            $sizefactor_w = (double)($width / $s_width);
        } else {
            $sizefactor_w = 1;
        }
        if ($height > 0 && ($s_height > $height || $expand)) {
            $sizefactor_h = (double)($height / $s_height);
        }else {
            $sizefactor_h = 1;
        }

        // Use the smaller factor to stay within the parameters
        $sizefactor = min($sizefactor_w, $sizefactor_h);

        $newwidth = (int)($s_width * $sizefactor);
        $newheight = (int)($s_height * $sizefactor);

        return array(
            's_width'   => $s_width,
            's_height'  => $s_height,
            'd_width'   => $newwidth,
            'd_height'  => $newheight,
            'mime'      => $mime_type,
        );
    }


    /**
     * Resize an image to the specified dimensions into the new location.
     * At least one of $newWidth or $newHeight must be specified.
     *
     * @param   string  $type       Either 'thumb' or 'disp'
     * @param   integer $newWidth   New width, in pixels
     * @param   integer $newHeight  New height, in pixels
     * @param   boolean $expand     True to allow expanding the image
     * @return  mixed   Array of new width,height if successful, false if failed
     */
    public static function reSize($src, $dst, $newWidth=0, $newHeight=0, $expand=false)
    {
        // Calculate the new dimensions
        $A = self::reDim($src, $newWidth, $newHeight, $expand);
        if ($A === false) {
            COM_errorLog(__CLASS__ . ": Invalid image $src");
            return false;
        }

        // Returns an array, with [0] either true/false and [1]
        // containing a message.
        if (function_exists('_img_resizeImage')) {
            $result = _img_resizeImage(
                $src, $dst,
                $A['s_height'], $A['s_width'],
                $A['d_height'], $A['d_width'],
                $A['mime']
            );
        } else {
            $result = array(false);
        }

        if ($result[0] === false) {
            COM_errorLog(__CLASS__ . ": Failed to convert $src ({$A['s_height']} x {$A['s_width']}) to $dst ($newHeight x $newWidth)");
            return false;
        } else {
            return $A;
        }
    }

}
