<?php
/*
 * @version		$Id: images.php 050 2013-03-21 11:27:00Z Anton Wintergerst $
 * @package     JiCustomFields 2.0 Framework for Joomla
 * @copyright   Copyright (C) 2013 Jinfinity. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @website     www.jinfinity.com
 * @email       support@jinfinity.com
*/

// No direct access
defined('_JEXEC') or die;

class JinfinityImagesHelper {
	function ResizeCropped($source, $dest, $thumbnail_width, $thumbnail_height) {
		// User forgot to enter a width and height, set a default to catch this
        if($thumbnail_width==null && $thumbnail_height==null) {
            $thumbnail_width = 200;
            $thumbnail_height = 200;
        }
		// Get Source Info
		list($width_orig, $height_orig, $type) = getimagesize($source);  
		// Calculate Source Ratio
		$ratio_orig = $width_orig/$height_orig;
		
		// Calculate Scaling for Destination
		if($thumbnail_width/$thumbnail_height > $ratio_orig) {
		   $new_height = $thumbnail_width/$ratio_orig;
		   $new_width = $thumbnail_width;
		} else {
		   $new_width = $thumbnail_height*$ratio_orig;
		   $new_height = $thumbnail_height;
		}
		// Find X Center
		$x_mid = $new_width/2;
		// Find Y Center
		$y_mid = $new_height/2;
	   
		switch ($type) {
			// GIF
			case 1:
				$image_p = imagecreatetruecolor(round($new_width), round($new_height));
				// preserve transparency
				imagecolortransparent($image_p, imagecolorallocatealpha($image_p, 255, 255, 255, 127));
		    	imagealphablending($image_p, false);
		    	imagesavealpha($image_p, true);
				// Setup Canvas
		    	$canvas = imagecolorallocate($image_p, 255, 255, 255);
		    	imagefilledrectangle($image_p, 0, 0, $new_width, $new_height, $canvas);
				
				$image = imagecreatefromgif($source);
				imagecopyresampled($image_p, $image, 0, 0, 0, 0, $new_width, $new_height, $width_orig, $height_orig);
				
				$thumb = imagecreatetruecolor($thumbnail_width, $thumbnail_height);
				// preserve transparency
				imagecolortransparent($thumb, imagecolorallocatealpha($thumb, 255, 255, 255, 127));
		    	imagealphablending($thumb, false);
		    	imagesavealpha($thumb, true);
				
				imagecopyresampled($thumb, $image_p, 0, 0, ($x_mid-($thumbnail_width/2)), ($y_mid-($thumbnail_height/2)), $thumbnail_width, $thumbnail_height, $thumbnail_width, $thumbnail_height);
				// Save Image
				//imagejpeg($thumb, $dest, 75);
				imagegif($thumb, $dest);
			break;
			// JPG
			case 2:
				$image_p = imagecreatetruecolor(round($new_width), round($new_height));
				$image = imagecreatefromjpeg($source);
				imagecopyresampled($image_p, $image, 0, 0, 0, 0, $new_width, $new_height, $width_orig, $height_orig);
				
				$thumb = imagecreatetruecolor($thumbnail_width, $thumbnail_height);
				imagecopyresampled($thumb, $image_p, 0, 0, ($x_mid-($thumbnail_width/2)), ($y_mid-($thumbnail_height/2)), $thumbnail_width, $thumbnail_height, $thumbnail_width, $thumbnail_height);
				// Save Image
				imagejpeg($thumb, $dest, 85);	
			break;
			// PNG
			case 3:
				$image_p = imagecreatetruecolor(round($new_width), round($new_height));
				// preserve transparency
				imagecolortransparent($image_p, imagecolorallocatealpha($image_p, 255, 255, 255, 127));
		    	imagealphablending($image_p, false);
		    	imagesavealpha($image_p, true);
		    	// Setup Canvas
		    	$canvas = imagecolorallocate($image_p, 255, 255, 255);
		    	imagefilledrectangle($image_p, 0, 0, $new_width, $new_height, $canvas);
				$image = imagecreatefrompng($source);
				
				imagecopyresampled($image_p, $image, 0, 0, 0, 0, $new_width, $new_height, $width_orig, $height_orig);
				
				$thumb = imagecreatetruecolor($thumbnail_width, $thumbnail_height);
				// preserve transparency
				imagecolortransparent($thumb, imagecolorallocatealpha($thumb, 255, 255, 255, 127));
		    	imagealphablending($thumb, false);
		    	imagesavealpha($thumb, true);
				
				imagecopyresampled($thumb, $image_p, 0, 0, ($x_mid-($thumbnail_width/2)), ($y_mid-($thumbnail_height/2)), $thumbnail_width, $thumbnail_height, $thumbnail_width, $thumbnail_height);
				// Save Image
				//imagejpeg($thumb, $dest, 75);
				imagepng($thumb, $dest, 1);
			break;			
		}
		// Remove Image from Memory
		imagedestroy($image_p);
	}
    function ResizeKeepAspect($source, $dest, $width, $height, $quality=85) {
        // Get Source Info
        list($width_orig, $height_orig, $type) = getimagesize($source);
        $ratio_orig = $width_orig/$height_orig; 
        if($width/$height == $ratio_orig) {
            // Same ratio
        } else if ($width/$height > $ratio_orig) {
            // Wider image
            $height = $width/$ratio_orig;
        } else {
            // Taller image
            $width = $height*$ratio_orig;
        }
        // Resample
        $image_p = imagecreatetruecolor($width, $height);
        switch ($type) {
            // GIF
            case 1:
                $thumb = imagecreatefromgif($source);
                imagecopyresampled($image_p, $thumb, 0, 0, 0, 0, $width, $height, $width_orig, $height_orig);
                // Output
                imagegif($image_p, $dest);
            break;
            // JPG
            case 2:
                $thumb = imagecreatefromjpeg($source);
                imagecopyresampled($image_p, $thumb, 0, 0, 0, 0, $width, $height, $width_orig, $height_orig);
                // Output
                imagejpeg($image_p, $dest, $quality);      
            break;
            // PNG
            case 3:
                $thumb = imagecreatefrompng($source);
                imagecopyresampled($image_p, $thumb, 0, 0, 0, 0, $width, $height, $width_orig, $height_orig);
                // Output
                imagepng($image_p, $dest, 1);
            break;
        }
        // Remove Image from Memory
        imagedestroy($image_p);
    }
	function CustomCrop($source, $dest, $p1, $p2) {
		// Input points are percentages relative to image size
		$p1s = explode(',', $p1);
		$p2s = explode(',', $p2);
		// Find Percentage Sizes
		$p1x = round($p1s[0]);
		$p1y = round($p1s[1]);
		$p2x = round($p2s[0]);
		$p2y = round($p2s[1]);
		// Get Source Info
		list($width_orig, $height_orig, $type) = getimagesize($source);  
		// Real Sizes
		$p1x = round(($width_orig/100)*$p1x);
		$p1y = round(($height_orig/100)*$p1y);
		$p2x = round(($width_orig/100)*$p2x);
		$p2y = round(($height_orig/100)*$p2y);
		$thumbnail_width = abs(round($p2x-$p1x));
		$thumbnail_height = abs(round($p2y-$p1y));
		switch ($type) {
			// GIF
			case 1:
				$image_p = imagecreatetruecolor($width_orig, $height_orig);
				// preserve transparency
				imagecolortransparent($image_p, imagecolorallocatealpha($image_p, 255, 255, 255, 127));
		    	imagealphablending($image_p, false);
		    	imagesavealpha($image_p, true);
				// Setup Canvas
		    	$canvas = imagecolorallocate($image_p, 255, 255, 255);
		    	imagefilledrectangle($image_p, 0, 0, $width_orig, $height_orig, $canvas);
				
				$image = imagecreatefromgif($source);
				imagecopyresampled($image_p, $image, 0, 0, 0, 0, $width_orig, $height_orig, $width_orig, $height_orig);
				
				$thumb = imagecreatetruecolor($thumbnail_width, $thumbnail_height);
				// preserve transparency
				imagecolortransparent($thumb, imagecolorallocatealpha($thumb, 255, 255, 255, 127));
		    	imagealphablending($thumb, false);
		    	imagesavealpha($thumb, true);
				
				imagecopyresampled($thumb, $image_p, 0, 0, $p1x, $p1y, $thumbnail_width, $thumbnail_height, $thumbnail_width, $thumbnail_height);
				// Save Image
				//imagejpeg($thumb, $dest, 75);
				imagegif($thumb, $dest);
			break;
			// JPG
			case 2:
				$image_p = imagecreatetruecolor($width_orig, $height_orig);
				$image = imagecreatefromjpeg($source);
				imagecopyresampled($image_p, $image, 0, 0, 0, 0, $width_orig, $height_orig, $width_orig, $height_orig);
				
				$thumb = imagecreatetruecolor($thumbnail_width, $thumbnail_height);
				imagecopyresampled($thumb, $image_p, 0, 0, $p1x, $p1y, $thumbnail_width, $thumbnail_height, $thumbnail_width, $thumbnail_height);
				// Save Image
				imagejpeg($thumb, $dest, 85);	
			break;
			// PNG
			case 3:
				$image_p = imagecreatetruecolor($width_orig, $height_orig);
				// preserve transparency
				imagecolortransparent($image_p, imagecolorallocatealpha($image_p, 255, 255, 255, 127));
		    	imagealphablending($image_p, false);
		    	imagesavealpha($image_p, true);
		    	// Setup Canvas
		    	$canvas = imagecolorallocate($image_p, 255, 255, 255);
		    	imagefilledrectangle($image_p, 0, 0, $width_orig, $height_orig, $canvas);
				$image = imagecreatefrompng($source);
				
				imagecopyresampled($image_p, $image, 0, 0, 0, 0, $width_orig, $height_orig, $width_orig, $height_orig);
				
				$thumb = imagecreatetruecolor($thumbnail_width, $thumbnail_height);
				// preserve transparency
				imagecolortransparent($thumb, imagecolorallocatealpha($thumb, 255, 255, 255, 127));
		    	imagealphablending($thumb, false);
		    	imagesavealpha($thumb, true);
				
				imagecopyresampled($thumb, $image_p, 0, 0, $p1x, $p1y, $thumbnail_width, $thumbnail_height, $thumbnail_width, $thumbnail_height);
				// Save Image
				//imagejpeg($thumb, $dest, 75);
				imagepng($thumb, $dest, 1);
			break;			
		}
		// Remove Image from Memory
		imagedestroy($image_p);
	}
}