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

 
class Picture {
	
	private $m_img; # ressource image pour travailler
	private $m_width;	# dimensions dÃ©sirÃ©es
	private $m_height;
	private $m_source_img; # source
	private $m_source_width;
	private $m_source_height;
	private $m_imagetype;
	private $m_size; # Ko
	private $m_filename;
	private $m_cropped;

	public static function isPicture( $filename )
	{
		return( self::getImageType( $filename )!= null );
	}

	static private function getImageType($image)
	{
		$handle   = fopen($image, 'r');
		$contents = fread($handle, 12);
		fclose($handle);
			
		$_0_8  = substr($contents, 0, 8);
		$_0_4  = substr($contents, 0, 4);
		$_6_4  = substr($contents, 6, 4);
		$_20_4 = substr($contents, 20, 4);
			
		if ($_0_4 == "MM\x00\x2A" || $_0_4 == "II\x2A\x00") {
			return 'tif';
		}
			
		if ($_0_8 == "\x89PNG\x0D\x0A\x1A\x0A") {
			return 'png';
		}
			
		if ($_0_4 == 'GIF8') {
			return 'gif';
		}
			
		if ($_6_4 == 'JFIF' || $_6_4 == 'Exif' || ($_0_4 == "\xFF\xD8\xFF\xED" && $_20_4 == "8BIM")) {
			return 'jpg';
		}
			
		return NULL;
	}

	/**
	 * constructeur
	 * @param string $filename
	 */
	public function __construct()
	{
		
	}

	public function init($params =null) {
		$this->m_img = NULL;
		$this->m_width = NULL;
		$this->m_height = NULL;
		$this->m_source_img = NULL;
		$this->m_source_width = NULL;
		$this->m_source_height = NULL;
		$this->m_imagetype = NULL;
		$this->m_size = 0;
		$this->m_filename = null;
		$this->m_cropped = false;
		if (isset($params['filename'])) {
			$this->setSource($params['filename'], isset($params['remote']) ? $params['remote'] : false );
		}
	}

	/**
	 * retourne une ressource image selon le type de fichier
	 * @param string $type
	 * @param string $filename
	 */
	protected function imagecreatefrom( $type, $filename )
	{
		
		switch( $type )
		{
			case 1:
				return imagecreatefromgif( $filename );
				break;
			case 2:
				return imagecreatefromjpeg( $filename );
				break;
			case 3:
				return imagecreatefrompng( $filename );
				break;
			default:
				return null;
		}
	}


	/**
	 * dÃ©finition de l'image source
	 * @param string $photo_path
	 */
	public function setSource( $photo_path, $remote )
	{
		list( $width, $height, $type, $attr ) = @getimagesize( $photo_path );
		
		$this->m_imagetype = $type;
		$this->m_filename = $photo_path;
		$this->m_source_width = $width;
		$this->m_source_height = $height;
		$this->m_width = $this->m_source_width;
		$this->m_height = $this->m_source_height;
		if(!$remote)$this->m_size = filesize( $photo_path );
		$this->m_source_img = $this->imagecreatefrom( $type, $photo_path );
	}

	/**
	 * permet de savoir si l'image a Ã©tÃ© redimensionnÃ©e
	 * @return bool
	 */
	public function resized()
	{
		return( $this->m_source_width != $this->m_width ||
				$this->m_source_height != $this->m_height );
	}


	/**
	 * changement de la largeur
	 * @param number $width # la largeur souhaitÃ©e
	 * @param bool $keep_aspect_ratio
	 * @return number # le coefficient
	 */
	public function setWidth( $width, $keep_aspect_ratio = false )
	{
		$coeff = 1;
		if( $keep_aspect_ratio )
		{
			$coeff = $width / $this->m_source_width;
			$this->m_height = $coeff * $this->m_source_height;
		}
		$this->m_width = $width;
		$this->create();
		return( $coeff );
	}

	/**
	 * crÃ©ation de l'image finale en tant que ressource
	 * @param bool destruction de l'image de travail en cours
	 */
	private function create( $destroy = true )
	{
		if( $destroy && isset( $this->m_img ) ) imagedestroy( $this->m_img );
		$dst_img = imagecreatetruecolor( ceil($this->m_width), ceil($this->m_height) );
		$background = imagecolorallocate($dst_img, 0, 0, 0);
		imagecolortransparent($dst_img, $background);
		imagealphablending($dst_img,FALSE);
		imagesavealpha($dst_img,TRUE);
		imagecopyresampled( $dst_img, $this->m_source_img, 0, 0, 0, 0, ceil($this->m_width), ceil($this->m_height), $this->m_source_width, $this->m_source_height );
		$this->m_img = $dst_img;
	}


	/**
	 * changement de la hauteur
	 * @param number $height # la hauteur souhaitÃ©e
	 * @param bool $keep_aspect_ratio
	 * @return number # le coefficient
	 */
	public function setHeight( $height, $keep_aspect_ratio = false )
	{
		$coeff = 1;
		if( $keep_aspect_ratio )
		{
			$coeff = $height / $this->m_source_height;
			$this->m_width = $coeff * $this->m_source_width;
		}
		$this->m_height = $height;
		$this->create();
		return( $coeff );
	}


	/**
	 * fonctions d'accession
	 */
	public function getWidth()
	{
		return $this->m_width;
	}


	/**
	 * fonctions d'accession
	 */
	public function getHeight()
	{
		return $this->m_height;
	}


	/**
	 * fonctions d'accession
	 */
	public function getFilename()
	{
		return( basename( $this->m_filename ) );
	}


	/**
	 * fonctions d'accession
	 */
	public function getSize()
	{
		return $this->m_size;
	}


	/**
	 * dimension maximale (carrÃ©) dans laquelle doit s'inscrire l'image en gardant son aspect
	 * dans le meilleur des cas on aura une taille $max_width, $max_height
	 * @param number $max_width
	 * @param number $max_height
	 * @return number # le coefficient
	 */
	public function dimensionTo( $max_width, $max_height )
	{
		$max_width = isset( $max_width ) ? abs( $max_width ) : 99999;
		$max_height = isset( $max_height ) ? abs( $max_height ) : 99999;

		$r = $this->m_width / $this->m_height;


		if( $r >= 1 )
		{
			if( $max_width > $this->m_width && $max_height > $this->m_height ) return $r;
			if( $max_width > $max_height )
			{
				$this->m_width = $max_height * $r;
				$this->m_height = $max_height;
			}
			else
				if( $max_width <= $max_height )
				{
					$this->m_width = $max_width;
					$this->m_height = $max_width / $r;
				}
		}
		else
			if( $r < 1 )
			{
				$r = 1 / $r;
				if( $max_width > $this->m_width && $max_height > $this->m_height ) return $r;
				if( $max_width >= $max_height )
				{
					$this->m_width = $max_height / $r;
					$this->m_height = $max_height;
				}
				else
					if( $max_width < $max_height )
					{
						$this->m_width = $max_width;
						$this->m_height = $max_width * $r;
					}
			}

			return( $r );
	}


	/**
	 * dimension maximale (carrÃ©) dans laquelle doit s'inscrire l'image en gardant son aspect mais en respectant les dimensions de sortie
	 * quoi qu'il arrive on aura une taille $max_width, $max_height
	 * @param number $max_width
	 * @param number $max_height
	 * @return number # le coefficient
	 */
	public function cropTo( $max_width, $max_height )
	{
		$max_width = isset( $max_width ) ? abs( $max_width ) : $this->m_width;
		$max_height = isset( $max_height ) ? abs( $max_height ) : $this->m_height;
		$dst_img = imagecreatetruecolor( $max_width, $max_height );

		// ratio image origine
		$r = $this->m_width / $this->m_height;
	
		// ratio image finale
		$r2 = $max_width / $max_height;
		if( $r >= $r2 )
		{
			$this->m_width = $max_height * $r;
			$this->m_height = $max_height;
		}
		else
		{
			$this->m_width = $max_width;
			$this->m_height = $max_width / $r;
		}
		$this->create();
		//preserve transparency (again)
		$background = imagecolorallocate($dst_img, 0, 0, 0);
		imagecolortransparent($dst_img, $background);
		imagealphablending($dst_img,FALSE);
		imagesavealpha($dst_img,TRUE);
		imagecopy( $dst_img, $this->m_img, 0, 0, abs($this->m_width - $max_width) / 2, abs($this->m_height - $max_height) / 2, $max_width, $max_height );
		$this->m_img = $dst_img;
		$this->m_cropped = true;
		$this->m_width = $max_width;
		$this->m_height = $max_height;
	}


	/**
	 * sortie de l'image en mÃ©moire
	 * @param $quality # qualitÃ© de sortie
	 */
	public function toMemory( $quality=100 )
	{
		if( !$this->m_cropped ) $this->create();
		switch( $this->m_imagetype )
		{
			case 1:
				header( "Content-type: image/gif" );
				imagegif( $this->m_img );
				break;
			case 2:
				header( "Content-type: image/jpeg" );
				imagejpeg( $this->m_img, null, $quality );
				break;
			case 3:
				header( "Content-type: image/png" );
				imagepng( $this->m_img );
				break;
		}
		imagedestroy( $this->m_img );
	}


	/**
	 * sortie de l'image en fichier
	 * @param $atregt_filename # le fichier de sortie
	 * @param $quality # qualitÃ© de sortie
	 */
	public function toFile( $target_filename, $quality=100 )
	{
		$ret = true;
		if( $this->m_cropped ) {
			if( !$this->resized() ) {
				$ret = copy( $this->m_filename, $target_filename );
			} else {
				$ret = $this->image( $target_filename, $quality );
				imagedestroy( $this->m_img );
			}
		} else {
			if( !$this->resized() ) {
				$ret = copy( $this->m_filename, $target_filename );
			} else {
				$this->create();
				$ret = $this->image( $target_filename, $quality );
				imagedestroy( $this->m_img );
			}
		}
		return( $ret );
	}


	/**
	 * sortie de l'image en fichier (voir ci-dessus)
	 * @param $atregt_filename # le fichier de sortie
	 * @param $quality # qualitÃ© de sortie
	 */
	private function image( $target_filename=null, $quality=100 ){
		switch( $this->m_imagetype ){
			case 1:
				return imagegif( $this->m_img, $target_filename);
			case 2:
				return imagejpeg( $this->m_img, $target_filename);
			case 3:
				return imagepng( $this->m_img, $target_filename);
		}
	}

	public static function getPictureFilename( $filename, $width, $height, $mode = 'equal' ){
		$filename = str_ireplace( CACHE_ROOT, '', $filename );
		$filename = str_ireplace( FILE_ROOT, '', $filename );
		return CACHE_ROOT . base64_encode( $filename . $mode . $width . 'x' . $height );
	}

	public function getNewSize( $width, $height, $crop = false ){
		if( $crop ){
			$max_width = isset( $width ) ? abs( $width ) : $this->m_width;
			$max_height = isset( $height ) ? abs( $height ) : $this->m_height;
			if( $this->m_source_width >= $this->m_source_height ){
				$this->m_height = $max_height;
				$coeff = $max_height / $this->m_source_height;
				$this->m_width = $coeff * $this->m_source_width;
				if( $this->m_width < $max_width )
					$max_width = $this->m_width;
			} else {
				$this->m_width = $max_width;
				$coeff = $max_width / $this->m_source_width;
				$this->m_height = $coeff * $this->m_source_height;
				if( $this->m_height < $max_height )
					$max_height = $this->m_height;
			}
			$this->m_width = $max_width;
			$this->m_height = $max_height;
			return( array('width'=>ceil($this->m_width),'height'=>ceil($this->m_height) ) );
		} else {
			$max_width = isset( $width ) ? abs( $width ) : $this->m_width;
			$max_height = isset( $height ) ? abs( $height ) : $this->m_height;

			# H > MH + W > MW
			$divider = 1;
			if( ( $this->m_height > $max_height ) && ( $this->m_width > $max_width ) ) {
				if( $this->m_width > $this->m_height ){
					$divider = $this->m_width / $max_width;
				}else {
					$divider = $this->m_height / $max_height;
				}
				if( $divider > 1 ) {
					$this->m_width = $this->m_width / $divider;
					$this->m_height = $this->m_height / $divider;
				}
			}
			# reglage selon la largeur
			if( ( $this->m_width > $max_width ) && ( $this->m_height <= $max_height ) )
				$divider = $this->setWidth( $max_width, true );
			# reglage selon la hauteur
			if( ( $this->m_height > $max_height ) && ( $this->m_width <= $max_width ) )
				$divider = $this->setHeight( $max_height, true );
			return( array('width'=>ceil($this->m_width),'height'=>ceil($this->m_height) ) );
		}
	}


	public function cacheIt( $width = null, $height = null, $mode = 'equal' ){
		$w = isset( $width ) ? $width : $this->m_width;
		$h = isset( $height ) ? $height : $this->m_height;

		$p = new Picture( $this->m_filename );

		if( $w == null && $h == null ) $mode = 'equal';

		$search_filename = Picture::getPictureFilename( $this->m_filename, $w, $h, $mode );

		if( !file_exists( $search_filename ) ){
			switch( $mode ){
				case 'redim': $p->dimensionTo( $w, $h ); break;
				case 'crop': $p->cropTo( $w, $h ); break;
			}
			$p->toFile( $search_filename );
		}
		if(file_exists($search_filename)){
			list( $width, $height, $type, $attr ) = getimagesize( $search_filename );
			return(array('width'=>$width,'height'=>$height));
		}
	}
	
	public function makeFilename( $name )
	{
		$string = str_replace('http://', '-', $name);
		$string = str_replace('https://', '-', $string);
		$string = stripslashes( strtolower( trim( $string ) ) );
		$string = strtr( $string,
				"Ã€Ã�Ã‚ÃƒÃ„Ã…Ã Ã¡Ã¢Ã£Ã¤Ã¥Ã’Ã“Ã”Ã•Ã–Ã˜Ã²Ã³Ã´ÃµÃ¶Ã¸ÃˆÃ‰ÃŠÃ‹Ã¨Ã©ÃªÃ«Ã‡Ã§ÃŒÃ�ÃŽÃ�Ã¬Ã­Ã®Ã¯Ã™ÃšÃ›ÃœÃ¹ÃºÃ»Ã¼Ã¿Ã‘Ã±",
				"AAAAAAaaaaaaOOOOOOooooooEEEEeeeeCcIIIIiiiiUUUUuuuuyNn" );
		$string = str_replace( " ", "-", $string );
		$string = preg_replace('@[^a-zA-Z0-9\-\.\_]@','-',$string);
		$string = strip_tags($string);
		$string = str_replace( "_-", "-", $string );
		$string = str_replace( "-_", "-", $string );

		/*      while( strpos( $string, '--' ) > 0 )
		 $string = str_replace( "--", "-", $string );
		*/		return( strtolower( $string ) );
	}
}
