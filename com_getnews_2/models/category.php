<?php

////////////////////////////////////////////////////////////////////////////
/////////		FOR BAODATVIET			//////////
////////////////////////////////////////////////////////////////////////////
function mosModelCategoryGetBDV($url = null)
{	
	if ($url == null) {
		$url		=	'http://baodatviet.vn/dv/';
	}	
	
	$browser	=	new phpWebHacks();
	$response	=	$browser->get($url);
	$html_obj	=	loadHtmlString($response);
	
	if (!$html_obj->find('ul[id="topnav"]')) {
		echo 'invalid area menu';
		die();
	}
	$list_menu	=	$html_obj->find('ul[id="topnav"]',0);
	
	$p_menu		=	$list_menu->children();	
	$arr_menu	=	array();
	$reg_alias	=	'/\/([^\.\/]+)\.datviet/';	
	for ($i=1;$i<count($p_menu); $i++)
	{
		$obj_menu	=	new stdClass();
		$obj_menu->link = $p_menu[$i]->first_child()->href;
		$obj_menu->title = $p_menu[$i]->first_child()->innertext;
		$obj_menu->parent = -1;
		
		if (!strpos($obj_menu->link,'http://') == 0 or strpos($obj_menu->link,'.datviet') <0) {
			continue;
		}
		if (!preg_match($reg_alias,$obj_menu->link,$matcher_alias)) {
			continue;
		}
		$obj_menu->alias	=	$matcher_alias[1];		
		$obj_menu->published	=	0;	
		$parent	=	count($arr_menu);	
		$arr_menu[]		=	$obj_menu;		
		if ($sub_menu	=	$p_menu[$i]->find('ul[class="children"]',0)) {			
			$list_submenu	=	$sub_menu->children();
			for ($j=0;$j<count($list_submenu);$j++)
			{
				$obj_submenu	=	new stdClass();
				$obj_submenu->link	=	$list_submenu[$j]->first_child()->href;
				$obj_submenu->title	=	$list_submenu[$j]->first_child()->innertext;
				if (!preg_match($reg_alias,$obj_submenu->link,$matcher_alias)) {
					continue;
				}
				$obj_submenu->parent =	$parent;				
				$obj_submenu->alias	 =	$matcher_alias[1];
				$obj_submenu->published	=	1;				
				$arr_menu[]			 =	$obj_submenu;
			}
		}
	}
	return $arr_menu;
}

function mosModelCategorySaveBDV($data)
{
	global $database,$arrErr;	
	$db	=	& $database;	
	$arr_ID		=	array();
	for ($i=0; $i<count($data); $i++)
	{
		$parent	=	0;
		$menu	=	$data[$i];
		if ($menu->parent != -1) {
			$parent	=	$arr_ID[intval($menu->parent)];
		}
		$query	=	'INSERT INTO `#__article2010_category_baodatviet`'. 
						' SET link = '.$db->quote('http://baodatviet.vn'.$menu->link).
							', title = ' . $db->quote($menu->title).
							', parent = '. $db->quote($parent).
							', alias_origional = '. $db->quote($menu->alias).							
							', publish = '. $db->quote($menu->published);
		$db->setQuery($query);
//		echo $db->getQuery();
//		echo '<br />';		
		$db->query();
		$arr_ID[$i] = mysql_insert_id();
	}
	return true;
}

////////////////////////////////////////////////////////////////////////////
/////////		FOR GIAODUC.NET.VN			//////////
////////////////////////////////////////////////////////////////////////////
function mosModelCategoryGetGD($url =null)
{
	if ($url == null) {
		$url		=	'http://giaoduc.net.vn/';
	}
	$browser	=	new phpWebHacks();
	$response	=	$browser->get($url);
	$html_obj	=	loadHtmlString($response);
	$arrMenu	=	array();
	if ($boxsection = $html_obj->find('div[id="boxsection"]',0)) {
		$c_boxgiua1	=	$boxsection->find('div[class="c_boxgiua1"]');
		for ($i=0;$i<count($c_boxgiua1); $i++)
		{		
			$obj_menu	=	new stdClass();
			$obj_menu->title	=	$c_boxgiua1[$i]->find('div[class="c_boxgiua1_text"]',0)->first_child()->innertext;
			$obj_menu->link	=	$c_boxgiua1[$i]->find('div[class="c_boxgiua1_text"]',0)->first_child()->href;
			$obj_menu->parent	=	-1;
			$obj_menu->published	=	0;
			$parent	=	count($arrMenu);
			$arrMenu[]	=	$obj_menu;
			$sub_menu	=	$c_boxgiua1[$i]->find('div[class="c_boxgiua1_nd"]',0)->first_child();
			$items		=	$sub_menu->find('a');
			for ($j=0; $j<count($items); $j++)
			{
				$item	=	$items[$j];
				$obj_submenu	=	new stdClass();
				$obj_submenu->title	=	$item->innertext;
				$obj_submenu->link	=	$item->href;
				$obj_submenu->parent=	$parent;
				$obj_submenu->published=	1;
				$arrMenu[]	=	$obj_submenu;	
			}			
		}
	}
	if ($contentleft = $html_obj->find('div[id="contentleft"]',0)) {
		$showcat_sec	=	$contentleft->find('div[class="showcat_sec"]');
		
		for ($i=0;$i<count($showcat_sec); $i++)
		{
			$obj_menu	=	new stdClass();
			$obj_menu->title	=	$showcat_sec[$i]->find('div[class="link_sec"]',0)->first_child()->innertext;
			$obj_menu->link	=	$showcat_sec[$i]->find('div[class="link_sec"]',0)->first_child()->href;
			$obj_menu->parent	=	-1;
			$obj_menu->published	=	0;
			$parent	=	count($arrMenu);
			$arrMenu[]	=	$obj_menu;
			$sub_menu	=	$showcat_sec[$i]->find('ul',0);			
			$items		=	$sub_menu->find('a');
			for ($j=0; $j<count($items); $j++)
			{
				$item	=	$items[$j];
				$obj_submenu	=	new stdClass();
				$obj_submenu->title	=	$item->innertext;
				$obj_submenu->link	=	$item->href;
				$obj_submenu->parent=	$parent;
				$obj_submenu->published=	1;
				$arrMenu[]	=	$obj_submenu;
			}
		}
	}
	if ($contentleft = $html_obj->find('div[id="contentleft"]',0)) {
		$c_box_k2_tit	=	$contentleft->find('div[class="c_box_k2_tit"]');
		
		for ($i=0;$i<count($c_box_k2_tit); $i++)
		{
			$obj_menu	=	new stdClass();
			$obj_menu->title	=	$c_box_k2_tit[$i]->find('div[class="c_box_k2_tittext"]',0)->first_child()->innertext;
			$obj_menu->link	=	$c_box_k2_tit[$i]->find('div[class="c_box_k2_tittext"]',0)->first_child()->href;
			$obj_menu->parent	=	-1;
			$obj_menu->published	=	0;
			$parent	=	count($arrMenu);
			$arrMenu[]	=	$obj_menu;
			$sub_menu	=	$c_box_k2_tit[$i]->find('ul',0);			
			$items		=	$sub_menu->find('a');
			for ($j=0; $j<count($items); $j++)
			{
				$item	=	$items[$j];
				$obj_submenu	=	new stdClass();
				$obj_submenu->title	=	$item->innertext;
				$obj_submenu->link	=	$item->href;
				$obj_submenu->parent=	$parent;
				$obj_submenu->published=	1;
				$arrMenu[]	=	$obj_submenu;
			}
		}
	}
	return $arrMenu;
}


function mosModelCategorySaveGD($data)
{
	global $database,$arrErr;	
	$db	=	& $database;	
	$arr_ID		=	array();
	$reg_id		=	'/\/(\d+)[^\/\.]+\.html/ism';
	for ($i=0; $i<count($data); $i++)
	{
		$parent	=	0;
		$menu	=	$data[$i];
		if ($menu->parent != -1) {
			$parent	=	$arr_ID[intval($menu->parent)];
		}
		$id_origional	=	0;
		if (preg_match($reg_id,$menu->link,$matches_id)) {
			$id_origional	=	$matches_id[1];
		}
		$query	=	'INSERT INTO `#__article2010_category_giaoduc`'. 
						' SET link = '.$db->quote('http://giaoduc.net.vn'.$menu->link).
							', id_origional = ' . $db->quote($id_origional).
							', title = ' . $db->quote(trim(strip_tags(str_replace("\r\n","",$menu->title)))).
							', parent = '. $db->quote($parent).														
							', publish = '. $db->quote($menu->published);
		$db->setQuery($query);
		echo $db->getQuery();
//		echo '<br />';		
//die();
		$db->query();
		$arr_ID[$i] = mysql_insert_id();
	}
	return true;
}
////////////////////////////////////////////////////////////////////////////
/////////		FOR VIR.COM.VN			//////////
////////////////////////////////////////////////////////////////////////////
function mosModelCategoryGetVIR($url =null)
{
	if ($url == null) {
		$url		=	'http://www.vir.com.vn/news/home';
	}
	$browser	=	new phpWebHacks();
	$response	=	$browser->get($url);
	$html_obj	=	loadHtmlString($response);
	$arrMenu	=	array();
	if ($navMenu = $html_obj->find('div[id="navMenu"]',0)) {
		$parentMenu	=	$navMenu->first_child()->children();
		for ($i=1;$i<count($parentMenu); $i++)
		{		
			$obj_parent	=	$parentMenu[$i];
			$obj_menu	=	new stdClass();
			$obj_menu->title	=	$obj_parent->first_child()->first_child()->innertext;
			$obj_menu->link		=	$obj_parent->first_child()->href;
			$obj_menu->parent	=	-1;
			$obj_menu->published	=	1;		
			$parent	=	count($arrMenu);
			$arrMenu[]	=	$obj_menu;
			$sub_menu	=	$obj_parent->find('ul',0)->children();			
			for ($j=0; $j<count($sub_menu); $j++)
			{
				$item	=	$sub_menu[$j];				
				$obj_submenu	=	new stdClass();
				$obj_submenu->title	=	$item->first_child()->innertext;
				$obj_submenu->link	=	$item->first_child()->href;
				$obj_submenu->parent=	$parent;
				$obj_submenu->published=	1;				
				$arrMenu[]	=	$obj_submenu;	
			}		
		}
	}
	$reg_alias	=	'/\/([^\/]+)$/ism';
	for ($i=0;$i<count($arrMenu); $i++)
	{
		$link	=	$arrMenu[$i]->link;
		if (!preg_match($reg_alias,$link,$matches_link)) {
			$arrMenu[$i]	=	null;
			continue;
		}
		$arrMenu[$i]->alias	=	strtolower($matches_link[1]);
	}	
	return $arrMenu;
}


function mosModelCategorySaveVIR($data)
{
	global $database,$arrErr;	
	$db	=	& $database;	
	$arr_ID		=	array();
	
	for ($i=0; $i<count($data); $i++)
	{
		$parent	=	0;
		$menu	=	$data[$i];
		if ($menu->parent != -1) {
			$parent	=	$arr_ID[intval($menu->parent)];
		}		
		$query	=	'INSERT INTO `#__article2010_category_vir`'. 
						' SET link = '.$db->quote($menu->link).
							', alias_origional = ' . $db->quote($menu->alias).
							', title = ' . $db->quote(trim(strip_tags(str_replace("\r\n","",$menu->title)))).
							', parent = '. $db->quote($parent).														
							', publish = '. $db->quote($menu->published);
		$db->setQuery($query);
//		echo $db->getQuery();
		$db->query();
		$arr_ID[$i] = mysql_insert_id();
	}
	return true;
}

////////////////////////////////////////////////////////////////////////////
/////////		FOR NGUOIDUATIN.COM.VN			//////////
////////////////////////////////////////////////////////////////////////////
function mosModelCategoryGetNDT($url =null)
{
	if ($url == null) {
		$url		=	'http://www.nguoiduatin.vn/';
	}
	$browser	=	new phpWebHacks();
	$response	=	$browser->get($url);	
	$html_obj	=	loadHtmlString($response);
//	c49
	$reg_id		=	'/c(\d+)/ism';
	
	$arrMenu	=	array();
	
	
	if ($navMenu = $html_obj->find('ul[id="main-nav"]',0)) {
		$parentMenu	=	$navMenu->children();
		for ($i=1;$i<count($parentMenu); $i++)
		{		
			$obj_parent	=	$parentMenu[$i];
			$obj_menu	=	new stdClass();
			$obj_menu->title	=	$obj_parent->first_child()->innertext;
			$obj_menu->link		=	$obj_parent->first_child()->href;
			if (!preg_match($reg_id,$obj_parent->id,$matches_id)) {
				continue;
			}
			$obj_menu->id_origional	=	$matches_id[1];
			if ($obj_menu->id_origional<1) {
				continue;
			}
			$parent				=	$obj_menu->id_origional;
			$obj_menu->parent	=	0;
			$obj_menu->published	=	1;
			if ($obj_parent->find('ul',0)) {
				$sub_menu	=	$obj_parent->find('ul',0)->children();
				for ($j=0; $j<count($sub_menu); $j++)
				{
					$item	=	$sub_menu[$j];
					$obj_submenu	=	new stdClass();
					$obj_submenu->title	=	$item->first_child()->innertext;
					$obj_submenu->link	=	$item->first_child()->href;
					if (!preg_match($reg_id,$item->id,$matches_id)) {
						continue;
					}
					$obj_submenu->id_origional	=	$matches_id[1];
					if ($obj_submenu->id_origional<1) {
						continue;
					}
					$obj_submenu->parent=	$parent;
					$obj_submenu->published=	1;
					$arrMenu[]	=	$obj_submenu;
				}
			}
			$arrMenu[]	=	$obj_menu;	
		}
	}
	return $arrMenu;
}


function mosModelCategorySaveNDT($data)
{
	global $database,$arrErr;	
	$db	=	& $database;	
	$arr_ID		=	array();
	
	for ($i=0; $i<count($data); $i++)
	{
		
		$menu	=	$data[$i];
		$parent	=	$menu->parent;	
		$query	=	'INSERT INTO `#__article2010_category_nguoiduatin`'. 
						' SET link = '.$db->quote($menu->link).
							', id_origional  = ' . $db->quote($menu->id_origional).
							', title = ' . $db->quote(trim(strip_tags(str_replace("\r\n","",$menu->title)))).
							', parent = '. $db->quote($parent).														
							', publish = '. $db->quote($menu->published);
		$db->setQuery($query);
//		echo $db->getQuery();
		$db->query();
		$arr_ID[$i] = mysql_insert_id();
	}
	return true;
}
////////////////////////////////////////////////////////////////////////////
/////////		FOR VIETNAMPLUS.VN			//////////
////////////////////////////////////////////////////////////////////////////
function mosModelCategoryGetVNP($url =null)
{
	if ($url == null) {
		$url		=	'http://www.vietnamplus.vn/';
	}
	$browser	=	new phpWebHacks();
	$response	=	$browser->get($url);	
	$html_obj	=	loadHtmlString($response);
//	(event, 'm209','s', 0, 0)
	$reg_id		=	'/\(event,\s*\'(m\d+)\'/ism';
	
	$arrMenu	=	array();
	$href	=	new href();	
	$content	=	$html_obj->find('div[id="header"]',0);
	
	if ($navMenu = $content->find('div[id="nav"]',0)) {
		$parentMenu	=	$navMenu->children(0)->children();
		
		for ($i=0;$i<count($parentMenu); $i++)
		{		
			$obj_parent	=	$parentMenu[$i];
			$obj_menu	=	new stdClass();
			$obj_menu->title	=	trim(str_replace('|','',strip_tags($obj_parent->first_child()->innertext)));
			$obj_menu->link		=	$href->process_url($obj_parent->first_child()->href,$url);
//			m209Frame
			$obj_menu->parent	=	-1;			
			$onmouseout		=	$obj_parent->first_child()->onmouseout;			
			if (!preg_match($reg_id,$onmouseout,$matches_id)) {
				//continue;
			}
			$id		=	$matches_id[1];
			
			if ($content->find('div[id="'.$id.'"]',0)) {
				$obj_menu->published	=	0;
				$arrMenu[]	=	$obj_menu;	
				$parent		=	count($arrMenu) - 1;
				$sub_menu	=	$content->find('div[id="'.$id.'"]',0)->find('ul',0)->children();				
				for ($j=0; $j<count($sub_menu); $j++)
				{
					$item	=	$sub_menu[$j];
					
					$obj_submenu	=	new stdClass();
					$obj_submenu->title	=	trim(str_replace('|','',strip_tags($item->first_child()->innertext)));
					$obj_submenu->link	=	$href->process_url($item->first_child()->href,$url);
					$obj_submenu->parent=	$parent;
					$obj_submenu->published=	1;
					$arrMenu[]	=	$obj_submenu;
				}
			}else {
				$obj_menu->published	=	1;
				$arrMenu[]	=	$obj_menu;
			}			
		}
	}	
	return $arrMenu;
}


function mosModelCategorySaveVNP($data)
{
	global $database,$arrErr;	
	$db	=	& $database;	
	$arr_ID		=	array();
	
	for ($i=0; $i<count($data); $i++)
	{
		
		$menu	=	$data[$i];
		if ($menu->parent == -1) {
			$parent	=	0;
		}else {
			$parent	=	$arr_ID[$menu->parent];
		}		
		$query	=	'INSERT INTO `#__article2010_category_vietnamplus`'. 
						' SET link = '.$db->quote($menu->link).							
							', title = ' . $db->quote(trim(strip_tags(str_replace("\r\n","",$menu->title)))).
							', parent = '. $db->quote($parent).														
							', publish = '. $db->quote($menu->published);
		$db->setQuery($query);
//		echo $db->getQuery();
		$db->query();
		$arr_ID[$i] = mysql_insert_id();
	}
	return true;
}
////////////////////////////////////////////////////////////////////////////
/////////		FOR VNECONOMY.VN			//////////
////////////////////////////////////////////////////////////////////////////
function mosModelCategoryGetVneconomy($url =null)
{
	if ($url == null) {
		$url		=	'http://vneconomy.vn/';
	}
	$browser	=	new phpWebHacks();
	$response	=	$browser->get($url);	
	$html_obj	=	loadHtmlString($response);

//	http://vneconomy.vn/p9920c9922/chinh-tri-nghi-truong.htm
	$reg_id		=	'/\/p(\d+)c(\d+)\/([^\.]+)\.htm/ism';
	
	$arrMenu	=	array();
	$href	=	new href();	
	$content	=	$html_obj->find('div[class="menu"]',0);
	
	if ($content	=	$html_obj->find('div[class="menu"]',0)) {
		$parentMenu	=	$content->children();				
		for ($i=0;$i<count($parentMenu); $i++)
		{		
			$obj_parent	=	$parentMenu[$i];
			$link	=	$obj_parent->href;
			if (!preg_match($reg_id,$link,$matches_id)) {
				continue;
			}
			$obj_menu	=	new stdClass();
			$obj_menu->title	=	$matches_id[3];
			$obj_menu->link		=	$href->process_url($link,$url);
			$obj_menu->parent	=	0;
			$obj_submenu->published=	1;
			$obj_menu->id_origional	=	$matches_id[2];
			$response_sub = $browser->get($obj_menu->link);
			$sub = loadHtmlString($response_sub);
			$arrMenu[]	=	$obj_menu;	
			
			if ($content = $sub->find('div[class="submenu"]',0)) {
				
				$sub_menu	=	$content->find('a');
				for ($j=0; $j<count($sub_menu); $j++)
				{
					$item	=	$sub_menu[$j];
					$link	=	$item->href;
					if (!preg_match($reg_id,$link,$matches_id)) {
						continue;
					}	
					$obj_submenu	=	new stdClass();
					$obj_submenu->title	=	$matches_id[3];
					$obj_submenu->link	=	$href->process_url($link,$url);
					$obj_submenu->parent	=	$matches_id[1];
					$obj_submenu->id_origional	=	$matches_id[2];
					$obj_submenu->published=	1;
					$arrMenu[]	=	$obj_submenu;
				}
			}			
		}
	}
	
	return $arrMenu;
}


function mosModelCategorySaveVneconomy($data)
{
	global $database,$arrErr;	
	$db	=	& $database;	
	$arr_ID		=	array();
	
	for ($i=0; $i<count($data); $i++)
	{		
		$menu	=	$data[$i];				
		$query	=	'INSERT INTO `#__article2010_category_vneconomy`'. 
						' SET link = '.$db->quote($menu->link).							
							', title = ' . $db->quote(trim(strip_tags(str_replace("\r\n","",$menu->title)))).
							', id_origional = '. $db->quote($menu->id_origional).														
							', parent = '. $db->quote($menu->parent).														
							', publish = 1';
		$db->setQuery($query);
//		echo $db->getQuery();
		$db->query();
		$arr_ID[$i] = mysql_insert_id();
	}
	return true;
}