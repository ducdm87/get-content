<?php
global $config_repair;
$config_repair	=	array();
$config_repair['SiteID']	=	array('bm12', 'bm15');

function mosRepai_image()
{
	global $database, $config_repair;
	$db	=	$database;
	$siteID	=	array();;
	for ($i =0; $i <count($config_repair['SiteID']); $i++)
	{
		$siteID[]	=	$db->quote($config_repair['SiteID'][$i]);
	}	
	$siteID	=	implode(',', $siteID);
	$query	=	'SELECT * '.
				' FROM `#__smedia2010_new` '.
				' WHERE `SiteID` in ( '. ($siteID). ')'. 
				' AND `MediaImage`<> 1 and `MediaImage`<> -1'.
				' LIMIT 0,200';
	$db->setQuery($query);		
	
	$array_image	=	$db->loadObjectList();	
	
	if (count($array_image) < 1) {
		echo 'sucesssfull';
		die();
	}
	
	$param	=	array();
	$param['option']	=	$_REQUEST['option'];
	$param['task']	=	$_REQUEST['task'];
	$_REQUEST['host']	=	'repair images';
	$href	=	new href();
	$refresh	=	$href->refresh($param);
	echo $refresh;
	echo '<hr />';
	
	echo 'process: '.count($array_image);
	echo '<hr />';
	$number_sucess	=	0;
	for ($i=0; $i< count($array_image); $i++)
	{
		echo '$i: ['.$i.']';
		$obj	=	$array_image[$i];
		$path	=	$obj->Path;
		$source	=	$obj->SourceURL;
		$name	=	$obj->FileName;
		$type	=	$obj->FileType;
		$path_img	=	$path . DS . $name . '.'.$type;
//		$path	=	'images\zrecybil\vn10\2011\04';
//		$path_img	=	$path.'\vn_Cat-giam-o-muc-cao-nhat-2011-04-1.jpeg';
			$MediaImage	=	1;
//		if (!is_writable($path_img)) {
			if (!mosGetRepairImage($path, $name, $type, $source)) {
				$MediaImage	=	-1;
				echo '<br /> ';
					echo 'status: false';
				echo '<br /> ';
			}else {
				$number_sucess ++;
			}
			echo '$number_sucess: ['.$number_sucess.']';		echo ' aid: ';		echo $obj->aid;
			echo ' &nbsp; id: ';		echo $obj->id;
			echo '<br />';		echo $path_img;
			echo '<br /> ';		echo $source;
			echo '<br /> $MediaImage: ';		echo $MediaImage;
			
			echo '<hr />';
//		}
		$query	=	'UPDATE `#__smedia2010_new` SET `MediaImage` = '. $MediaImage.
					' WHERE id = '.$obj->id;
		$db->setQuery($query);
		$db->query();		
	}
	echo "<title>$number_sucess </title>";
	echo '$number_sucess: '. $number_sucess;
}

function  mosGetRepairImage($path_image, $name, $type, $source)
{
	$path_image	=	mos_folderCreate($path_image);	
	$obj_get_image	=	new vov_Get_Image($source,$path_image);			
	if (!$response = $obj_get_image->get_image($name)) {
		return false;
	}
	
	return true;
}

function mos_folderCreate($path_image)
{
	$path_image	=	'images/img_repair' .DS . $path_image;
	$path_image	=	str_replace('\\','/',$path_image);
	$arr_path	=	explode('/', $path_image);
	$path	=	$arr_path[0];
	for ($i=1; $i<count($arr_path); $i++)
	{
		$path	=	$path.'/'.$arr_path[$i];		
		if (!is_dir($path)) {
			mkdir($path);
		}
	}
	return $path;
}


function mosRepair_original()
{
	$param	=	array();
	$param['option']	=	$_REQUEST['option'];
	$param['task']	=	$_REQUEST['task'];
	$param['type']	=	$_REQUEST['type'];
	$_REQUEST['host']	=	'repair content';
	$href	=	new href();
	$refresh	=	$href->refresh($param);
	if (!isset($_REQUEST['debug_1'])) {
		echo $refresh;	
	}
	echo '<hr />';
	
	$type	=	$_REQUEST['type'];
	if ($type == 'bm12') {		
		mosRepair_bm12();
	}else if ($type == 'bm15') {
		mosRepair_bm15();
	}	
}

function mosRepair_bm12()
{
	global $arrErr,$database;
	$db	=	$database;	
	$tbl_name	= '#__article2010_new_baomoi_02';
	$SiteID 	= 'bm12';
	$query		=	'SELECT id,id_original FROM '. $tbl_name . 
					' WHERE status = 0 LIMIT 0,50';				
	$db->setQuery($query);	
	$arr_content	=	$db->loadObjectList();
	echo $db->getQuery();
	echo '<br />';
	if (count($arr_content)<1) {
		echo 'sucessfull';	die();
	}
	for ($i=0; $i<count($arr_content); $i++)
	{
		echo '['.$i.'] ';
		$content	=	$arr_content[$i];
		$link	=	'http://www.baomoi.com/Home/category/host-baomoi/alias-baomoi/'.$content->id_original.'.epi';
		$query	=	'UPDATE '. $tbl_name .' SET status = 1 WHERE id='.$content->id;			
		if (!mos_process($content->id,$link,$SiteID)) {
			$query	=	'UPDATE '. $tbl_name .' SET status = -1 WHERE id='.$content->id;
		}
		$db->setQuery($query);		
		$db->query();	
	}	
}

function mosRepair_bm15()
{
	global $arrErr,$database;
	$db	=	$database;
	
	$tbl_name	= '#__article2010_new_baomoi_en';
	$SiteID 	= 'bm15';
	$query		=	'SELECT id,id_original FROM '. $tbl_name . 
					' WHERE status = 0 LIMIT 0,50';				
	$db->setQuery($query);
	echo $db->getQuery();
	$arr_content	=	$db->loadObjectList();
	
	if (count($arr_content)<1) {
		echo 'sucessfull';	die();
	}
	for ($i=0; $i<count($arr_content); $i++)
	{
		echo '['.$i.'] ';
		$content	=	$arr_content[$i];		
		$link	=	'http://en.baomoi.com/Home/category/host-baomoi/alias-baomoi/'.$content->id_original.'.epi';		
		$query	=	'UPDATE '. $tbl_name .' SET status = 1 WHERE id='.$content->id;			
		if (!mos_process($content->id,$link,$SiteID)) {
			$query	=	'UPDATE '. $tbl_name .' SET status = -1 WHERE id='.$content->id;
		}
		$db->setQuery($query);		
		$db->query();	
	}	
}

function mos_process($aid,$link,$SiteID)
{
	global $arrErr,$database;
	$db	=	$database;
	//	$link	=	'http://en.baomoi.com/Home/category/host-baomoi/alias-baomoi/154574.epi';
	$browser	=	new phpWebHacks();
	$response	=	$browser->get($link);
	
	if (!preg_match('/<base[^>]*href="([^"]*)"[^>]*\/>/ism',$response,$matches)) {		
		echo 'error width $aid: '.$aid.' $link: '. $link;
		echo '<br />';	
		return false;
	}else {		
		echo 'true: ';
		echo $aid.' $link: '. $link;
		echo '<br />';
	}
	$base_link	=	$matches[1];
	// store original
	$html	=	loadHtmlString($response);
	if (!$content = $html->find('div[id="mainContainer"]',0)) {
		echo 'error width $aid: '.$aid.' $link: '. $link;
		echo '<br />';
		return false;
	}
	
	$query	=	'INSERT into `#__article2010_original`
					SET aid = '.$db->quote($aid).',
						SiteID = '.$db->quote($SiteID).',
						PageHTML = '.$db->quote($content->innertext).',
						url = '.$db->quote($base_link);
	$db->setQuery($query);
	if (isset($_REQUEST['debug_1'])) {
		echo $base_link;
		echo $db->getQuery();die();
	}
	$db->query();
	return true;
}