<?php
function mosGetImages($obj_content,$root, & $arr_Images , $path_image , $link_image, $param_getimage = null)
{
	if ($param_getimage == null) {
		$param_getimage = array();
	}
	$date	=	$obj_content->content_date;

	if (!is_dir($path_image)) {
		mkdir($path_image);
	}
	
	$path_image	.=	DS.date("Y",strtotime($date));
	if (!is_dir($path_image)) {
		mkdir($path_image);		
	}	
	$path_image	.=	DS.date("m",strtotime($date));
	if (!is_dir($path_image)) {
		mkdir($path_image);
	}
	$link_image	.=	'/'.date("Y",strtotime($date)).'/'.date("m",strtotime($date)).'/';
	$href	=	new href();
	$image_prefix	=	$href->take_file_name($obj_content->title).'-'.date("Y",strtotime($date)).'-'.date("m",strtotime($date));	
			
	$obj_content->intro		=	mosProcessImages($obj_content->intro,$root, $arr_Images, $path_image,$image_prefix,$link_image, $param_getimage);		
	$obj_content->fulltext	=	mosProcessImages($obj_content->fulltext,$root, $arr_Images, $path_image,$image_prefix,$link_image, $param_getimage);
	return true;
}
function mosProcessImages($content,$root,& $arr_Images, $path_image, $image_prefix,$link_image, $param_getimage = array())
{	
	global $arrNotice;
	//find all href value in a tag (with ")
	$href	=	new href();
	if(preg_match_all('/<img.*?(src=([^"\'].*?))(\s|\/>|>)/im', $content, $matches)){
		for ($i = 0; $i < count($matches[1]); $i++){
			$matches[2][$i]	=	$href->process_url($matches[2][$i],$root);
			$content = str_replace($matches[1][$i], 'src="'. $matches[2][$i] .'"', $content);
		}
	}
	//find all href value in a tag (with ')
	if(preg_match_all('/<img.*?(src=\'(.*?)\')/im', $content, $matches)){
		for ($i = 0; $i < count($matches[1]); $i++){
			$matches[2][$i]	=	$href->process_url($matches[2][$i],$root);
			$content= str_replace($matches[1][$i], 'src="'. $matches[2][$i] .'"', $content);
		}
	}
	
	if(preg_match_all('/<img.*?(src="(.*?)")/ism', $content, $matches)){		
		for ($i = 0; $i < count($matches[1]); $i++){
			
			$matches[2][$i]	=	$href->process_url($matches[2][$i],$root);
			$number	=	count($arr_Images)+1;
			$image_name	=	$image_prefix.'-'.$number;			
			$obj_get_image	=	new vov_Get_Image($matches[2][$i],$path_image);			
			if (!$response = $obj_get_image->get_image($image_name, $param_getimage)) {				
//				$content = str_replace($matches[0][$i], '', $content);
				continue;
			}			
			$_link_image	=	$link_image.$response->file_name;			
			$obj_image	=	new stdClass();			
			$obj_image->media_url	=	$_link_image;
			$obj_image->SourceURL	=	$matches[2][$i];
			$obj_image->Size		=	filesize($path_image.DS.$image_name.'.'.$response->file_type);
			$obj_image->FileName	=	$image_name;
			$obj_image->Path		=	$path_image;
			$obj_image->FileType	=	$response->file_type;
			$obj_image->MediaType	=	'image';
			$arr_Images[]			=	$obj_image;						
				$info 		= @getimagesize($path_image.DS.$image_name.'.'.$response->file_type);
				$width		=	$info[0];			
			if ($width>500) {
				$path_to_image	=	$path_image.DS.$image_name.'.'.$response->file_type;
				$newName		=	'best_'.$image_name;
				ImageResizeFactory::getInstanceOf($path_to_image, $path_image.DS.$newName.'.'.$response->file_type,500 ,1000);
				
				$obj_thumb				=	new stdClass();
				$obj_thumb->media_url	=	$link_image.$newName.'.'.$response->file_type;
				$obj_thumb->SourceURL	=	$matches[2][$i];
				$obj_thumb->Size		=	filesize($path_image.DS.$newName.'.'.$response->file_type);
				$obj_thumb->FileName	=	$newName;
				$obj_thumb->Path		=	$path_image;				
				$obj_thumb->FileType	=	$response->file_type;
				$obj_thumb->MediaType	=	'image';				
				$arr_Images[]	=	$obj_thumb;
				
				$content = str_replace($matches[1][$i], 'src="'. $obj_thumb->media_url .'"', $content);
			}else {
				$content = str_replace($matches[1][$i], 'src="'. $_link_image .'"', $content);
			}			
			
		}
	}	
	return $content;
}