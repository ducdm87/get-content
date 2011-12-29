<?php 

function mosModlesTheThaoVanHoaGetVideoNews($max_number = 1)
{	
	
	global $arrErr,$database, $mosConfig_live_site;
	
	$id_content	=	$_REQUEST['id_content'];
	$number		=	$max_number;
	
	$id_result		=	$id_content	+	$number;
	
	$arr_ID			=	array();
	for ($i = 0; $i < $number; $i++)
	{
		$arr_ID[]	=	$id_content;
		$id_content	=	$id_content + 1;
	}
	
	$number_getContent	=	0;

	$option	=	$_REQUEST['option'];
	
	for ($i = 0; $i < count($arr_ID) ; $i++)
	{			
		$id_content	=	$arr_ID[$i];
		$link		=	$mosConfig_live_site."/index.php?option=$option&task=getvideos&conten_id=".$id_content;
		
		$web 		=	parse_url($link);
		$begin		=	md5('BEGIN_GET_CONTENT_VOV');
		$end		=	md5('END_GET_CONTENT_VOV');
		 
		$postdata	=	$web['query'];
		$postdata	.=	'&begin_get_content='.$begin;
		$postdata	.=	'&end_get_content='.$end;
			
		$fp = @fsockopen($web['host'], 80, $errnum, $errstr, 30);
		$info = '';
        if (!$fp)	echo $errnum.': '.$errstr;
        else {
                fputs($fp, "POST ".$web['path']." HTTP/1.1\r\n");
                fputs($fp, "Host: ".$web['host']."\r\n");
                fputs($fp, "Content-type: application/x-www-form-urlencoded\r\n");
                fputs($fp, "Content-length: ".strlen($postdata)."\r\n");
                fputs($fp, "Connection: close\r\n\r\n");
                fputs($fp, $postdata . "\r\n\r\n");    

                while(!feof($fp)) {
                        $info .= @fgets($fp, 1024);
                }
                fclose($fp);                   
        }       
         if (preg_match('/' . $begin . '(.*?)' . $end . '/ism', $info, $match)) 
         {                   
             $info=trim($match[1]);
         }
         else {
         	$message	=	'ERROR_GET_CONTENT_VOV| #123 API false '.$id_content.' '.$info;
         	array_push($arrErr,$message);
            continue;
         }
      	 if (stristr($info,'ERROR_GET_CONTENT_VOV')) {
  	 		$message	=	'ERROR_GET_CONTENT_VOV| '.$info;
     		array_push($arrErr,$message);
            continue;
         }
         $number_getContent	=	$number_getContent + 1;
	}
	$obj_result	=	new stdClass();
	$obj_result->number_getcontent	=	$number_getContent;
	$obj_result->id_result			=	$id_result;
//	mysql_close($db->_resource);
	return $obj_result;
	
}

function mosModlesTheThaoVanHoaGetUrlVideo($id)
{
	global $database;
	$db	=	& $database;
	
	$query = "SELECT *
		FROM `#__article2010_videos`
		WHERE `state` = 1 AND `id` = $id";	
	$db->setQuery($query);
	$db->loadObject($obj);
	
	return $obj;
}

function mosModlesTheThaoVanHoaUpdateVideo($id)
{
	global $database, $arrErr;
	$db	=	& $database;
	
	$query = "UPDATE `#__article2010_videos` 
				SET `state` = 2
				WHERE `id` =".$id;	
	$db->setQuery( $query );
	if (!$db->query()) {
		$messege	=	$db->getQuery();
		array_push($arrErr,$messege);
		return false;
	}	

}

function mosModlesTheThaoVanHoaGetVideo($external_video_url, $referer_path, $path_save, $video_filename)
{
	if (!is_dir($path_save)) {
		mkdir($path_save);
	}
	$url_to_download=str_replace(' ','%20',$external_video_url); 
	$fp = fopen ($path_save.$video_filename, 'w+');//This is the file where we save the information
    $ch = curl_init($url_to_download);//Here is the file we are downloading
    $curl_options_download = array(
    CURLOPT_FILE  => $fp,
    CURLOPT_FOLLOWLOCATION => true,     // follow redirects
    CURLOPT_USERAGENT      => "MozillaMozilla/5.0 (Windows; U; Windows NT 6.0; en-US; rv:1.9.0.11) Gecko/2009060215 Firefox/3.0.9", // who am i
    CURLOPT_AUTOREFERER    => true,     // set referer on redirect
    CURLOPT_REFERER    => "$referer_path",
    CURLOPT_CONNECTTIMEOUT => 900,      // timeout on connect
    CURLOPT_TIMEOUT        => 900,      // timeout on response
    CURLOPT_MAXREDIRS      => 10,       // stop after 10 redirects
    );    

    curl_setopt_array( $ch, $curl_options_download);   
    curl_exec($ch);
    $downloadInfo=curl_getinfo($ch); 
    curl_close($ch);
    fclose($fp);
    return true;
}

?>