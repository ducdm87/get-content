<?php

function GetCategory (){
	
	global $database;
	$db	=	& $database;
	
	$query = "SELECT link FROM #__vov_category ORDER BY id";
	
	$db->setQuery ( $query );
		
	$arr_link = $db->loadResultArray();
	
	return $arr_link;
}



function GetFocus($link)
{
	
	$browser	=	new phpWebHacks();
	$response	=	$browser->get($link);	
	$html		=	loadHtmlString($response);
	$arr_content = array();
	
	if (preg_match('/<div id="sidebar">(.*?)<div id="listen">/ism',$html,$matches)) {
		
		preg_match_all('/<a id="[^"]+".*?href="([^"]+)">(.*?)<\/a>/ism',$matches[1],$data);
		
		for ($i=0;$i<count($data[1]);$i++){
			
			$link_item = $data[1][$i];
			$arr_link_item = explode('/',$link_item);
			$result_link = $arr_link_item[count($arr_link_item)-1];
			$arr_content[] = $result_link;
			
		}
		return $arr_content;
	}
	else return false;
	
}

function SaveFocus($id)
{
	
	global $database,$error;

	$db	=	& $database;
	
	$query = "UPDATE `#__vov_content` SET `focus` = '1' WHERE `link` like '%$id%'";
	
	$db->setQuery ( $query );

	if (!$db->query()) {
		$error->arr_err[]	=	"Error insert or update data ".$query;
		return false;
	}		
}

