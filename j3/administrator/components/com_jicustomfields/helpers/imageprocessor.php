<?php
/**
 * @version     $Id: imageprocessorr.php 145 2014-03-11 11:36:00Z Anton Wintergerst $
 * @package     JiCustomFields 2.0 Framework
 * @copyright   Copyright (C) 2014 Jinfinity. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @website     www.jinfinity.com
 * @email       support@jinfinity.com
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

class JiCustomFieldsImageProcessor {
    public $errormsg = '';
    public function resizeImage($oldfile, $newfile, $params, $type, $pre, $debug=0) {
        if(file_exists($oldfile)) {
            if($params->get('filesizecheck', 0)==1) {
                if(filesize($oldfile)>$params->get('filesizechecklimit', 30000)) {
                    if($debug) $this->errormsg = 'Error: File size exceeds maximum limit';
                    return false;
                }
            }
            $thumb_quality = $params->get($pre.'_thumbs_quality', 75);
            $thumbnail_width = $params->get($pre.'_thumbs_width', null);
            $thumbnail_height = $params->get($pre.'_thumbs_height', null);
            // User forgot to enter a width and height, set a default to catch this
            if($thumbnail_width==null && $thumbnail_height==null) {
                $thumbnail_width = 200;
                $thumbnail_height = 200;
            }
            // Prepare to resize
            $resize_orig = $params->get('images_resize', 0);
            $image_quality = $params->get('images_quality', 75);
            $maxwidth = $params->get('images_width', 800);
            $maxheight = $params->get('images_height', 500);
            $p1x = 0;
            $p1y = 0;


            // Get Original Dimensions
            $imageInfo = getimagesize($oldfile);
            $width_orig = $imageInfo[0];
            $height_orig = $imageInfo[1];
            $type = $imageInfo[2];

            $ratio_orig = $width_orig/$height_orig;
            if($thumbnail_width==null) $thumbnail_width = $thumbnail_height*$ratio_orig;
            if($thumbnail_height==null) $thumbnail_height = $thumbnail_width/$ratio_orig;

            if($params->get($pre.'_thumbs_keepratio', 1)==1) {
                // Calculate ratio for resizing thumbnail image
                if($thumbnail_width/$thumbnail_height==$ratio_orig) {
                    // Image size matches desired thumb size
                    $newwidth = $thumbnail_width;
                    $newheight = $thumbnail_height;
                } elseif($thumbnail_width/$thumbnail_height < $ratio_orig) {
                    // Image size is wider than desired thumb size
                    $newwidth = $thumbnail_height*$ratio_orig;
                    $newheight = $thumbnail_height;
                } else {
                    // Image size is taller than desired thumb size
                    $newwidth = $thumbnail_width;
                    $newheight = $thumbnail_width/$ratio_orig;
                }
            } else {
                // Don't alter images for aspect ratio
                $newwidth = $thumbnail_width;
                $newheight = $thumbnail_height;
            }
            if($params->get($pre.'_thumbs_fill', 1)==0) {
                if($thumbnail_width<$newwidth) {
                    $newwidth = $thumbnail_width;
                    $newheight = $thumbnail_width/$ratio_orig;
                }
            }
            if($params->get($pre.'_thumbs_cropcenter', 1)==1) {
                // Calculate ratio for resizing thumbnail image
                if($thumbnail_width/$thumbnail_height < $ratio_orig) {
                    $p1x = floor(abs($newwidth - $thumbnail_width)/2);
                    $p1y = 0;
                } else {
                    $p1x = 0;
                    $p1y = floor(abs($newheight - $thumbnail_height)/2);
                }
            } else {
                $p1x = 0;
                $p1y = 0;
            }
            // Y Offset
            if($params->get($pre.'_thumbs_ycenter', 1)==1) {
                $p2y = floor(($thumbnail_height - $newheight)/2);
            } else {
                $p2y = 0;
            }
            // X Offset
            if($params->get($pre.'_thumbs_xcenter', 1)==1) {
                $p2x = floor(($thumbnail_width - $newwidth)/2);
            } else {
                $p2x = 0;
            }

            if ($params->get($pre.'_images_keepratio', 1) == 1 && $resize_orig == 1) {
                // Calculate ratio for resizing original image
                if($thumbnail_width/$thumbnail_height==$ratio_orig) {
                    // Image size matches desired size
                } elseif($maxwidth/$maxheight > $ratio_orig) {
                    // Image size is wider than desired size
                    $maxwidth = $maxheight*$ratio_orig;
                } else {
                    // Image size is taller than desired size
                    $maxheight = $maxwidth/$ratio_orig;
                }
            }
            if($params->get($pre.'_thumbs_enlarge', 0)==0) {
                if($newwidth>$width_orig && $newheight>$height_orig) {
                    copy($oldfile, $newfile);
                    return true;
                }
            }
            if($params->get('memcheck', 1)==1) {
                // Check memory required
                if(!isset($imageInfo['bits'])) $imageInfo['bits'] = 8;
                if(!isset($imageInfo['channels'])) $imageInfo['channels'] = 4;
                $memoryNeeded = round(($imageInfo[0] * $imageInfo[1] * $imageInfo['bits'] * $imageInfo['channels']) + ($thumbnail_width*$thumbnail_height*8*4));
                // Check memory is available first
                $memoryCheck = $this->memoryCheck($memoryNeeded, $params->get('thumbs_memincrease', false));
                if($memoryCheck==false) {
                    // Too much memory required for this file
                    // Try downsizing the image using exec first
                    exec('convert -limit memory 16mb -limit map 32mb -define registry:temporary-path="'.JPATH_SITE.DS.'tmp" "'.$oldfile.'" -thumbnail "1000x1000" "'.$newfile.'"', $result, $error);
                    if(file_exists($newfile)) {
                        $oldfile = $newfile;
                        $imageInfo = getimagesize($oldfile);
                        $width_orig = $imageInfo[0];
                        $height_orig = $imageInfo[1];
                        $type = $imageInfo[2];
                    } else {
                        if($debug) $this->errormsg = 'Error: Not enough memory to process image!';
                        return false;
                    }
                }
            }

            switch ($type) {
                case '1':
                case 'gif':
                    // Create Canvas
                    $canvas = imagecreatetruecolor(round($thumbnail_width), round($thumbnail_height));
                    // Preserve transparency
                    //imagecolortransparent($canvas, imagecolorallocatealpha($canvas, 255, 255, 255, 127));
                    imagealphablending($canvas, false);
                    imagesavealpha($canvas, true);
                    // Draw Background
                    $background = imagecolorallocatealpha($canvas, 255, 255, 255, 127);
                    imagefilledrectangle($canvas, 0, 0, round($thumbnail_width), round($thumbnail_height), $background);
                    // Load Original
                    $original = imagecreatefromgif($oldfile);
                    // Draw into Canvas
                    imagecopyresampled($canvas, $original, $p2x, $p2y, $p1x, $p1y, $newwidth, $newheight, $width_orig, $height_orig);
                    // Save Thumbnail
                    imagepng($canvas, $newfile, 1);
                    // Remove canvas from memory
                    imagedestroy($canvas);

                    if($resize_orig==1) {
                        // Resize Original Image if too large
                        if ($width_orig > $maxwidth || $height_orig > $maxheight) {
                            // Resample
                            $canvas = imagecreatetruecolor($maxwidth, $maxheight);
                            // Resize
                            imagecopyresampled($canvas, $original, 0, 0, 0, 0, $maxwidth, $maxheight, $width_orig, $height_orig);
                            // Save Original
                            imagegif($canvas, $oldfile);
                            // Remove canvas from memory
                            imagedestroy($canvas);
                        }
                    }
                    // Remove original from memory
                    imagedestroy($original);
                    break;
                case '2':
                case 'jpeg':
                case 'jpg':
                    // Create Canvas
                    $canvas = imagecreatetruecolor(round($thumbnail_width), round($thumbnail_height));
                    // Preserve Transparency
                    imagealphablending($canvas, false);
                    imagesavealpha($canvas, true);
                    // Draw Background
                    $background = imagecolorallocatealpha($canvas, 255, 255, 255, 127);
                    imagefilledrectangle($canvas, 0, 0, round($thumbnail_width), round($thumbnail_height), $background);


                    // Load Original
                    $original = imagecreatefromjpeg($oldfile);
                    // Resize
                    imagecopyresampled($canvas, $original, $p2x, $p2y, $p1x, $p1y, $newwidth, $newheight, $width_orig, $height_orig);
                    // Save Thumbnail
                    imagepng($canvas, $newfile, 1);
                    //imagejpeg($canvas, $newfile, $thumb_quality);
                    // Remove canvas from memory
                    imagedestroy($canvas);
                    if($resize_orig==1) {
                        // Resize Original Image if too large
                        if ($width_orig > $maxwidth || $height_orig > $maxheight) {
                            // Create Canvas
                            $canvas = imagecreatetruecolor($maxwidth, $maxheight);
                            // Resize
                            imagecopyresampled($canvas, $original, 0, 0, 0, 0, $maxwidth, $maxheight, $width_orig, $height_orig);
                            // Save Original
                            imagejpeg($canvas, $oldfile, $image_quality);
                            // Remove canvas from memory
                            imagedestroy($canvas);
                        }
                    }
                    // Remove original from memory
                    imagedestroy($original);
                    break;
                case '3':
                case 'png':
                    // Create Canvas
                    $canvas = imagecreatetruecolor(round($thumbnail_width), round($thumbnail_height));
                    // Load Original
                    $original = imagecreatefrompng($oldfile);
                    // Preserve transparency
                    //imagecolortransparent($canvas, imagecolorallocatealpha($canvas, 255, 255, 255, 127));
                    imagealphablending($canvas, false);
                    imagesavealpha($canvas, true);
                    // Draw Background
                    $background = imagecolorallocatealpha($canvas, 255, 255, 255, 127);
                    imagefilledrectangle($canvas, 0, 0, round($thumbnail_width), round($thumbnail_height), $background);
                    // Resize
                    imagecopyresampled($canvas, $original, $p2x, $p2y, $p1x, $p1y, $newwidth, $newheight, $width_orig, $height_orig);
                    // Save Thumbnail
                    imagepng($canvas, $newfile, 1);
                    // Remove canvas from memory
                    imagedestroy($canvas);

                    if($resize_orig==1) {
                        // Resize Original Image if too large
                        if ($width_orig > $maxwidth || $height_orig > $maxheight) {
                            // Resample
                            $canvas = imagecreatetruecolor($maxwidth, $maxheight);
                            // Resize
                            imagecopyresampled($canvas, $original, 0, 0, 0, 0, $maxwidth, $maxheight, $width_orig, $height_orig);
                            // Save Original
                            imagepng($canvas, $oldfile, 1);
                            // Remove canvas from memory
                            imagedestroy($canvas);
                        }
                    }
                    // Remove original from memory
                    imagedestroy($original);
                    break;
            }
            return true;
        }
        if($debug) $this->errormsg = 'Error: Image file does not exist!';
        return false;
    }
    public function memoryCheck($memoryNeeded, $increase=true) {
        // Check memory required
        $memoryUsed = memory_get_usage(true);
        //$imageInfo = getimagesize($file);
        //$memoryNeeded = round(($imageInfo[0] * $imageInfo[1] * $imageInfo['bits'] * $imageInfo['channels']) + $offset);
        //$memoryNeeded = round(($imageInfo[0] * $imageInfo[1] * $imageInfo['bits'] * $imageInfo['channels'] / 8 + Pow(2, 16)) * 1.65);

        $memoryLimit = (int) ini_get('memory_limit')*1048576;
        $memoryAvailable = $memoryLimit - $memoryUsed;

        if((int) $memoryAvailable > (int) $memoryNeeded) {
            // sufficient memory is available
            $response = true;
        } else {
            $response = false;
            // not enough memory is available
            if($increase==true) {
                // try to increase memory limit
                ini_set('memory_limit', ceil(($memoryNeeded - $memoryAvailable + $memoryLimit)/1048576).'M');
                // check if memory has actually increased
                $memoryLimit = (int) ini_get('memory_limit')*1048576;

                if(($memoryLimit - $memoryUsed) > $memoryNeeded) {
                    // sufficient memory is now available
                    $response = true;
                }
            }
            // not enough memory to proceed
            return $response;
        }
        return $response;
    }
}