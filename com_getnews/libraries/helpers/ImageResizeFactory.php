<?php
/**
 * @version	$Id: ImageResizeFactory.php $
 * @package	F5Gallery
 * @subpackage	Component
 * @copyright	Copyright (C) 2010
 * @license	Commercial
 */
defined('_VALID_MOS') or die( 'Restricted access' );

class ImageResizer
{
	
	private $original_img = null;
	private $resized_img = null;
	private $w = 0;
	private $h = 0;
	private $exact = false; // true: generate an image with the exact size WxH, with lateral or horizontal image completion(with bgColor)
	private $bgR = 0;
	private $bgG = 0;
	private $bgB = 0;
	
		
	function loadImage($path) // filename/url
	{
		$imgparsers = array();
		$imgparsers[]='imagecreatefromjpeg';
		$imgparsers[]='imagecreatefromgif';
		$imgparsers[]='imagecreatefrompng';
		$imgparsers[]='imagecreatefromstring';
		$imgparsers[]='imagecreatefromwbmp';
		$imgparsers[]='imagecreatefromxbn';
		$imgparsers[]='imagecreatefromxpm';
		$img = false;
		foreach($imgparsers as $i=>$func)
			if(function_exists($func))
				if( $img = @$func($path) )
				{
					break;
				}
		if(!$img)
			return false;
		$this->original_img = $img;
		return true;		
	}//loadImage
	
	
	function setImage($img)
	{
		if($this->original_img!=null)
			@imagedestroy($this->original_img);
		$this->original_img = $img;
	}//setImage

	function checkColorCode($code)
	{
		if(is_numeric($code)&&0<=$code&&$code<256)
			return $code;
		else return 0;
	}//checkColorCode

	// if $exact is true the result image will be EXACTLY of the specified size, while
	//     still keeping original aspect ratio; empty areas may result horizontally or vertically,
	//     which will be filled with the color specified in RGB format by bgR, bgG and bgB  
	function setExactSizeResult($exact, $bgR=0, $bgG=0, $bgB=0)
	{
		if($exact)
			$this->exact = true;
		$this->bgR = $this->checkColorCode($bgR);
		$this->bgG = $this->checkColorCode($bgG);
		$this->bgB = $this->checkColorCode($bgB);
	}//setExactResult
	
	// w -- desired Width of the result
	// h -- desired Height of the result
	// mode -- 0 = 'resize to at most w x h'; neither width nor height of the result image will be greater than w and h respectively
	//      -- 1 = 'resize to at least w x h'; neither width nor height of the result will be smaller than w or h respectively 	
	function resize($w,$h,$mode=0)
	{
		if($this->original_img==null)
			return;
		if($this->resized_img!=null)
			@imagedestroy($this->resized_img);
		
		$ow = imagesx($this->original_img); //original width
		$oh = imagesy($this->original_img); //original height
		
		$zero = false;
		
		if($w<=0)
		{
			$zero=true;
			$w = $ow;
		}
		if($h<=0)
		{
			$zero=true;
			$h = $oh;
		}
		$rw = $ow / $w; 
		$rh = $oh / $h;
		
		if($mode==1)
			$r=$rw<$rh?$rw:$rh;
		else
			$r = $rw>$rh?$rw:$rh;
		
		$res_w = $ow/$r;
		$res_h = $oh/$r;			
		
		if($this->exact&&!$zero)
		{
			$img = imagecreatetruecolor($w,$h);
			$bgcolor = imagecolorallocate($img,$this->bgR, $this->bgG, $this->bgB);
			imagefill($img,0,0,$bgcolor);
			imagecopyresampled($img,$this->original_img,($w-$res_w)/2,($h-$res_h)/2,0,0,$res_w,$res_h,$ow,$oh);
		}
		else
		{
			$img = imagecreatetruecolor($res_w,$res_h);
			imagecopyresampled($img,$this->original_img,0,0,0,0,$res_w,$res_h,$ow,$oh);
		}
		
		$this->resized_img = $img;
	}//resize
	
	
	function getResult()
	{
		if($this->resized_img==null)
			return null;
		if( !$img = @imagecreatetruecolor( imagesx( $this->resized_img ), imagesy( $this->resized_img )  ) )
			return null;
		imagecopy($img,$this->resized_img,0,0,0,0, imagesx( $this->resized_img ), imagesy($this->resized_img) );
		return $img;
	}//
	
	function freeResult()
	{
		if($this->resized_img!=null)
			@imagedestroy($this->resized_img);
	$this->resized_img=null;		
	}//freeResult
	
	function getOriginal()
	{
		return $this->original_img;
	}//getOriginal
	
	function __destruct()
	{
		if($this->original_img!=null)
			@imagedestroy($this->original_img);
		if($this->resized_img!=null)
			@imagedestroy($this->resized_img);
	}//destruct
	
}//ImageResizer

/**
* class ImageResizeFactory
*
* { Description :- 
*	This Class is a factory method class which returns the appropriate object of ImageResizeClass depending on the type of Image 
*	i.e jpg or Png.
* }
*/

class ImageResizeFactory
{
	/**
	* Method ImageResizeFactory::getInstanceOf()
	*
	* { Description :- 
	*	This method resizes the image.
	* }
	*/
	
	function getInstanceOf($imageName, $resizedImageName, $newWidth, $newHeight)
	{
		$info 		= @getimagesize($imageName);		
		
		if (($info[0] <= $newWidth) && ($info[1] <= $newHeight)) {
			if (!@ copy($imageName, $resizedImageName)) {				
				return false;
			}			
			return true;
		}
		
		// Instantiate the correct object depending on type of image i.e jpg or png
		$dimg = new ImageResizer();
		// -- original image: load from file
		$dimg->loadImage($imageName);			
		$dimg->resize($newWidth,$newHeight);
		$r_img = $dimg->getResult();
		
		if ($r_img === null) {
			return false;
		}
		
		$chunks = explode('.', $imageName);
		$chunksCount = count($chunks) - 1;

		$extension	=	$chunks[$chunksCount];
		
		if(preg_match("/jpg|jpeg/i", $extension))
		{								
			imagejpeg($r_img, $resizedImageName);
			return true;
		}
		
		if(preg_match("/png/i", $extension))
		{
			imagepng($r_img, $resizedImageName);
			return true;
		}
		
		if (preg_match("/gif/i", $extension)) {
			imagegif($r_img, $resizedImageName);				
			return true;
		}
		
		if (preg_match("/bmp/i", $extension)) {
			imagewbmp($r_img, $resizedImageName);
			return true;
		} 
		
		return false;
	}
}
