<?php
/**
 * @package		f5gallery
 * @subpackage	Helper
 * @link		http://yopensource.com
 * @author		yopensource
 * @copyright 	yopensource (yopensource@gmail.com)
 * @license		Commercial
 */
// Check to ensure this file is included in Joomla!
defined('_VALID_MOS') or die();

/**
 * YOS Getting images from remote site
 */
class vov_Get_Image extends stdClass  
{	
	function vov_Get_Image($src, $image_path){
		//ducdm87 hacked to remove space
		$src		=	str_replace(' ','%20',$src);
		$this->src = $src;		
		$this->tmp_path = $image_path;
	}
	
	/**
	 * Get an image then store in tmp directory
	 *
	 * @param string $src full image url
	 * @param int $max_width
	 * @param int $max_height
	 * @return boolean
	 */
	function get_image($file_name, $param =	array()){
		//get image
		global $arrNotice;		
		$browser = new phpWebHacks();
		//ducdm87 hacked to set param	
		$browser->setParam($param);
		$browser->timeout	=	20;
		
		$response = @$browser->get($this->src);
		
		if (!$response) {
			array_push($arrNotice,'[vov_Get_Image] '.$browser->getErrors());
			return false;
		}
		
		$page_header = $browser->get_head();
	
		if (!isset($page_header['Status']['Code']) || $page_header['Status']['Code'] != 200) {
			array_push($arrNotice,'[vov_Get_Image] Unable to get image: '.$this->src);
			return false;
		}
		// check type image
	
		if (!preg_match('/^image/ism',$page_header['Content-Type'])) {		
			array_push($arrNotice,'[vov_Get_Image] That is not the image: '.$this->src);
			return false;
		}
		
		$conten_type	=	$browser->get_head();
		//write image to specified location
		//create directoy with name is $link_id in tmp
		if(!is_dir($this->tmp_path)){
			if(!mkdir($this->tmp_path)){
				array_push($arrNotice,'[vov_Get_Image] Cannot create directoy: '.$this->tmp_path);
				return false;
			}
		}
		
		$conten_type	=	$conten_type['Content-Type'];
		$conten_type	=	explode('/',$conten_type);
		$conten_type	=	$conten_type[1];		
		
		$file_stored = $this->tmp_path.DS.$file_name.'.'.$conten_type;
		if(!file_put_contents($file_stored, $response)){
			array_push($arrNotice,'[vov_Get_Image] Cannot write file: '.$file_stored);
			return false;
		}
		
		//process image file type
		$file_name2 = $file_name;	
		if (function_exists('exif_imagetype')) {
			if($int_image_type = exif_imagetype($file_name2)){				
				$str_image_extension = image_type_to_extension($int_image_type, 1);
				//remove current image extension (if exist)
				$file_name2 = preg_replace('#\.[^.]*$#', '', $file_name);
				//add the real extension
				$file_name2 .= $str_image_extension;
			}
			else { 				
				//cannot get image type (unknow reason!)
				//TODO: trying to get extension from header
			}
		}else {
				
		}
		
		//process duplicate files
//		$file_name2 = $this->_process_duplicate($file_name2, $this->tmp_path);

		//rename file
		if ($file_name2 != $file_name) {
			$file_stored2 = $this->tmp_path.DS.$file_name2;
			if(!rename($file_stored, $file_stored2)){
				array_push($arrNotice,'[vov_Get_Image] Cannot move file: '.$file_stored.' to new file: '.$file_stored2);
				return false;
			}
			$file_stored = $file_stored2;
		}
		$result	=	new stdClass();
		$result->file_stored	=	$file_stored;
		$result->file_name	=	$file_name.'.'.$conten_type;
		$result->file_type	=	$conten_type;
		return $result;
	}
}