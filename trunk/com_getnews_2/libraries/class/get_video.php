<?php
function mosGetVideo($external_video_url, $referer_path, $path_save, $video_filename)
{
	if (!is_dir($path_save)) {
		mkdir($path_save);
	}
	$arr_video = explode(',',$external_video_url);
	for ($i=0;$i<count($arr_video);$i++)
	{
		$url_to_download=str_replace(' ','%20',$arr_video[$i]);
		$fp = fopen ($path_save.$video_filename, 'w+');//This is the file where we save the information
		$ch = curl_init($url_to_download);//Here is the file we are downloading
		$curl_options_download = array(
		CURLOPT_FILE => $fp,
		CURLOPT_FOLLOWLOCATION => true, // follow redirects
		CURLOPT_USERAGENT => "MozillaMozilla/5.0 (Windows; U; Windows NT 6.0; en-US; rv:1.9.0.11) Gecko/2009060215 Firefox/3.0.9", // who am i
		CURLOPT_AUTOREFERER => true, // set referer on redirect
		CURLOPT_REFERER => "$referer_path",
		CURLOPT_CONNECTTIMEOUT => 900, // timeout on connect
		CURLOPT_TIMEOUT => 900, // timeout on response
		CURLOPT_MAXREDIRS => 10, // stop after 10 redirects
	);
	
	curl_setopt_array( $ch, $curl_options_download);
	curl_exec($ch);
	$downloadInfo=curl_getinfo($ch);
	curl_close($ch);
	fclose($fp);
	
	}
	return true;
}
