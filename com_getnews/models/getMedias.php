<?php


	function get_image($src='http://a9.vietbao.vn/images/vn965/bong-da/65223148-vnm_2011_344002.jpg', $image_path='images/content_vov'){
		
		//get image
		
		$browser	=	new phpWebHacks();
		
		$response	=	$browser->get($src);
		$header = $browser->get_head(); 
		
		if(!$header['Status']['Code'] == '200'){
			return false;
		}
		
		//write image to specified location
		//create directoy with name is $link_id in tmp
		
		if (!mkdir($image_path, 0, true)) {
		    die('Failed to create folders...');
		}
		
		$arr_link_image = explode('/',$src);
		$file_name = $arr_link_image[count($arr_link_image) - 1];
		$arr_file_name = explode('.',$file_name);
		$file_name = $arr_file_name[0];
		$file_stored = $image_path.DS.$file_name;
	
		if(!fwrite($file_stored, $response)){
			$this->setError('[Get_Image] Cannot write file: '.$file_stored);
			return false;
		}
		
		//process image file type
		
		$file_name2 = $file_name1;
		if(function_exists('exif_imagetype')){
			if($int_image_type = exif_imagetype($file_stored)){
				$str_image_extension = image_type_to_extension($int_image_type, 1);
				//remove current image extension (if exist)
				$file_name2 = JFile::stripExt($file_name1);
				//add the real extension
				$file_name2 .= $str_image_extension;
			}
			else {
				//cannot get image type (unknow reason!)
				//TODO: trying to get extension from header
			}
		}
		
		
		//process duplicate files
		
		$file_name2 = $this->_process_duplicate($file_name2, $image_path);
		
		//rename file
		if ($file_name2 != $file_name) {
			$file_stored2 = $image_path.DS.$file_name2;
			if(!JFile::move($file_stored, $file_stored2)){
				$this->setError('[Get_Image] Cannot move file: '.$file_stored.' to new file: '.$file_stored2);
				return false;
			}
			$file_stored = $file_stored2;
		}
		
		
		//check if need resize
		if ($this->max_width && $this->max_height) {
			
			require_once(dirname(__FILE__).DS.'ImageResizeFactory.php');
			if(!ImageResizeFactory::getInstanceOf($file_stored, $file_stored, $this->max_width, $this->max_height)){
				$this->setError('[Get_Image] Cannot resize image: '.$file_stored);
				return false;
			}
			
		}
		
		return $file_stored;
	}
	
	function _process_duplicate($file_name_in, $str_dir){
		$i = 1;
		$str_name = JFile::stripExt(JFile::getName($file_name_in));
		$str_ext = JFile::getExt($file_name_in);
		$file_name = $str_name.'.'.$str_ext;
		while (JFile::exists($str_dir.DS.$file_name)) {
			$file_name = $str_name.'_'.$i.'.'.$str_ext;
			$i++;
		}
		return $file_name;
	}
	
	