<?php

$data_category	=	array();

/////////////////////////////////////////////////////////////////////////////////////////
	//////////////  FOR VOV	/////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////

/**
 * Get listcategory of vov
 *
 * @param unknown_type $url
 * @return unknown
 */
function mosModelCategoryGetVOV($url = null)
{
	if ($url == null) {
		$url	=	'http://english.vovnews.vn';
	}	
	$browser	=	new phpWebHacks();
	$response	=	$browser->get($url);
	
	$reg		=	'/<div[^>]*id="mainnav"[^>]*>\s*<ul>(.*?)<\/ul>\s*<\/div>\s*<div[^>]*id="secondary"[^>]*>/ism';
	
	if (!preg_match($reg,$response,$matches)) {
		return false;
	}	
	$tree	=	new buildTreeVOV();
	$arr_data	=	$tree->getTree($matches[1],$url);
	return $arr_data;
}
/**
 * Store category of vov
 *
 * @param unknown_type $data
 * @param unknown_type $section_id
 * @return unknown
 */
function mosModelCategorySaveVOV($data , $section_id = 1)
{	
	if (!is_array($data) || count($data)<1) {
		return false;
	}
	
	global $database;
	$db	=	$database;
	foreach ($data as $category)
	{
		// check article2010_category_vov
		$query	=	'SELECT COUNT(*) 
					FROM `#__article2010_category_vov` 
					WHERE `link` = '.$db->Quote($category->link);
		$db->setQuery($query);		
		$result	=	$db->loadResult();
		if ($result) {
			continue;
		}
		$jl_category	=	'';
		$parent			=	'';
		if ($category->parent != -1) {
			$query	=	'SELECT id,jl_category  
											FROM `#__article2010_category_vov` 
											WHERE `link`='. $db->Quote($data[$category->parent]->link);
			$db->setQuery($query);
			$db->loadObject($parent);			
			$jl_category	=	$parent->jl_category;
			$parent			=	$parent->id;
		}
		// store category
		$row_caregory				=	new	mosCategory($db);
		$row_caregory->parent_id	=	$parent;
		$row_caregory->title		=	$category->title;
		$row_caregory->name			=	$category->title;
		$row_caregory->section		=	$section_id;
				
		if (! $row_caregory->store ()) {
			echo "<script> alert('" . $row_caregory->getError () . "'); window.history.go(-1); </script>\n";
			exit ();
		}
		// store vov_category
		$row_vov_caregory				=	new mosVovCategory($db);
		$row_vov_caregory->jl_category	=	$row_caregory->id;
		$row_vov_caregory->title		=	$category->title;
		$row_vov_caregory->link			=	$category->link;
		$row_vov_caregory->parent		=	$parent;
		if (! $row_vov_caregory->store ()) {
			echo "<script> alert('" . $row_vov_caregory->getError () . "'); window.history.go(-1); </script>\n";
			exit ();
		}
	}
	return true;
}


////////////////////////////////////////////////////////////////////////////////////
	////////////////	FOR BAOMOI	/////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////////
/**
 * Get list category of baomoi
 *
 * @param unknown_type $url
 * @return unknown
 */
function mosModelCategoryGetBAOMOI($url = null, $link_js)
{
	if ($url == null) {
		$url	=	'http://www.baomoi.com/';
		$link_js	=	'http://static.baomoi.vn/JScripts/static_menu.js';
	}
	$browser	=	new phpWebHacks();
	$response	=	$browser->get($link_js);
	
	$js_command	=	explode(';',$response);
	$command	=	$js_command[1];
	$command	=	str_replace('\"','sperator_VB',$command);
	$command	=	str_replace('"','',$command);
	$command	=	str_replace('\\','',$command);
	$command	=	str_replace('sperator_VB','"',$command);
	$command	=	str_replace('menu +=','',$command);	
	
	$reg		=	'/<div[^>]*class="wrapNavi"[^>]*>(.*?)<\/div>/ism';
	
	if (!preg_match($reg,$command,$matches)) {
		return false;
	}	
	$tree	=	new buildTreeBAOMOI();
	$arr_data	=	$tree->getTree($matches[1],$url);
	$reg_id		=	'/\/(\d+)\/\d+\.epi/ism';
	for ($i=1; $i<=count($arr_data);$i++)
	{
		$link	=	$arr_data[$i]->link;		
		$response	=	$browser->get($link);
		$html		=	loadHtmlString($response);
		$columnLatest	=	$html->find('div[id="columnLatest"]',0);
		$link_content	=	$columnLatest->find('ul[class="bmListing"]',0)->first_child()->first_child()->href;
		if (!preg_match($reg_id,$link_content,$matches_id)) {
			$arr_data[$i]->id_origional	=	0;
			continue;
		}
		$arr_data[$i]->id_origional	=	$matches_id[1];		
	}	
	return $arr_data;
}
/**
 * Store category of baomoi
 *
 * @param unknown_type $data
 * @param unknown_type $section_id
 * @return unknown
 */
function mosModelCategorySaveBAOMOI($data)
{	
	if (!is_array($data) || count($data)<1) {
		return false;
	}
	
	global $database;
	$db	=	$database;
	foreach ($data as $category)
	{
		$query = "INSERT INTO #__article2010_category_baomoi
						SET title=".$db->Quote(trim($category->title)).",
						link=".$db->Quote(trim($category->link)).",
						id_origional=".$db->Quote(trim($category->id_origional))."						
					ON DUPLICATE KEY UPDATE 
						title=".$db->Quote(trim($category->title)).",
						id_origional=".$db->Quote(trim($category->id_origional));						
		
		$db->setQuery ( $query );		
		if (!$db->query()) {
			echo "<script> alert('" . $db->getError () . "'); window.history.go(-1); </script>\n";
			exit ();
		}
	}
	return true;
}

//////////////////////////////////////////////////////////////////////////////////////
	//////////////	FOR VIETNAMNET	/////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////////

// cat_alias và cat_title
function mosModelCategoryGetVNNET ($url = null)
{
	if ($url == null) {
		$url		=	'http://english.vietnamnet.vn/';
	}
	
	$html		= 	loadHtml($url);
	$obj		= 	$html->find('div[class="home-menu"]',0);
	$response	= 	$obj->innertext;
	
	$reg		=	'/<a href="([^"]+)"\s*(class="sub-link"|)>(.*?)<\/a>/ism';

	if (!preg_match_all($reg,$response,$matches)) 
	{
		$message	=	'#341 models Newsktdt mosModelNewsktdtGetNews. Invalid get news content';
		array_push($arrErr,$message);
		return false;
	}
	$list_cat_alias = $matches[1];
	$list_cat_title = $matches[3];
	
	$arr_cat_alias = array();
	for ($i=0;$i<count($list_cat_alias);$i++)
	{
		$source_cat = 'http://english.vietnamnet.vn'.$list_cat_alias[$i];
		$obj_cat = new stdClass();
		$link_cat_item = explode('/',$list_cat_alias[$i]);
		$cat_alias = $link_cat_item[2];
		
		if ($cat_alias == 'index.html') continue;
	
		$obj_cat->source_cat = $source_cat;
		if ($cat_alias != null) {
			$obj_cat->cat_alias = $cat_alias;
			$obj_cat->title = $list_cat_title[$i];
		}
		$arr_cat_alias[] = $obj_cat;
	}
	return $arr_cat_alias;
}

// Lưu category vào csdl
function mosModelCategorySaveVNNET($data)
{
	global $database;
	$db	=	& $database;
	for ($i=0; $i<count($data); $i++)
	{
		$query = "INSERT INTO #__article2010_category_vietnamnet
						SET 
						title=".$db->Quote(trim($data[$i]->title)).",
						alias_origional=".$db->Quote(trim($data[$i]->cat_alias)).",
						isparent= 0,
						publish = 1,
						domain=".$db->Quote(trim($data[$i]->source_cat))."
						
					ON DUPLICATE KEY UPDATE 
						alias_origional=".$db->Quote(trim($data[$i]->cat_alias));
						
		
		$db->setQuery ( $query );
		
		if (!$db->query()) {
			$this->arr_err[]	=	"Error insert or update data ".$query;
			return false;
		}
	}	
	
	return true;
}


//////////////////////////////////////////////////////////////////////////////////////
//////////////////	FOR THANHNIEN	//////////////////////////////////////////////////
//////////////////////////////////////////////////////////////////////////////////////

// cat_alias và cat_title
function mosModelCategoryGetVNTHANHNIEN ($url = null)
{
	if ($url == null) {
		$url		=	'http://www.thanhniennews.com/Pages/Politics.aspx';
	}
	
	$html		= 	loadHtml($url);
	$obj		= 	$html->find('select[id="ctl00_ctl12_g_c0a1ce59_a786_4bb3_b198_00f244316600_ctl00"]',0);
	$response	= 	$obj->innertext;
	
	$reg		=	'/<option[^>]*value="([^"]+)">(.*?)<\/option>/ism';

	if (!preg_match_all($reg,$response,$matches)) 
	{
		$message	=	'#341 models Newsktdt mosModelNewsktdtGetNews. Invalid get news content';
		array_push($arrErr,$message);
		return false;
	}
	$value = $matches[1];
	$list_cat_title = $matches[2];
	
	$arr_obj_cat = array();
	for ($i=0;$i<count($list_cat_title);$i++)
	{
		
		if ($value[$i] == '0') {
			continue;
		}
		if ($value[$i] != '0') {
			$arr_obj_cat[] = $list_cat_title[$i];
		}
		
	}
	return $arr_obj_cat;
}

// Lưu category vào csdl
function mosModelCategorySaveVNTHANHNIEN($data)
{
	global $database,$arrErr;
	$db	=	& $database;
	
	for ($i=0; $i<count($data); $i++)
	{
		$query = "INSERT INTO #__article2010_category_thanhnien
						SET 
						title=".$db->Quote($data[$i]).",
						isparent= 0,
						publish = 1
				  ON DUPLICATE KEY UPDATE 
						isparent= 0";
		$db->setQuery ( $query );
		
		if (!$db->query()) {
			$message	=	'#341 models Newsktdt mosModelNewsktdtGetNews. Invalid get news content';
			array_push($arrErr,$message);
			return false;
		}		
	}
	return true;
}


////////////////////////////////////////////////////////////////////////////
/////////		FOR KTDT			//////////
////////////////////////////////////////////////////////////////////////////

function mosModelCategoryGetKTDT($url)
{
	$url		=	'http://www.ktdt.com.vn/';
	$browser	=	new phpWebHacks();
	$response	=	$browser->get($url);
	$reg		=	'/<TABLE id=table498[^>]*>(.*?)<\/TABLE>\s*<TABLE id=table498[^>]+>/ism';
	if (!preg_match($reg,$response,$matches)) 
	{
		$message	=	'#341 models Newsktdt mosModelNewsktdtGetNews. Invalid get news content';
		array_push($arrErr,$message);
		return false;
	}
	$list_news = $matches[1];
	$html	=	loadHtmlString($list_news);
	$href	=	new href();
	preg_match_all('/<a class="(menu|menu-sub|menu_sub|menu_sub2)" href="([^"]+)">(.*?)<\/a>/ism',$html,$parent_cat);
	$arr_cat = array();
	for ($i=0;$i<count($parent_cat[2]);$i++)
	{
		$obj_cat = new stdClass();
		$cat_link = explode('CatID=',$parent_cat[2][$i]);
		
		$obj_cat->id_origional 	= $cat_link[1]; 
		$obj_cat->title 		= $parent_cat[3][$i]; 
		$obj_cat->str_url 		= $parent_cat[2][$i]; 
		$obj_cat->domain 		= $url; 
		$obj_cat->isparent = 0;
		
		$arr_cat[] = $obj_cat;
	}
	return $arr_cat;
}

function mosModelCategorySaveKTDT($arr_data)
{
	global $arrErr, $database;
	$db	=	& $database;	
	for ($i=0; $i<count($arr_data); $i++)
	{
		$data	=	$arr_data[$i];
		$query = "INSERT INTO #__article2010_category
						SET id_origional =".trim($data->id_origional).",
						title=".$db->Quote(trim($data->title)).",
						domain=".$db->Quote(trim('http://www.ktdt.com.vn/showCat.asp?CatID=').trim($data->id_origional)).",
						isparent=".$db->Quote(trim($data->isparent))."
						
					ON DUPLICATE KEY UPDATE 					
						title=".$db->Quote(trim($data->title)).",
						domain=".$db->Quote(trim('http://www.ktdt.com.vn/showCat.asp?CatID=').trim($data->id_origional));
		
		$db->setQuery ( $query );		
		if (!$db->query()) {
			array_push($arrErr,"Error insert or update data ".$query);
			return false;
		}
	}	
	return true;
}

////////////////////////////////////////////////////////////////////////////
/////////		FOR NGUOI LAO DONG			//////////
////////////////////////////////////////////////////////////////////////////
function mosModelCategoryGetNGUOILAODONG($url = null)
{
	if ($url == null) {
		$url		=	'http://nld.com.vn/';
	}
	
	$html		= 	loadHtml($url);
	$obj		= 	$html->find('div[id="nav"]',0);
	$response	= 	$obj->innertext;
	$reg		=	'/<a href="([^"]+)"[^>]*>(.*?)<\/a>/ism';

	if (!preg_match_all($reg,$response,$matches)) 
	{
		$message	=	'#341 models Newsktdt mosModelNewsktdtGetNews. Invalid get news content';
		array_push($arrErr,$message);
		return false;
	}
	$value = $matches[1];
	$list_cat_title = $matches[2];
	
	$arr_obj_cat = array();
	for ($i=0;$i<count($value);$i++)
	{
		
		$obj_category = new stdClass();
		if ($value[$i] == '/') {
			continue;
		}
		$arr_value = explode('/',$value[$i]);
		
		$alias = explode('.',$arr_value[2]);
		$alias_origional = $alias[0];
		if(strlen($arr_value[1])<=5)
		{
			continue;
		}
		if(strlen($arr_value[1])<=7)
		{
			$obj_category->title = strip_tags($list_cat_title[$i]);
			$obj_category->alias_origional = strip_tags($alias_origional);
			$obj_category->id = substr($arr_value[1],3,4);
			$obj_category->parent = 0;
			$arr_obj_cat[] = $obj_category;
			continue;
		}
		
		$obj_category->title = strip_tags($list_cat_title[$i]);
		$obj_category->alias_origional = strip_tags($alias_origional);
		$obj_category->id = substr($arr_value[1],6,4);
		$obj_category->parent = substr($arr_value[1],1,4);
		$arr_obj_cat[] = $obj_category;
		
	}
	
	return $arr_obj_cat;
}

// Lưu category vào csdl
function mosModelCategorySaveNGUOILAODONG($data)
{
	global $database,$arrErr;
	$db	=	& $database;
	
	for ($i=0; $i<count($data); $i++)
	{
		$link	=	"http://nld.com.vn/p".$data[$i]->parent."c".$data[$i]->id."/".$data[$i]->alias_origional.".htm";
		$query = "INSERT INTO #__article2010_category_nguoilaodong
						SET 
						title=".$db->Quote($data[$i]->title).",
						parent=".$db->Quote($data[$i]->parent).",
						alias_origional=".$db->Quote($data[$i]->alias_origional).",
						id_origional=".$db->Quote($data[$i]->id).",
						domain = ".$db->Quote($link).",
						publish = 1
				  ON DUPLICATE KEY UPDATE 
				  		title=".$db->Quote($data[$i]->title).",
				  		alias_origional=".$db->Quote($data[$i]->alias_origional).",
				  		domain = ".$db->Quote($link).",
						parent=".$db->Quote($data[$i]->parent);
		$db->setQuery ( $query );
		
		if (!$db->query()) {
			$message	=	'#341 models Newsktdt mosModelNewsktdtGetNews. Invalid get news content';
			array_push($arrErr,$message);
			return false;
		}		
	}
	return true;
}

////////////////////////////////////////////////////////////////////////////
/////////		FOR THE THAO VAN HOA			//////////
////////////////////////////////////////////////////////////////////////////
function mosModelCategoryGetTTVH($url = null)
{
	if ($url == null) {
		$url		=	'http://thethaovanhoa.vn/';
	}
	
	$html		= 	loadHtml($url);
	$response = $html->innertext;
	$arr_menu = array();
	
	$obj_parent_menu		= 	$html->find('div[id="nav"]',0);
	$response_parent_menu 	= 	$obj_parent_menu->innertext;
	
	$reg_parent_menu = '/<li><a href="([^"]+)"[^>]+id="menu(\d+)">(.*?)<\/a><\/li>/ism';
	preg_match_all($reg_parent_menu,$response_parent_menu,$matches);	
	$arr_cat_link	= $matches[1];
	$arr_cat_id 	= $matches[2];
	$arr_cat_title 	= $matches[3];
	for ($i=0;$i<count($arr_cat_id);$i++)
	{
		$obj_parent_menu = new stdClass();
		$obj_parent_menu->id = $arr_cat_id[$i];
		$obj_parent_menu->title = $arr_cat_title[$i];
		$obj_parent_menu->link = $arr_cat_link[$i];
		$obj_parent_menu->parent='';
		$arr_menu[]=$obj_parent_menu;
	}
	
// Sub_menu <ul class="clearfix" id="submenu128" style="display: none">
	
	$preg_group_sub_menu = '/<ul[^>]*id="submenu(\d+)"[^>]*>(.*?)<\/ul>/ism';
	preg_match_all($preg_group_sub_menu,$response,$matches_group);
	$group_sub_menu =  $matches_group[2];
	$parent_group_sub_menu =  $matches_group[1];

	for ($j=0;$j<count($group_sub_menu);$j++)
	{
		
		$reg_sub_menu_item = '/<li><a href="([^"]+)">(.*?)<\/a><\/li>/ism';
		preg_match_all($reg_sub_menu_item,$group_sub_menu[$j],$matches);
		$link_menu = $matches[1];
		$title_menu = $matches[2];
		for ($n=0;$n<count($link_menu);$n++)
		{
			$obj_sub_menu = new stdClass();
			$explode_link_menu = explode('/',$link_menu[$n]);
			$id_sub_menu = $explode_link_menu[count($explode_link_menu)-2];
			$explode_id_sub_menu = explode('CT',$id_sub_menu);
			$obj_sub_menu->id = $explode_id_sub_menu[0];
			$obj_sub_menu->title = $title_menu[$n];		
			$obj_sub_menu->link = $link_menu[$n];		
			$obj_sub_menu->parent = $parent_group_sub_menu[$j];
			$arr_menu[]=$obj_sub_menu;
		}
		
		
		
	}
//	for ($a=0;$a<count($arr_menu);$a++)
//	{
//		var_dump($arr_menu[$a]);echo '<br/>';
//	}
//	die();
	
	return $arr_menu;
}


// Lưu category vào csdl
function mosModelCategorySaveTTVH($data)
{
	global $database,$arrErr;
	$db	=	& $database;
//	var_dump($data); die();
	for ($i=0; $i<count($data); $i++)
	{
		$link	=	'http://thethaovanhoa.vn'.$data[$i]->link;
		$query = "INSERT INTO #__article2010_category_thethaovanhoa
						SET 
						title = ".$db->Quote($data[$i]->title).",
						parent = ".$db->Quote($data[$i]->parent).",
						id_origional = ".$db->Quote($data[$i]->id).",
						domain = ".$db->Quote($link).",
						publish = 1
				  ON DUPLICATE KEY UPDATE 
				  		title=".$db->Quote($data[$i]->title).",
				  		domain = ".$db->Quote($link).",
						parent=".$db->Quote($data[$i]->parent);
		$db->setQuery ( $query );
	
		if (!$db->query()) {
			$message	=	'#341 models Newsktdt mosModelNewsktdtGetNews. Invalid get news content';
			array_push($arrErr,$message);
			return false;
		}		
	}
	return true;
}

////////////////////////////////////////////////////////////////////////////
/////////		FOR AUTONET			//////////
////////////////////////////////////////////////////////////////////////////
function mosModelCategoryGetATN($url = null)
{
	if ($url == null) {
		$url		=	'http://autonet.com.vn//common/v1/js/layout_3441.js';
	}
	
	$html		= 	loadHtml($url);
	$response = $html->innertext;
	$arr_menu = array();
	$reg_menu = '/function VO14893\(\)\{\s*document.write\("(.*?)"\);\s*\}/ism';
	
	preg_match($reg_menu,$response,$matches);
	$group_menu = str_replace('\\','',$matches[1]);
	$obj_group_menu = loadHtmlString($group_menu);
	
	$parent_menu = $obj_group_menu->find('div[id="parentcate"]',0);
	$parent_menu = $parent_menu->innertext;
// parent menu	
	$reg_parent_menu_item = '/<div id="(\d+)" url="\/([^"]+)\/">(.*?)<\/div>/ism';
	preg_match_all($reg_parent_menu_item,$parent_menu,$match_menu_parent);
	
	for ($i=0;$i<count($match_menu_parent[1]);$i++)
	{
		$obj_menu_parent = new stdClass();
		$obj_menu_parent->id_original = $match_menu_parent[1][$i];
		$obj_menu_parent->menu_alias = $match_menu_parent[2][$i];
		$obj_menu_parent->title = $match_menu_parent[3][$i];
		$obj_menu_parent->parent = 0;
		$arr_menu[] = $obj_menu_parent;
	}
// sub menu	
	$parent_menu = $obj_group_menu->find('div[id="subcate"]',0);
	$parent_menu = $parent_menu->innertext;
	
	$reg_parent_menu_item = '/<div id="(\d+)" class="(\d+)" url="\/([^"]+)\/">(.*?)<\/div>/ism';
	preg_match_all($reg_parent_menu_item,$parent_menu,$match_menu_sub);
	
	for ($j=0;$j<count($match_menu_sub[1]);$j++)
	{
		$obj_menu_sub = new stdClass();
		$obj_menu_sub->id_original = $match_menu_sub[1][$j];
		$obj_menu_sub->menu_alias = $match_menu_sub[3][$j];
		$obj_menu_sub->title = $match_menu_sub[4][$j];
		$obj_menu_sub->parent = $match_menu_sub[2][$j];
		$arr_menu[] = $obj_menu_sub;
	}
	
	return $arr_menu;
}


// Lưu category vào csdl
function mosModelCategorySaveATN($data)
{
	global $database,$arrErr;
	$db	=	& $database;
	
	for ($i=0; $i<count($data); $i++)
	{
		$link	=	"http://autonet.com.vn/".$data[$i]->menu_alias;
		
		if ($data[$i]->parent == 0) {
			$public = 0;
		}
		else $public = 1;
		$query = "INSERT INTO #__article2010_category_autonet
						SET 
						title=".$db->Quote($data[$i]->title).",
						parent=".$db->Quote($data[$i]->parent).",
						id_origional=".trim($data[$i]->id_original).",
						alias_origional=".$db->Quote($data[$i]->menu_alias).",
						domain = ".$db->Quote($link).",
						publish = ".$public ."
					ON DUPLICATE KEY UPDATE 
				  		title=".$db->Quote($data[$i]->title).",
				  		alias_origional=".$db->Quote($data[$i]->menu_alias).",
				  		domain = ".$db->Quote($link).",
						parent=".$db->Quote($data[$i]->parent);
				  
		$db->setQuery ( $query );
		
		if (!$db->query()) {
			$message	=	'#341 models Newsktdt mosModelNewsktdtGetNews. Invalid get news content';
			array_push($arrErr,$message);
			return false;
		}		
	}
	return true;
}

////////////////////////////////////////////////////////////////////////////
/////////		FOR AUTOPRO			//////////
////////////////////////////////////////////////////////////////////////////
function mosModelCategoryGetATP($url = null)
{
	if ($url == null) {
		$url		=	'http://autopro.com.vn/home.chn';
	}
	
	$html		= 	loadHtml($url);
	$nav_menu = $html->find('ul[id="Header1_mainMenu"]',0);
	$nav_menu_list = $nav_menu->innertext;
	
	$arr_menu = array();
	
	$reg_menu = '/<a href="\/([^"]+).chn"[^>]*title="([^"]*)"[^>]*>(.*?)<\/a>/ism';
	
	preg_match_all($reg_menu,$nav_menu_list,$matches_parent);
	
	for ($i=0;$i<count($matches_parent[3]);$i++)
	{
		if ($matches_parent[1][$i] == 'home') {
			continue;
		}
		$obj_menu_parent = new stdClass();
		
		$obj_menu_parent->alias_original	=	$matches_parent[1][$i];
		$obj_menu_parent->title 			= 	trim(strip_tags($matches_parent[3][$i]));
		if ($obj_menu_parent->title == '') {
			$obj_menu_parent->title 		= 	trim(strip_tags($matches_parent[2][$i]));
		}
		$obj_menu_parent->parent = '';
		$obj_menu_parent->public = 1;
		
	// sub menu
		$url_sub = 'http://autopro.com.vn/'.$obj_menu_parent->alias_original.'.chn';
		$html_sub		= 	loadHtml($url_sub);
		
		$sub_menu = $html_sub->find('div[id="ctl00_Header1_subMenu"]',0);
		$str_sub_menu = $sub_menu->innertext;

		$str_sub_menu = str_replace("\r\n",'',$str_sub_menu);

		$reg_sub_menu = '/<a href="\/([^"]+).chn"[^>]*>(.*?)<\/a>/ism';
		
		preg_match_all($reg_sub_menu,$str_sub_menu,$matches_sub);
		if ($matches_sub[1]) {
			$obj_menu_parent->public = 0;
			$arr_menu[] = $obj_menu_parent;
			for ($j=0;$j<count($matches_sub[2]);$j++)
			{				
				$obj_menu_sub = new stdClass();
				
				$obj_menu_sub->alias_original	=	$matches_sub[1][$j];
				$obj_menu_sub->title			=	trim(strip_tags($matches_sub[2][$j]));
				$obj_menu_sub->parent			=	$matches_parent[1][$i];
				$obj_menu_sub->public = 1;
				$arr_menu[] 					=	$obj_menu_sub;
			}
		}else {
			$arr_menu[] = $obj_menu_parent;
		}		
	}	
	return $arr_menu;
}


// Lưu category vào csdl
function mosModelCategorySaveATP($data)
{
	global $database,$arrErr;
	
	$db	=	& $database;
	
	for ($i=0; $i<count($data); $i++)
	{		
		$link	=	'http://autopro.com.vn/'.$data[$i]->alias_original;
		if ($data[$i]->parent != '') {
			$query = "INSERT INTO #__article2010_category_autopro 
							SET title = ".$db->Quote($data[$i]->title).",
							parent = (SELECT B.id FROM #__article2010_category_autopro as B WHERE B.alias_origional = ".$db->Quote($data[$i]->parent)."),
							alias_origional = ".$db->Quote($data[$i]->alias_original).",
							domain = ".$db->Quote($link).",
							publish = ".$data[$i]->public."
					  ON DUPLICATE KEY UPDATE 
					  		title = ".$db->Quote($data[$i]->title).",							
							domain = ".$db->Quote($link).",
							publish = ".$data[$i]->public.",
							parent = (SELECT C.id FROM #__article2010_category_autopro as C WHERE C.alias_origional = ".$db->Quote($data[$i]->parent).")";
		}
		else {
			$query = "INSERT INTO #__article2010_category_autopro 
						SET 
						title = ".$db->Quote($data[$i]->title).",
						parent = '',
						alias_origional=".$db->Quote($data[$i]->alias_original).",
						domain = ".$db->Quote($link).",
						publish = ".$data[$i]->public."
				  ON DUPLICATE KEY UPDATE 
				  		title = ".$db->Quote($data[$i]->title).",
				  		domain = ".$db->Quote($link).",						
						publish = ".$data[$i]->public.",
						parent = ''";
		}
		$db->setQuery ( $query );
		
		if (!$db->query()) {
			$message	=	'#341 models Newsktdt mosModelNewsktdtGetNews. Invalid get news content';
			array_push($arrErr,$message);
			return false;
		}		
	}
	return true;
}

////////////////////////////////////////////////////////////////////////////
/////////		FOR VNMEDIA			//////////
////////////////////////////////////////////////////////////////////////////
function mosModelCategoryGetVNM($url = null)
{
	
	if ($url == null) {
		$url		=	'http://www6.vnmedia.vn/home/include/menuMedia_1_0_.html';
	}
	
	$browser	=	new phpWebHacks();
	$response	=	$browser->get($url);
	
	$response	=	preg_replace('/\s+/ism',' ',$response);
	$response	=	preg_replace('/\)/ism','))',$response);
	$arr_command	=	explode(');',$response);	
	$arr_menu	=	array();
	$parent		=	-1;
	for ($i=0;$i<count($arr_command); $i++)
	{
		$command	=	$arr_command[$i];
		//remove command is coment
		if (preg_match('/\/\/stm_ai/ism',$command)) {
			continue;
		}
		$obj_menu	=	new stdClass();
		$_parent	=	$parent;
		if (!preg_match('/(stm_ai|stm_aix)\("([^"]*)",.*?(\[.*?\]).*?\)/ism',$command,$matches)) {
			continue;
		}
		
		if ($matches[2] == 'p1i0') {
			$obj_menu->parent	=	$parent;
		}else {
			$obj_menu->parent	=	-1;
			$parent			=	count($arr_menu);
		}

		$obj_json	=	json_decode($matches[3]);
		
		if (isset($obj_json[1]) and isset($obj_json[7]) ) {			
			$obj_menu->title	=	$obj_json[1];			
			$obj_menu->link		=	$obj_json[7];
			if (preg_match('/.*?\?CatId=(\d+)/ism',$obj_menu->link,$matches_catid)) {
				$obj_menu->id	=	$matches_catid[1];	
			}else if (preg_match('/.*?\/middle_page\.asp/ism',$obj_menu->link,$matches_catid)) {
				$obj_menu->id	=	91;
				$obj_menu->parent	=	-1;
				$parent			=	count($arr_menu);
			}else {				
				$parent		=	$_parent;
				continue;	
			}	
		$arr_menu[]			=	$obj_menu;	
		}else {
			$parent		=	$_parent;
			continue;	
		}			
	}
	return $arr_menu;
}

function mosModelCategorySaveVNM($data)
{
	global $database,$arrErr;	
	
	$db	=	& $database;
	$arr_parant		=	array();
	//dump_data($data); die();
	for ($i=0; $i<count($data); $i++)
	{
		$menu	=	$data[$i];
		
		// la con
		if ($menu->parent != -1) {
			$parent	=	intval($data[$menu->parent]->id)*10;
			$id_origional	=	intval($menu->id);
			$publish	=	1;
		}else {
			$parent	=	0;
			$id_origional	=	intval($menu->id)*10;
			$publish	=	0;
		}
		$query	=	'INSERT INTO `#__article2010_category_vnmedia`'. 
						' SET id_origional = '.$db->quote($id_origional).
							', title = ' . $db->quote($menu->title).
							', parent = '. $db->quote($parent).
							', alias_origional = '. $db->quote($menu->link).
							', domain = ' .$db->quote('http://vnmedia.vn'.$menu->link).
							', publish = '. $publish;
		$db->setQuery($query);
//		echo $db->getQuery();
//		echo '<br />';
		$db->query();
	}
	return true;
}


////////////////////////////////////////////////////////////////////////////
/////////		FOR AUTOHUI			//////////
////////////////////////////////////////////////////////////////////////////
function mosModelCategoryGetATH($url = null)
{
	if ($url == null) {
		$url		=	'http://www.oto-hui.com';
	}
	$arr_menu = array();
	$html		= 	loadHtml($url);
	$nav_menu_top = $html->find('div[id="menu_top"]',0);
	$nav_top_menu_list = $nav_menu_top->innertext;
	$nav_menu_sub = $html->find('div[id="menuundermenumain"]',0);
	
	$reg_link_parent = '/<a[^>]*href="(\/([^"]+)\.html)"[^>]*id="menu_parent_(\d*)"[^>]*>(.*?)<\/a>/ism';
	
	preg_match_all($reg_link_parent,$nav_top_menu_list,$matches_parent);

	$reg_id_origional	=	'/\w(\d+)\/([^\.]+)/ism';
	for ($i=0; $i<count($matches_parent[2]);$i++)
	{
		$obj_parent_menu 	= 	new stdClass();
		
		if ($matches_parent[2][$i] == 'index') {
			continue;
		}
		if (!preg_match($reg_id_origional, $matches_parent[2][$i], $maches_id)) {			
				continue;
			}		
		$id_origional	=	$maches_id[1];
		
		$obj_parent_menu->alias_original = $maches_id[2];
		$obj_parent_menu->title = $matches_parent[4][$i];
		$obj_parent_menu->parent = '';
		$obj_parent_menu->id = $maches_id[1];
		$obj_parent_menu->link = $matches_parent[1][$i];
		$obj_parent_menu->public = 0;

		$find_sub_item = 'div[id="menu'.$matches_parent[3][$i].'"]';
		
		$obj_sub_menu_group = $nav_menu_sub->find($find_sub_item,0);
		
		$arr_menu[] = $obj_parent_menu;
		
		if($obj_sub_menu_group)
		{		
			$arr_link	=	$obj_sub_menu_group->find('a[class="mlink"]');			
			
			for ($j=0;$j<count($arr_link);$j++)
			{
				$obj_sub_menu 	= 	new stdClass();							
				$obj_sub_menu->link = $arr_link[$j]->href;				
				if (!preg_match($reg_id_origional, $obj_sub_menu->link, $maches_sub_id)) {				
					continue;
				}			
				$obj_sub_menu->id 				= $maches_sub_id[1];
				$obj_sub_menu->alias_original 	= $maches_sub_id[2];
				$obj_sub_menu->title 			= strip_tags($arr_link[$j]->innertext);
				$obj_sub_menu->parent 			= $obj_parent_menu->id;				
				$obj_sub_menu->public 			= 1;				
				$arr_menu[] = $obj_sub_menu;
			}
		}		
	}

	return $arr_menu;
}


// Lưu category vào csdl
function mosModelCategorySaveATH($data)
{
	global $database,$arrErr;
	$db	=	& $database;
	
	for ($i=0; $i<count($data); $i++)
	{
		$query = "INSERT INTO #__article2010_category_autohui 
						SET id_origional = ".$db->Quote($data[$i]->id).",
						title = ".$db->Quote($data[$i]->title).",
						parent = ".$db->Quote($data[$i]->parent).",
						alias_origional=".$db->Quote($data[$i]->alias_original).",
						domain = ".$db->Quote('http://www.oto-hui.com'.$data[$i]->link).", 
						publish = ".$db->Quote($data[$i]->public)."
				  ON DUPLICATE KEY UPDATE 
				  		title = ".$db->Quote($data[$i]->title).",
						alias_origional = ".$db->Quote($data[$i]->alias_original).",
						domain = ".$db->Quote('http://www.oto-hui.com'.$data[$i]->link).", 
						publish = ".$db->Quote($data[$i]->public).",						
						parent = ".$db->Quote($data[$i]->parent);
	
		$db->setQuery ( $query );
		echo $db->getQuery();
		if (!$db->query()) {
			$message	=	'#341 models Newsktdt mosModelNewsktdtGetNews. Invalid get news content';
			array_push($arrErr,$message);
			return false;
		}		
	}
	return true;
}

////////////////////////////////////////////////////////////////////////////
/////////		FOR DAN VIET			//////////
////////////////////////////////////////////////////////////////////////////
function mosModelCategoryGetDV($url = null)
{
	if ($url == null) {
		$url		=	'http://danviet.vn';
	}
	$arr_menu = array();
	$html		= 	loadHtml($url);
	
	$nav_menu_top = $html->find('ul[id="TopNav"]',0);
	$nav_top_menu_list = $nav_menu_top->innertext;
	
	// <a href="/p1c24/thoi-su.htm">Thời sự</a>
	$reg_link_parent = '/<a href="(\/p(\d*)c(\d*)\/([^"]+).htm)">(.*?)<\/a>/ism';
	
	preg_match_all($reg_link_parent,$nav_top_menu_list,$matches_parent);
	
	for ($i=0; $i<count($matches_parent[1]);$i++)
	{
		$obj_parent_menu 	= 	new stdClass();
		
		if ($matches_parent[1][$i] == '/') {
			continue;
		}
		
		$obj_parent_menu->id_origional = $matches_parent[3][$i];
		$obj_parent_menu->alias_original = $matches_parent[4][$i];
		$obj_parent_menu->title = trim($matches_parent[5][$i]);
		$obj_parent_menu->parent = 0;
		$obj_parent_menu->link = $matches_parent[1][$i];
		$obj_parent_menu->public = 0;
		$arr_menu[] = $obj_parent_menu;
	}
	
	
	$nav_menu_sub = $html->find('div[id="TopSub"]',0);
	$obj_sub_menu_group = $nav_menu_sub->innertext;

	// <a title="Ch&#237;nh trị" id='sub24_40' href="/p24c40/chinh-tri.htm">Ch&#237;nh trị</a>
	$reg_link_sub = '/<a[^>]+href="(\/p(\d*)c(\d*)\/([^"]+).htm)">(.*?)<\/a>/ism';
	preg_match_all($reg_link_sub,$obj_sub_menu_group,$matches_sub);
	
	for ($j=0;$j<count($matches_sub[4]);$j++)
	{
		$obj_sub_menu 	= 	new stdClass();
		$obj_sub_menu->id_origional = $matches_sub[3][$j];
		$obj_sub_menu->alias_original = $matches_sub[4][$j];
		$obj_sub_menu->title = trim($matches_sub[5][$j]);
		$obj_sub_menu->parent = $matches_sub[2][$j];
		$obj_sub_menu->link = $matches_sub[1][$j];
		$obj_sub_menu->public = 1;
		$arr_menu[] = $obj_sub_menu;
	}
	
	return $arr_menu;
}


// Lưu category vào csdl
function mosModelCategorySaveDV($data)
{
	global $database,$arrErr;
	$db	=	& $database;
	
	for ($i=0; $i<count($data); $i++)
	{
		
		
		$query = "INSERT INTO #__article2010_category_danviet 
						SET 
						title = ".$db->Quote($data[$i]->title).",
						parent = ".$db->Quote($data[$i]->parent).",
						id_origional = ".$data[$i]->id_origional.",
						alias_origional=".$db->Quote($data[$i]->alias_original).",
						domain = ".$db->Quote('http://danviet.vn'.$data[$i]->link).",
						publish = ".$db->Quote($data[$i]->public)."
				  ON DUPLICATE KEY UPDATE 
				  		title = ".$db->Quote($data[$i]->title).",
						alias_origional = ".$db->Quote($data[$i]->alias_original).",
						publish = ".$db->Quote($data[$i]->public).",
						domain = ".$db->Quote('http://danviet.vn'.$data[$i]->link).",
						parent = ".$db->Quote($data[$i]->parent);
	
		$db->setQuery ( $query );
		
		if (!$db->query()) {
			$message	=	'#341 models Newsktdt mosModelNewsktdtGetNews. Invalid get news content';
			array_push($arrErr,$message);
			return false;
		}		
	}
	return true;
}
////////////////////////////////////////////////////////////////////////////
/////////		FOR TIN TUC TIM NHANH			//////////
////////////////////////////////////////////////////////////////////////////
function mosModelCategoryGetTTTN($url = null)
{
	if ($url == null) {
		$url		=	'http://tintuc.timnhanh.com';
	}
	$arr_menu = array();
	$html		= 	loadHtml($url);
	
	$nav_menu_top = $html->find('ul[class="mainmenu"]',0);
	$nav_menu_top->find('li[id="trang-chu"]',0)->outertext = "";
	$nav_top_menu_list = $nav_menu_top->innertext;
	$nav_top_menu_list = str_replace('http://tintuc.timnhanh.com','',$nav_top_menu_list);
	
	// <a href="http://tintuc.timnhanh.com/topnew.htm">Top 100</a>
	// <a href="http://tintuc.timnhanh.com/xa-hoi/phong-su.htm">Phóng sự</a>
	$reg_link = '/<a href="(([^"]*)\/([^"]+)\.htm)">(.*?)<\/a>/ism';
	
	preg_match_all($reg_link, $nav_top_menu_list, $matches_link);
	
	for ($i=0; $i<count($matches_link[1]);$i++)
	{
		$obj_menu 	= 	new stdClass();
		$arr_link = explode('/',$matches_link[1][$i]);
		$obj_menu->link	=	$matches_link[1][$i];
		if (count($arr_link) == 2)
		{
			$obj_menu->alias_original = $matches_link[3][$i];
			$obj_menu->title = trim(strip_tags($matches_link[4][$i]));
			$obj_menu->parent = 0;			
			$arr_menu[] = $obj_menu;
		}
		else if (count($arr_link) == 3)
		{
			$obj_menu->alias_original = $matches_link[3][$i];
			$obj_menu->title = trim(strip_tags($matches_link[4][$i]));
			$obj_menu->parent = str_replace('/','',$matches_link[2][$i]);			
			$arr_menu[] = $obj_menu;
		}
	}
	return $arr_menu;
}


// Lưu category vào csdl
function mosModelCategorySaveTTTN($data)
{
	global $database,$arrErr;
	$db	=	& $database;
	
	for ($i=0; $i<count($data); $i++)
	{
		$query = "INSERT INTO #__article2010_category_tt_timnhanh 
						SET 
						title = ".$db->Quote($data[$i]->title).",
						parent = ".$db->Quote($data[$i]->parent).",
						alias_origional=".$db->Quote($data[$i]->alias_original).",
						domain=".$db->Quote('http://tintuc.timnhanh.com'.$data[$i]->link).",
						publish = 1 
				  ON DUPLICATE KEY UPDATE 
				  		title = ".$db->Quote($data[$i]->title).",
						alias_origional = ".$db->Quote($data[$i]->alias_original).",						
						domain=".$db->Quote('http://tintuc.timnhanh.com'.$data[$i]->link).",
						parent = ".$db->Quote($data[$i]->parent);
	
		$db->setQuery ( $query );
		
		if (!$db->query()) {
			$message	=	'#341 models Newsktdt mosModelNewsktdtGetNews. Invalid get news content';
			array_push($arrErr,$message);
			return false;
		}		
	}
	return true;
}


////////////////////////////////////////////////////////////////////////////
/////////		FOR TIN TUC TIEN PHONG			//////////
////////////////////////////////////////////////////////////////////////////
function mosModelCategoryGetTP($url = null)
{
	if ($url == null) {
		$url		=	'http://www.tienphong.vn/';
	}
	$arr_menu = array();
	$html		= 	loadHtml($url);
	
	$list_menu_parent	= 	$html->find('div[class="menuBarTOp"]',0);
	$list_menu_sub	 	= 	$html->find('div[class="menuNarSubTop"]',0);

	$list_menu_parent	=	$list_menu_parent->find('li');	
	$list_category		=	array();
	for ($i=0; $i<count($list_menu_parent); $i++)
	{
		$menu_parent	=	$list_menu_parent[$i];		
		if (!preg_match('/mnu_(\d+)/ism',$menu_parent->id, $matches_id)) {
			continue;
		}
		$obj_menu	=	new stdClass();
		$obj_menu->title	=	$menu_parent->first_child()->first_child()->innertext;
		$obj_menu->link		=	$menu_parent->first_child()->href;	
		
		$reg	=	'ul[id="mnu_'. $matches_id[1] .'_ul_lv2"]';
		$menu_subs			=	$list_menu_sub->find($reg,0);
		$obj_menu->parent	=	-1;	
		if (!$menu_subs			=	$menu_subs->find('li')) {
			$list_category[]	=	$obj_menu;	
			continue;
		}
		$list_category[]	=	$obj_menu;
		$parent	=	count($list_category) - 1;
		for ($j=0;$j<count($menu_subs); $j++)
		{
			$menu_sub	=	$menu_subs[$j];
			$obj_menu	=	new stdClass();
			$obj_menu->title	=	$menu_sub->first_child()->innertext;
			$obj_menu->link		=	$menu_sub->first_child()->href;
			$obj_menu->parent	=	$parent;
			$list_category[]	=	$obj_menu;
		}		
	}
	return $list_category;	
} 


// Lưu category vào csdl
function mosModelCategorySaveTP($data)
{
	global $database,$arrErr;
	$db	=	& $database;	
//dump_data($data); die();
	$arr_parent	=	array();
	for ($i=0; $i<count($data); $i++)
	{
		$menu	=	$data[$i];
		
		$parent	=	0;
		// la con		
		if ($menu->parent != -1) 
			$parent	=	intval($arr_parent[$menu->parent]);
		
		$query	=	'INSERT INTO `#__article2010_category_tienphong`'. 
					' SET title = ' . $db->quote($menu->title).
						', parent = '. $db->quote($parent).							
						', domain = ' .$db->quote($menu->link).
						', publish = '. 1;
		$db->setQuery($query);
//		echo $db->getQuery();		
		if (!$db->query()) 
		{
			$message	=	'#341 models Newsktdt mosModelNewsktdtGetNews. Invalid get news content';
			array_push($arrErr,$message);
			return false;
		}
		if ($menu->parent == -1) 
			$arr_parent[$i]	=	mysql_insert_id();	
	}
	return true;
}

////////////////////////////////////////////////////////////////////////////
/////////		FOR AN NINH THU SO			//////////
////////////////////////////////////////////////////////////////////////////
function mosModelCategoryGetANTD($url = null)
{
	if ($url == null) {
		$url		=	'http://www.anninhthudo.vn/';
	}
	
	$arr_menu = array();
	$browser	=	new phpWebHacks();
	$response	=	$browser->get($url);
	$html		=	loadHtmlString($response);		
	
	$list_menu	= 	$html->find('div[id="mainnav"]',0)->first_child()->children();
	$href	=	new href();
	$reg_id	=	'/\/(\d+)\.antd/ism';
	$list_category		=	array();
	for ($i=0; $i<count($list_menu); $i++)
	{
		$menu	=	$list_menu[$i];
		
		$obj_menu	=	new stdClass();
		$obj_menu->title	=	trim(strip_tags($menu->first_child()->find('a',0)->innertext));
		$obj_menu->link		=	$href->process_url($menu->first_child()->find('a',0)->href,$url);
		if (!preg_match($reg_id,$obj_menu->link,$matches_id)) {
			continue;
		}
		$obj_menu->id		=	$matches_id[1];		
		
		$obj_menu->parent	=	-1;	
		$list_category[]	=	$obj_menu;
		if (!$list_menu_sub		= 	$menu->find('ul[class="sub"]',0)) {			
			continue;
		}		
		$list_menu_sub	=	$list_menu_sub->find('li');
		$parent	=	$matches_id[1];
		for ($j=0;$j<count($list_menu_sub); $j++)
		{
			$menu_sub	=	$list_menu_sub[$j];
			$obj_menu	=	new stdClass();
			$obj_menu->title	=	trim(strip_tags($menu_sub->first_child()->innertext));			
			$obj_menu->link		=	$href->process_url($menu_sub->first_child()->href,$url);
			if (!preg_match($reg_id,$obj_menu->link,$matches_id)) {
				continue;
			}
			$obj_menu->id		=	$matches_id[1];
			$obj_menu->parent	=	$parent;
			$list_category[]	=	$obj_menu;
		}		
	}	
	return $list_category;	
} 


// Lưu category vào csdl
function mosModelCategorySaveANTD($data)
{
	global $database,$arrErr;
	$db	=	& $database;	
//dump_data($data); die();	
	for ($i=0; $i<count($data); $i++)
	{
		$menu	=	$data[$i];
		$parent		=	0;
		$publish	=	1;
		// la con		
		if ($menu->parent != -1) 
		{
			$parent		=	$menu->parent;
			$publish	=	1;
		}
		
		$query	=	'INSERT INTO `#__article2010_category_anninhthudo`'. 
					' SET title = ' . $db->quote($menu->title).						
						', 	id_origional = ' .$db->quote($menu->id).
						', parent = '. $db->quote($parent).
						', domain = ' .$db->quote($menu->link).
						', lastGet_param = ' .$db->quote('').
						', publish = '. $publish;
		$db->setQuery($query);
//		echo $db->getQuery();		
		if (!$db->query()) 
		{
			$message	=	'#341 models Newsktdt mosModelNewsktdtGetNews. Invalid get news content';
			array_push($arrErr,$message);
			return false;
		}		
	}
	return true;
}
////////////////////////////////////////////////////////////////////////////
/////////		FOR BONGDAPLUS			//////////
////////////////////////////////////////////////////////////////////////////
function mosModelCategoryGetBDP($url = null)
{
	if ($url == null) {
		$url		=	'http://bongdaplus.vn/Home.bbd';
	}
	$arr_menu = array();
	$browser	=	new phpWebHacks();	
	
	$html		=	$browser->get($url);
	$html		=	loadHtmlString($html);
	
	$list_menu	= 	$html->find('div[id="navigation"]',0)->find('li[class="hasmenu"]');
	$reg_id		=	'/\/(\d+)\.bbd/ism';
	
	$href	=	new href();	
	$list_category		=	array();
	for ($i=0; $i<count($list_menu); $i++)
	{
		$menu	=	$list_menu[$i];			
		$obj_menu	=	new stdClass();
		$obj_menu->title	=	trim(strip_tags($menu->first_child()->first_child()->innertext));
		$obj_menu->link		=	$href->process_url($menu->first_child()->first_child()->href,$url);
		$obj_menu->parent	=	-1;
		
		if (!preg_match($reg_id,$obj_menu->link,$matches_id)) {
			continue;
		}
		$obj_menu->id		=	$matches_id[1];		
		$list_category[]	=	$obj_menu;
		if (!$list_menu_sub		= 	$menu->find('div[class="subnav"]',0)->first_child()) {			
			continue;
		}		
		$list_menu_sub	=	$list_menu_sub->children;		
		$parent	=	$matches_id[1];
		for ($j=0;$j<count($list_menu_sub); $j++)
		{
			$menu_sub	=	$list_menu_sub[$j]->first_child();
			$obj_menu	=	new stdClass();
			$obj_menu->title	=	trim(strip_tags($menu_sub->innertext));			
			$obj_menu->link		=	$href->process_url($menu_sub->href,$url);
			if (!preg_match($reg_id,$obj_menu->link,$matches_id)) {
				continue;
			}
			$obj_menu->parent	=	$parent;
			$obj_menu->id		=	$matches_id[1];
			if ($obj_menu->id == $parent) {
				continue;
			}				
			$list_category[]	=	$obj_menu;
		}	
	}	
	return $list_category;	
} 


// Lưu category vào csdl
function mosModelCategorySaveBDP($data)
{
	global $database,$arrErr;
	$db	=	& $database;
	$arr_parent	=	array();
	for ($i=0; $i<count($data); $i++)
	{
		$menu	=	$data[$i];
		$parent		=	0;
		$publish	=	0;
		// la con		
		if ($menu->parent != -1) 
		{
			$parent		=	$menu->parent;
			$publish	=	1;
		}
		
		$query	=	'INSERT INTO `#__article2010_category_bongdaplus`'. 
					' SET title = ' . $db->quote($menu->title).
						', parent = '. $db->quote($parent).
						', 	id_origional = ' .$db->quote($menu->id).
						', domain = ' .$db->quote($menu->link).
						', publish = '. $publish;
		$db->setQuery($query);
//		echo $db->getQuery();		
		if (!$db->query()) 
		{
			$message	=	'#341 models category mosModelCategorySaveBDP. Invalid get news content';
			array_push($arrErr,$message);
			return false;
		}		
	}
	return true;
}

////////////////////////////////////////////////////////////////////////////
/////////		FOR TIN TUC TIM NHANH CHUYEN MUC			//////////
////////////////////////////////////////////////////////////////////////////
function mosModelCategoryGetTTTNCM($url = null)
{
	$section		=	$_REQUEST['section'];
	if ($url == null) {
		$url		=	'http://tintuc.timnhanh.com/'.$section.'.htm';
	}
	$arr_menu = array();
	$browser	=	new phpWebHacks();
	if (!$source_content	=	$browser->get($url)) {
		echo 'No connect. Please press f5 to refresh';
		die();
	}
	$html	=	loadHtmlString($source_content);
	
	if ($section == 'su-kien') {
		
		$nav_menu_top = $html->find('select[id="cate_su_kien"]',0);
		$obj_cat = $nav_menu_top->find('option');
		
		for ($i=0; $i<count($obj_cat);$i++)
		{			
			$obj_menu 	= 	new stdClass();
			$str_cat = $obj_cat[$i]->outertext;
			$reg_parent_alias = '/<option value="\/([^"]+)"[^>]*>(.*?)<\/option>/ism';
			
			if (!preg_match($reg_parent_alias,$str_cat,$matches_cat)) {
				continue;
			}
			
			$link_next = '';
			$next = 1;
			$url_cat = 'http://tintuc.timnhanh.com/'.$section.'/'.$matches_cat[1].'.htm';
			
			while($next != 0)
			{
				
				$source_cat	=	$browser->get($url_cat);
				$html_cat	=	loadHtmlString($source_cat);
				
				$obj_content = $html_cat->find('div[class="content"]',0);
				
				$str_group = $obj_content->find('ul[class="group"]',0);
				$group_content = $str_group->innertext;
	// <br /><a href="http://tintuc.timnhanh.com/chuyen-de/xa-hoi/giao-duc/35A4F284/Ke-hoach-di-doi-cac-truong-Cao-dang-Dai-hoc.htm" title="Kế hoạch di dời các trường Cao đẳng, Đại học">Kế hoạch di dời các trường...</a></li>			
				//$reg_cat_link = '/<a href=".*?\/chuyen-de\/[^\/]+\/([^\/]+)\/([^\.]+)\.htm"><img class="thumb" alt="([^"]+)"[^>]*(\/>|>)<\/a>/ism';
				$reg_cat_link = '/<br \/><a href="(.*?\/chuyen-de(\/[^\/]+|)\/([^\/]+)\/([^\/]+)\/([^\.]+)\.htm)" title="([^"]+)">.*?<\/a>/ism';
				preg_match_all($reg_cat_link,$group_content,$matches_cat_link);
				
				if ($matches_cat_link) {
					for ($j=0;$j<count($matches_cat_link[1]);$j++)
					{
						$obj_menu_cat = new stdClass();
						$obj_menu_cat->section		=	$section;
						$obj_menu_cat->cat_parent	=	$matches_cat_link[3][$j];
						$obj_menu_cat->id_origional	=	$matches_cat_link[4][$j];
						$obj_menu_cat->cat_alias	=	$matches_cat_link[5][$j];
						$obj_menu_cat->title		=	$matches_cat_link[6][$j];
						$obj_menu_cat->link		=	$matches_cat_link[1][$j];
						$arr_menu[] = $obj_menu_cat;
					}
				}
				$isnext = $obj_content->find('li[class="next"]',0);
				if ($isnext) {
					$next = 1;
					$url_cat = $isnext->first_child()->href;
				}
				else $next = 0;
				
			}
		}
		
	}
	else if ($section == 'nhan-vat')
	{
		$obj_content_nv = $html->find('div[class="block_left_tc"]',0);
		$str_group_nv 	= $obj_content_nv->innertext;		
		// (http://tintuc.timnhanh.com/chuyen-de/(xa-hoi)/(giao-duc)/(35A4F27C)/(Guong-thanh-cong).htm)
		$reg_cat_link_nv = '/<br \/><a href="(.*?\/chuyen-de(\/[^\/]+|)\/([^\/]+)\/([^\/]+)\/([^\.]+)\.htm)" title="([^"]+)">.*?<\/a>/ism';
		preg_match_all($reg_cat_link_nv,$str_group_nv,$reg_cat_link_nv);
		
		if ($reg_cat_link_nv) {
			for ($j=0;$j<count($reg_cat_link_nv[1]);$j++)
			{
				$obj_menu_cat_nv = new stdClass();
				$obj_menu_cat_nv->section		=	$section;
				$obj_menu_cat_nv->cat_parent	=	$reg_cat_link_nv[3][$j];
				$obj_menu_cat_nv->id_origional	=	$reg_cat_link_nv[4][$j];
				$obj_menu_cat_nv->cat_alias	=	$reg_cat_link_nv[5][$j];
				$obj_menu_cat_nv->title		=	$reg_cat_link_nv[6][$j];
				$obj_menu_cat_nv->link		=	$reg_cat_link_nv[1][$j];
				$arr_menu[] = $obj_menu_cat_nv;
			}
		}
	}
	
	return $arr_menu;
}


// Lưu category vào csdl
function mosModelCategorySaveTTTNCM($data)
{
	global $database,$arrErr;
	$db	=	& $database;
	
	for ($i=0; $i<count($data); $i++)
	{
		$query = "INSERT INTO #__article2010_category_tt_timnhanh_chuyenmuc 
						SET 
						title = ".$db->Quote($data[$i]->title).",
						section = ".$db->Quote($data[$i]->section).",
						cat_parent = ".$db->Quote($data[$i]->cat_parent).",
						cat_alias=".$db->Quote($data[$i]->cat_alias).",
						id_origional=".$db->Quote($data[$i]->id_origional).",
						link=".$db->Quote($data[$i]->link).",
						publish = 1 
				  ON DUPLICATE KEY UPDATE 
				  		title = ".$db->Quote($data[$i]->title).",
						section = ".$db->Quote($data[$i]->section).",
						cat_parent = ".$db->Quote($data[$i]->cat_parent).",
						link = ".$db->Quote($data[$i]->link).",
						cat_alias=".$db->Quote($data[$i]->cat_alias);
						
						
	
		$db->setQuery ( $query );

		if (!$db->query()) {
			$message	=	'#341 models Newsktdt mosModelNewsktdtGetNews. Invalid get news content';
			array_push($arrErr,$message);
			return false;
		}		
	}
	return true;
}

////////////////////////////////////////////////////////////////////////////
/////////		FOR PHAPLUATTP			//////////
////////////////////////////////////////////////////////////////////////////
function mosModelCategoryGetPLHCM($url = null)
{
	if ($url == null) {
		$url		=	'http://phapluattp.vn/';
	}
	
	$arr_menu = array();
	$arr_parent_menu	=	array();
	$browser	=	new phpWebHacks();
	if (!$source_content	=	$browser->get($url)) {
		echo 'No connect. Please press f5 to refresh';
		die();
	}
	$reg_id	=	'/\/p0c(\d+)\//ism';	
	$href	=	new href();
	$html	=	loadHtmlString($source_content);
	if ($header = $html->find('div[class="header"]',0)) {
		$menu_parent	=	$header->first_child();
		if ($menu_parent	=	$menu_parent->find('li')) {
//														
//			$menu_parent[]->outertext	=	'<li><a href="/p0c1063/tap-chi-phap-luat.htm">Tạp chí pháp luật</a></li>';
//			$menu_parent[]->outertext	=	'<li><a href="/p0c1059/tap-chi-ky-nguyen-so.htm">Tạp chí kỷ nguyên số</a></li>';
//			$menu_parent[]->outertext	=	'<li><a href="/p0c1060/tap-chi-suc-khoe-gia-dinh.htm">Tạp chí sức khỏe gia đình</a></li>';
//			
			for ($i=2; $i<count($menu_parent); $i++)
			{				
				$obj_menu	=	new stdClass();
				$obj_menu->link	=	$href->process_url($menu_parent[$i]->first_child()->href,$url);
				$obj_menu->title=	strip_tags($menu_parent[$i]->first_child()->innertext);
				if (!preg_match($reg_id,$obj_menu->link, $matches_id)) {
					continue;
				}
				$obj_menu->id		=	$matches_id[1];
				$obj_menu->parent	=	0;
				$arr_parent_menu[]	=	$obj_menu;
			}
			
			$obj_menu	=	new stdClass();
			//
			$obj_menu->link	=	'http://phapluattp.vn/p0c1063/tap-chi-phap-luat.htm';
			$obj_menu->title=	'Tạp chí pháp luật';
			$obj_menu->id	=	1063;
			$obj_menu->parent	=	0;
			$arr_parent_menu[]	=	$obj_menu;
			//
			$obj_menu	=	new stdClass();
			$obj_menu->link	=	'http://phapluattp.vn/p0c1059/tap-chi-ky-nguyen-so.htm';
			$obj_menu->title=	'Tạp chí kỷ nguyên số';
			$obj_menu->id	=	1059;
			$obj_menu->parent	=	0;
			$arr_parent_menu[]	=	$obj_menu;
			//
			$obj_menu	=	new stdClass();
			$obj_menu->link	=	'http://phapluattp.vn/p0c1060/tap-chi-suc-khoe-gia-dinh.htm';
			$obj_menu->title=	'Tạp chí sức khỏe gia đình';
			$obj_menu->id	=	1060;
			$obj_menu->parent	=	0;
			$arr_parent_menu[]	=	$obj_menu;
			
			
			for ($i=0; $i<count($arr_parent_menu); $i++)
			{
				// get sub				
				$parent			=	$arr_parent_menu[$i];
				$arr_menu[]		=	$parent;
				$response_sub	=	$browser->get($parent->link);
				$html			=	loadHtmlString($response_sub);
				if (!$menu_sub	=	$html->find('div[class="subnav"]',0)) {
					continue;
				}
				if (!$menu_sub	=	$menu_sub->find('li')) {
					continue;
				}
				$reg_subid	=	'/\/p'. $parent->id .'c(\d+)\//ism';
				for ($j = 0 ; $j <count($menu_sub); $j++) 
				{
					$obj_menu	=	new stdClass();
					$obj_menu->link	=	$href->process_url($menu_sub[$j]->first_child()->href,$url);
					$obj_menu->title=	strip_tags($menu_sub[$j]->first_child()->innertext);
					if (!preg_match($reg_subid,$obj_menu->link, $matches_subid)) {
						continue;
					}
					$obj_menu->id		=	$matches_subid[1];
					$obj_menu->parent	=	$parent->id;
					$arr_menu[]			=	$obj_menu;
				}
			}
		}
		
	}	
	return $arr_menu;
}


// Lưu category vào csdl
function mosModelCategorySavePLHCM($data)
{
	global $database,$arrErr;
	$db	=	& $database;
	
	for ($i=0; $i<count($data); $i++)
	{
		$menu	=	$data[$i];
		$query	=	'INSERT INTO `#__article2010_category_phapluat_hcm`'. 
					' SET title = ' . $db->quote($menu->title).
						', parent = '. $db->quote($menu->parent).
						', 	id_origional = ' .$db->quote($menu->id).
						', domain = ' .$db->quote($menu->link).
						', publish = 1';				
						
	
		$db->setQuery ( $query );

		if (!$db->query()) {
			$message	=	'#341 models category mosModelCategorySavePLHCM. Invalid store category';
			array_push($arrErr,$message);
			return false;
		}		
	}
	return true;
}
////////////////////////////////////////////////////////////////////////////
/////////		FOR AFAMILY		//////////
////////////////////////////////////////////////////////////////////////////
function mosModelCategoryGetAFAMILY($url = null)
{
	if ($url == null) {
		$url		=	'http://afamily.vn/';
	}
	
	$arr_menu = array();
	$arr_parent_menu	=	array();
	$browser	=	new phpWebHacks();
	if (!$source_content	=	$browser->get($url)) {
		echo 'No connect. Please press f5 to refresh';
		die();
	}
	$reg_id	=	'/\/p0c(\d+)\//ism';	
	$href	=	new href();
	$html	=	loadHtmlString($source_content);
	if ($header = $html->find('ul[class="navi"]',0)) {		
		if ($menu_parent	=	$header->children()) {			
			for ($i=2; $i<count($menu_parent); $i++)
			{
				$menu	=	$menu_parent[$i];
				if (!$menu->find('a')) {
					continue;
				}
				
				$obj_menu	=	new stdClass();
				$obj_menu->link	 =	$href->process_url($menu_parent[$i]->first_child()->href,$url);
				$obj_menu->title =	strip_tags($menu_parent[$i]->first_child()->innertext);
				$obj_menu->parent  = -1;
				$obj_menu->publish = 0;
				$parent	=	count($arr_menu);
				if (!$sub_menu = $menu->find('ul',0)) {
					$obj_menu->publish	=	1;
					$arr_menu[]	=	$obj_menu;
					continue;
				}
				$arr_menu[]	=	$obj_menu;
				$sub_menu	=	$sub_menu->find('li');
				for ($j = 0; $j <count($sub_menu); $j++)
				{
					$item_sub	=	$sub_menu[$j];
					$obj_menu	=	new stdClass();
					$obj_menu->link	 =	$href->process_url($item_sub->first_child()->href,$url);
					$obj_menu->title =	strip_tags($item_sub->first_child()->innertext);
					$obj_menu->parent  = $parent;
					$obj_menu->publish = 1;
					$arr_menu[]	=	$obj_menu;
				}				
			}		
		}
	}
	
	return $arr_menu;
}


// Lưu category vào csdl
function mosModelCategorySaveAFAMILY($data)
{
	global $database,$arrErr;
	$db	=	& $database;
	
	$arr_parent	=	array();
	for ($i=0; $i<count($data); $i++)
	{
		$menu	=	$data[$i];
		$parent	=	0;
		// la con		
		if ($menu->parent != -1) 
			$parent	=	intval($arr_parent[$menu->parent]);
		
		$query	=	'INSERT INTO `#__article2010_category_afamily`'. 
					' SET title = ' . $db->quote($menu->title).
						', parent = '. $db->quote($parent).						
						', link = ' .$db->quote($menu->link).
						', publish = '. $menu->publish;
								
		$db->setQuery ( $query );

		if (!$db->query()) {
			$message	=	'#341 models category mosModelCategorySavePLHCM. Invalid store category';
			array_push($arrErr,$message);
			return false;
		}
		if ($menu->parent == -1) 
			$arr_parent[$i]	=	mysql_insert_id();		
	}
	return true;
}