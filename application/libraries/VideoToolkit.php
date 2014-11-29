<?php

/**
 * CodeIgnighter layout support library
 *  with Twig like inheritance blocks
 *
 * v 1.0
 *
 *
 * @author Constantin Bosneaga
 * @email  constantin@bosneaga.com
 * @url    http://a32.me/
 */
if (!defined('BASEPATH'))
	exit('No direct script access allowed');

 
class VideoToolkit {
	
	public function createThumbnailFromVideo($videoPath, $outputDir, $options = null, &$errors = ''){
		if (!$options) {
			$options = array();
		}
		$width = isset($options['width']) ?  $options['width'] : 650;
		$height = isset($options['height']) ? $options['height'] : 315;
		$movie = new ffmpeg_movie($videoPath);
		$frameCount = $movie->getFrameCount();
		$videoName = array_pop(explode('/', $videoPath));
		$out = $outputDir.$videoName.'-thumb-'.uniqid().'.jpg';
		$thumb = 1;
		$size = 0;
		$step = 3;
		for($i=1; $i<$frameCount; $i+=$step) {
			$frame = $movie->getFrame($i);
			if($frame){
				$tmpSize=$this->getGdImageSize($frame->toGDImage());
				if($tmpSize > $size){
					$size = $tmpSize;
					$thumb = $i;
				}
			}
				
		}
		$thumbGd = $movie->getFrame($thumb)->toGDImage();
		imagejpeg($thumbGd, $out);
		return $out;
	}
	
	private function getGdImageSize($gdImage) {
		ob_start(); 
		imagejpeg($gdImage); 

		$output = ob_get_contents(); 

		ob_end_clean(); 
		return strlen($output); 
	}
}
