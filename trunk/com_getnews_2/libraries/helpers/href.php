<?php
class href extends stdClass {
	var $root = '';
	function href() {
	}
	function process_url($str_value, $root) {
		$this->root = $root;
		if (preg_match ( '/^http/i', $str_value )) {
			return $str_value;
		}
		if (preg_match ( '/^\//i', $str_value )) {
			return $this->_get_root_url () . $str_value;
		}		
		return $this->_get_path_url () . $str_value;
	}
	function _get_root_url() {
		$str_page_url = $this->root;
		$referer = parse_url ( $str_page_url );
		$referer ['path'] = null;
		$referer ['query'] = null;
		$referer ['fragment'] = null;
		return $this->_http_build_url ( $referer );
	}
	function _get_path_url() {
		$str_page_url = $this->root;
		$referer = parse_url ( $str_page_url );
		@ $referer ['path'] = preg_replace ( '/\/[^\/]*$/', '/', $referer ['path'] );
		$referer ['query'] = null;
		$referer ['fragment'] = null;		
		return $this->_http_build_url ( $referer );
	}
	function _http_build_url($referer) {
		$str_return = $referer ['scheme'];
		$str_return .= '://';
		
		$str_return .= isset ( $referer ['user'] ) ? $referer ['user'] : '';
		$str_return .= isset ( $referer ['pass'] ) ? ':' . $referer ['pass'] : '';
		
		$str_return .= isset ( $referer ['port'] ) ? ':' . $referer ['port'] : '';
		$str_return .= $referer ['host'];
		$str_return .= $referer ['path'];
		
		$str_return .= isset ( $referer ['query'] ) ? '?' . $referer ['query'] : '';
		$str_return .= isset ( $referer ['fragment'] ) ? '#' . $referer ['fragment'] : '';
		
		return $str_return;
	}
	/**
	 * convert string
	 *
	 * @param unknown_type $string
	 * @return unknown
	 */
	function convertalias($string)
	{
		$alias = $string;
		
		$coDau=array("à","á","ạ","ả","ã","â","ầ","ấ","ậ","ẩ","ẫ","ă",
		"ằ","ắ","ặ","ẳ","ẵ",
		"è","é","ẹ","ẻ","ẽ","ê","ề" ,"ế","ệ","ể","ễ",
		"ì","í","ị","ỉ","ĩ",
		"ò","ó","ọ","ỏ","õ","ô","ồ","ố","ộ","ổ","ỗ","ơ"
		,"ờ","ớ","ợ","ở","ỡ",
		"ù","ú","ụ","ủ","ũ","ư","ừ","ứ","ự","ử","ữ",
		"ỳ","ý","ỵ","ỷ","ỹ",
		"đ",
		"À","Á","Ạ","Ả","Ã","Â","Ầ","Ấ","Ậ","Ẩ","Ẫ","Ă"
		,"Ằ","Ắ","Ặ","Ẳ","Ẵ",
		"È","É","Ẹ","Ẻ","Ẽ","Ê","Ề","Ế","Ệ","Ể","Ễ",
		"Ì","Í","Ị","Ỉ","Ĩ",
		"Ò","Ó","Ọ","Ỏ","Õ","Ô","Ồ","Ố","Ộ","Ổ","Ỗ","Ơ"
		,"Ờ","Ớ","Ợ","Ở","Ỡ",
		"Ù","Ú","Ụ","Ủ","Ũ","Ư","Ừ","Ứ","Ự","Ử","Ữ",
		"Ỳ","Ý","Ỵ","Ỷ","Ỹ",
		"Đ","ê","ù","à");
	
		$khongDau=array("a","a","a","a","a","a","a","a","a","a","a"
		,"a","a","a","a","a","a",
		"e","e","e","e","e","e","e","e","e","e","e",
		"i","i","i","i","i",
		"o","o","o","o","o","o","o","o","o","o","o","o"
		,"o","o","o","o","o",
		"u","u","u","u","u","u","u","u","u","u","u",
		"y","y","y","y","y",
		"d",
		"A","A","A","A","A","A","A","A","A","A","A","A"
		,"A","A","A","A","A",
		"E","E","E","E","E","E","E","E","E","E","E",
		"I","I","I","I","I",
		"O","O","O","O","O","O","O","O","O","O","O","O"
		,"O","O","O","O","O",
		"U","U","U","U","U","U","U","U","U","U","U",
		"Y","Y","Y","Y","Y",
		"D","e","u","a");
		
		$alias = str_replace($coDau,$khongDau,$alias);		
		$alias = preg_replace('/[^a-zA-Z0-9-.]/', '-', $alias);	
		$alias = preg_replace('/^[-]+/', '', $alias);
		$alias = preg_replace('/[-]+$/', '', $alias);
		$alias = preg_replace('/[-]{2,}/', '-', $alias);		
		return $alias;
	}
	
	function take_file_name($external_image_url)
	{
		$external_image_url	=	$this->convertalias($external_image_url);
		$image_filename=preg_replace('/.*\/(.*)/','$1', urldecode($external_image_url));	
		$image_filename = preg_replace('/[^a-zA-Z0-9-.]/', '-', $image_filename);
		$image_filename=str_replace(' ','-',$image_filename);
		$image_filename = preg_replace('/^[-]+/', '', $image_filename);
		$image_filename = preg_replace('/[-]+$/', '', $image_filename);
		$image_filename = preg_replace('/[-]{2,}/', '-', $image_filename);
		$image_filename=str_replace('.jpg','.jpeg',$image_filename);
		if (strlen($image_filename) > 80) $image_filename = substr($image_filename,-80);
		$image_filename=trim($image_filename,'-');
		$image_filename=trim($image_filename);
		return $image_filename;
	}
	/**
	 * Enter description here...
	 *
	 * @param unknown_type $param
	 */
	function refresh($param )
	{
			 @$param['time']	=	 $param['time']? $param['time']:'4000';
		ob_start();
		?>
		<html>
			<script type="text/javascript">
			function submit()
			{
				setTimeout("document.form_refresh_getcontent.submit();", "<?php echo $param['time']; ?>");
			}				
			</script>
			<body onload="submit();">
			<center>
				<font face="Verdana, Arial, Helvetica, sans-serif" size="2" color="333333">
					<h3 name="title">
						Please wait while you are being redirected to continue ...
					</h3>
					<h4>Get new</h4>
					<h4><?php echo $_REQUEST['host']; ?></h4>
					</font>
				</center>
				<form name="form_refresh_getcontent" action="index.php" method="GET">
					<?php
						foreach ($param as $k=>$value)
						{
							if ($k=='time') {
								continue;
							}
							?>
								<input type="hidden" name="<?php echo $k; ?>" value="<?php echo $value; ?>" />
							<?php
						}
					?>
				</form>
			</body>
		</html>
		<?php		
		$str	=	ob_get_contents();
		ob_clean();	
		return $str;
	}

}


?>