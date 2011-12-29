<?php

function mosModelNewsVinacorpGetNews($get_existing = true)
{		
	global $arrErr,$database, $mosConfig_live_site;
	
	$arr_obj 	=	mosModelCafefGetCat();
	if (count($arr_obj)<2) {
		echo 'success';
		die();
	}
	
	$obj_cat	=	$arr_obj[0];

	$param	=	$obj_cat->lastGet_param;
	preg_match('/getold=([^;]*);page=([^;]*);/ism',$param,$matches_param);
	
	$getold		=	1;	$page	=	0;
	if(isset($matches_param[1]))
		$getold	=	$matches_param[1];
	
	if(isset($matches_param[2]) and $matches_param[2])
		$page	=	$matches_param[2];
		
	$page		=	intval($page)	+	1;
	
	if($getold == 0 and $page >5)
	{		
		$page	=	1;
	}

	$bool	=	1;	
	$data_content = mosModelNewsVinacorpGetListContent($obj_cat->link, $page, $obj_cat->id_origional, $obj_cat->parent);

	$arr_ID		=	$data_content->arrID;
	$arr_link	=	$data_content->arr_link;
	$arr_title	=	$data_content->arr_title;
	$arr_alias	=	$data_content->arrAlias;	
	
	$arr_result	=	array();
	$arr_geted	=	array();
	if ($get_existing==false) {
		$db = $database;
		$aid	=	array();
		for ($i=0 ; $i<count($arr_ID); $i++)
		{
			$aid[]	=	$db->quote($arr_ID[$i]);
		}
		$_id	=	implode(',',$aid);
		$query = "SELECT id_original 
					FROM #__article2010_new_vinacorp 
					WHERE id_original in($_id)";
		$db->setQuery($query);	

		$arr_result	=	$db->loadResultArray();		
		$arr_ban	=	mosBanidGet('vinacorp.vn',array('id_origional'),"id_origional  in($_id)");
		$arr_geted	=	mosGetDBOBJ('#__article2010_totalcontent',array('id_origional'),'id_origional  in('.$_id.') AND SiteID='.$db->quote('vnc355'));		

		if (count($arr_result)) {
			if (count($arr_ban)) {
				$arr_result	=	array_merge($arr_result,$arr_ban);
			}
		}else {
			$arr_result	=	$arr_ban;
		}
		if (count($arr_result)) {
			if (count($arr_geted)) {
				$arr_result	=	array_merge($arr_result,$arr_geted);
			}
		}else {
			$arr_result	=	$arr_geted;
		}
		$arr_result	=	is_array($arr_result)?$arr_result:array();
		$arr_result	=	array_unique($arr_result);
	}


	$i = 0;
	$number_getContent	=	0;
	$option	=	$_REQUEST['option'];
	$browser	=	new phpWebHacks();
	echo '<br /> ';
	echo '$arr_result: ';	echo count($arr_result);
	echo '<br /> ';
	echo '$arr_ID: ';	echo count($arr_ID);
	echo '<br /> [$arr_ID] : '. count($arr_ID);
	
	for ($i = 0; $i < count($arr_ID) ; $i++)
	{
		if ($get_existing==false && in_array($arr_ID[$i],$arr_result))
		{
			array_push($arrErr,'#444 '.$arr_ID[$i].' is existing');
			continue;
		}
		
		$url		=	$mosConfig_live_site."/index.php?option=$option";		
		
		$begin		=	md5('BEGIN_GET_CONTENT_C2F');
		$end		=	md5('END_GET_CONTENT_C2F');
		
		$arr_post	=	array();
		$arr_post['task']				=	'getnewsVNC';
		$arr_post['begin_get_content']	=	$begin;
		$arr_post['end_get_content']	=	$end;		
		$arr_post['secid']				=	$obj_cat->secid;			
		$arr_post['catid']				=	$obj_cat->catid;		
		$arr_post['catid_vnc']			=	$obj_cat->id;		
		$arr_post['content_id']			=	$arr_ID[$i];
		$arr_post['page']				=	$page;
		$arr_post['page']				=	'p:'.$page." | c:$obj_cat->id";
		$arr_post['content_link']		=	$arr_link[$i];
		$arr_post['content_alias']		=	$arr_alias[$i];
		$arr_post['content_title']		=	$arr_title[$i];
//		echo $url;		
//		$a	=	array();
//		foreach ($arr_post as $k=>$v) {
//			$a[]	=	"$k=$v";
//		}
//		echo '<br /> <hr />';
//		echo implode('&',$a);
//		die();
		
		$info	=	$browser->post($url,$arr_post);
		if (preg_match('/' . $begin . '(.*?)' . $end . '/ism', $info, $match)) 
		{                   
		 $info=trim($match[1]);
		}
		else {
			$message	=	'ERROR_GET_CONTENT_C2F| #123 API false '.$arr_ID[$i].' '.$info;
			array_push($arrErr,$message);
		continue;
		}
		if (stristr($info,'ERROR_GET_CONTENT_C2F')) {
				$message	=	'ERROR_GET_CONTENT_C2F| '.$info;
			array_push($arrErr,$message);
		continue;
		}
		$number_getContent	=	$number_getContent + 1;
	}
	
	mosSaveVNCGetParam($obj_cat->id, $getold, $page, $data_content->isNext,$obj_cat);
	
	$arr_obj[0]->number_getcontent	=	$number_getContent;
	$arr_obj[0]->page	=	$page;
	$arr_obj[0]->next	=	$data_content->isNext?'YES':'NO';
	$arr_obj[0]->old	=	$getold?'YES':'NO';
	$arr_obj[0]->date	=	date('Y-m-d');
	return $arr_obj;
//	mysql_close($db->_resource);
	return $obj_result;
}

function mosSaveVNCGetParam($catid, $getold, $page, $isNext, $obj_cat)
{	
	global $arrErr,$database;
	$db	=	$database;
	$query	=	'';
	if ($isNext == false) {
		$query	=	'UPDATE `#__article2010_category_vinacorp` 
					SET `last_run` = '.$db->quote(date ( 'Y-m-d H:i:s' )).', 
						`lastGet_param` = '.$db->quote("getold=0;page=1;").'
					WHERE `id` ='. $catid;
		}else {
			// con nh trang
			$query	=	'UPDATE `#__article2010_category_vinacorp` 
					SET `last_run` = '.$db->quote(date ( 'Y-m-d H:i:s' )).', 
						`lastGet_param` = '.$db->quote("getold=$getold;page=$page;").'
					WHERE `id` ='. $catid;
	}
	$db->setQuery($query);
	$db->query();
}

function mosModelCafefGetCat()
{
	global $database;
	$db	=	& $database;
	$arr_obj	=	array();
	if (isset($_REQUEST['cat_id'])) {
		$cat_id	=	$_REQUEST['cat_id'];
		$query = "SELECT *
			FROM `#__article2010_category_vinacorp`
			WHERE id = $cat_id";
		$db->setQuery($query);
		$db->loadObject($obj);
		
		$arr_obj[]	=	$obj;
		$arr_obj[]	=	$obj;
	}else {
		$query = "SELECT *
			FROM `#__article2010_category_vinacorp`
			WHERE publish = 1 and (lastGet_param = '' or `lastGet_param` like ".$db->quote('%getold=1;%').")".
			" ORDER BY `last_run` LIMIT 0,2";
		$db->setQuery($query);		
		$arr_obj	=	$db->loadObjectList();
		if (count($arr_obj) == 1) {
			$arr_obj[]		=	$arr_obj[0];
		}
	}	
	return $arr_obj;
}

function mosModelNewsVinacorpGetListContent($link,$page =1, $catid_origional, $cat_parent)
{
	global $arrErr,$database, $mosConfig_live_site;
	$root	=	'http://www.vinacorp.vn/';
	$href	=	new href();
//	http://www.vinacorp.vn/news/thi-truong-chung-khoan
//	http://www.vinacorp.vn/news/thi-truong-chung-khoan/4
	$link	=	$link.'/'.$page;
	echo '<br/>';
	echo $link;	
	echo '<br/>';	
	$browser	=	new phpWebHacks();
	$response	=	$browser->get($link);
	
	$arr_link_article	=	array();
	$html		=	loadHtmlString($response);
	$arr_link	=	array();
	$arr_id		=	array();
	$arr_title	=	array();
	$arr_alias	=	array();
//	http://www.vinacorp.vn/news/nhung-bat-ngo-thu-vi-cua-chung-khoan-tuan-qua/ct-478537
	$reg_id	 	= '/\/([^\/]+)\/ct-(\d+)/ism';
	
	// find catNews
	if ($hot = $html->find('div[class="ca-hot"]',0) and $page == 1) {
		if ($item = $hot->find('h3',0)) {
			$link	=	$href->process_url($item->children(0)->href,$root);
			if (preg_match($reg_id, $link, $matches)) {
				$arr_link[]	=	$link;
				$arr_id[]	=	md5($link);
				$arr_alias[]=	$matches[1];
				$arr_title[]=	strip_tags($item->children(0)->innertext);
			}
		}
	}
	
	// find catNewsBottom
	if ($page ==1 and $item = $html->find('div[class="master-news-top-right"]',0)) {						
		$link	=	$href->process_url($item->children(0)->href,$root);		
		if (preg_match($reg_id, $link, $matches)) {
			$arr_link[]	=	$link;
			$arr_id[]	=	$matches[2];
			$arr_alias[]=	$matches[1];
			$arr_title[]=	strip_tags($item->children(0)->innertext);
		}
	}
	
	if ($content = $html->find('div[id="divNewsSearchList"]',0)) {
			$items = $content->find('div[class="news-big-left-thumbnail"]');
			for ($i=0; $i<count($items); $i++)
			{
				$item = $items[$i]->find('a[class="title"]',0);				
				$link = $href->process_url($item->href,$root);				
				if (preg_match($reg_id, $link, $matches)) {
					$arr_link[]	=	$link;
					$arr_id[]	=	$matches[2];
					$arr_alias[]=	$matches[1];
					$arr_title[]=	strip_tags($item->innertext);
				}
			}
	}
	
	// isNext	
	$page++;
	$isNext	=	false;
	if ($items = $html->find('ul[class="pages"]',0)) {
		$item	=	$items->innertext;
		$reg_page	=	'/<a[^>]*>\s*'.$page.'/ism';
		if (preg_match($reg_page,$item)) {
			$isNext	=	true;
		}	
	}

	$obj_return	=	new stdClass();
	$obj_return->arr_link	=	$arr_link;	
	$obj_return->arrID		=	$arr_id;	
	$obj_return->arrAlias	=	$arr_alias;	
	$obj_return->arr_title	=	$arr_title;	
	$obj_return->isNext		=	$isNext;
	return $obj_return;
}

/**
 * Get news vov
 *
 * @param unknown_type $id_content
 * @param unknown_type $section_id
 * @param unknown_type $catid
 * @param unknown_type $path_image
 * @param unknown_type $link_image
 * @return unknown
 */
function mosModelNewsVinacorpGetCNC($id_content, $link_content, $content_title, $content_alias, $page, $secid, $catid, $catid_vnc, $path_image = 'images', $link_image,$SiteID = 'vnp330')
{
	global $arrErr;		
	if (!$content	=	mosModelNewsGetContent($link_content,$id_content, $content_title))
	{
		$message_ban	=	$arrErr[count($arrErr)-1];
		$message	=	'#389 models newsNguoiduatin mosModelNewsVinacorpGetCNC. Not get content.'.$id_content;
		array_push($arrErr,$message);
//		echo 'banid was disable. Please enable if real run';
	//	mosBanidStore('cafef.vn','',$id_content,$message_ban."\r\n".$message);
		return false;
	}
	$content->title	=	$content_title;
	$content->alias	=	$content_alias;	
	$content->secid	=	$secid;	
	$content->catid	=	$catid;	
	$content->page	=	$page;	
	$content->cat_id_origional	=	$catid_vnc;	
	
	$content->intro		=	mosModelNewsVinacorpProcessOther($content->intro,$SiteID, $id_content);
	$content->fulltext	=	mosModelNewsVinacorpProcessOther($content->fulltext,$SiteID, $id_content);
	
	$root	=	'http://vinacorp.vn/';
	$arr_Images	=	array();
	
	mosGetImages($content, $root, $arr_Images, $path_image, $link_image);	
	
	$content->arr_image		=	$arr_Images;
	$content->id_content	=	$id_content;
	if (!mosModelNewsSave($content,$SiteID)) {
		$message	=	'#391 models news mosModelNewsCafefGetC2F. Not save content.'.$id_content;
		array_push($arrErr,$message);
		return false;
	}
}

function mosModelNewsGetContent($link,$id_content, $content_title)
{
	global $arrErr,$database;	
	$db	=	$database;

	$browser	=	new phpWebHacks();
	echo $link;
	if (!$response	=	$browser->get($link)) {
		$message	=	'#832 models newsVinacorp mosModelNewsGetContent. Invalid get article '.$link;
		array_push($arrErr,$message);
		return false;
	}	
	
	$html	=	loadHtmlString($response);
	
	// get intro
	$intro		=	strip_tags($html->find('h3[class="description"]',0)->innertext);
	
	if ($text = $html->find('div[class="desc-image"]',0)) {
		$intro	=	$text->innertext . $intro;
	}

	$full_text	=	$html->find('div[class="details"]',0)->innertext;
	$date		=	strip_tags($html->find('div[class="extra"]',0)->children(0)->innertext);
//	 Th?? Ba?y, 10/09/2011, 08:21
	$date 		=	explode(',',$date);
	$d			=	$date[1];
	$time		=	trim($date[2]);
	$d			=	explode('/',$d);
	$date_time	=	trim($d[2]).'-'.trim($d[1]).'-'.trim($d[0]).' '.$time.':00';
		
	$obj_content			=	new stdClass();	
	$obj_content->intro		=	trim(str_replace('\r\n','',mostidy_clean($intro)));
	$obj_content->fulltext	=	trim(str_replace('\r\n','',mostidy_clean($full_text)));	
	$obj_content->link		=	$link;
	$obj_content->content_date		=	$date_time;
	$obj_content->PageHTML	=	$response;	
	return $obj_content;
}

function mosModelNewsVinacorpProcessOther($str_in, $SiteID,$id_original)
{
	global $database,$error;
	$db	=	$database;
	$reg_link_other = '/<a.*?href=["\' ]*([^"\' ]*)["\' ]*.*?>(.*?)<\/a>/ism';
	$reg_id_other 	= '/\/[^\/]+\/ct-(\d+)/ism';
	$href	=	new href();
	$root	=	'http://cafef.vn/';
	
	if (!preg_match_all($reg_link_other,$str_in,$matches_link)) {
		return $str_in;
	}
	
	for ($i=0; $i< count($matches_link[0]); $i++)
	{		
		$link	=	str_replace('&amp;','&',$href->process_url($matches_link[1][$i], $root)); 
		
		if (!preg_match($reg_id_other, $link,$matches)) {
			$title		=	strip_tags($matches_link[2][$i]);
			$str_in		=	str_replace($matches_link[0][$i],$title,$str_in);
			continue;
		}	
		$id_orgional_other	=	$matches[1];		
		
		if ($id_result	=	mosModelNewsVinacorpSaveOther($SiteID, $id_original, $matches[1],$matches_link[0][$i], $link)) {
			$title		=	strip_tags($matches_link[2][$i]);
			$link_content = '<a title="'.str_replace('&gt;','',$title).'" href="/index.php?option=com_content&task=view&id=' . $id_result.'" >'.$title."</a>";
			$str_in		=	str_replace($matches_link[0][$i],$link_content,$str_in);
		}	
	}
	
	return $str_in;
}

function mosModelNewsVinacorpSaveOther ($SiteID,$id_original,$id_original_other,$str_replace,$link_other)
{
	global $database,$error;
	
	$db	=	& $database;
	$query = "SELECT * FROM #__article2010_new_cafef WHERE id_original = ".trim($id_original_other);
	$db->setQuery($query);
	
	$id_result	=	false;
	
	if ($db->loadObject($obj)) {
		 $state = 0;
		 $other_pageHtml	=	$obj->PageHTML;
		 $id_result	=	$obj->id;
		 $type	=	1;
	}else {
		$browser		=	new phpWebHacks();	
		$other_pageHtml	=	$browser->get($link_other);		
		$header 		= $browser->get_head(); 
		$content_type 	= $header['Content-Type'];
		
		$state = 1;		
		if ((strpos($content_type,'text/html')) >=0 ) {
			$type	=	1;
		}elseif ((strpos($content_type,'image/jpeg')) >=0 )
		{
			$type	=	2;
		}else
		{
			$type	=	3;
		}
	}
//	1: article-text/html, 2: image-image/jpeg, 3: file
	$result	=	mosOtherStore($SiteID, $id_original, $id_original_other, $str_replace, $link_other, $type, $state, $other_pageHtml);
	
	if (! $result) {
		$error->arr_err[]	=	"Error insert or update data for other table ";
		return false;
	}	
	return $id_result;
}

function mosModelNewsSave($content, $SiteID = 'vnc355')
{	
	global $database, $my, $mainframe, $mosConfig_offset, $arrErr;
	$db	=	$database;
	$id	=	$content->id_content;
	// insert into
	$date	=	date('Y-m-d H:i:s');
	
	$nullDate = $database->getNullDate ();
	$row = new mosVovArticle2010_new2( $db );

	$row->firstRunDate  =	$row->firstRunDate ? $row->firstRunDate : date ( 'Y-m-d H:i:s' );
	$row->latestRunDate =	date ( 'Y-m-d H:i:s' );	
	$row->id_original	=	$content->id_content;	
	$row->SiteID		=	$SiteID;	
	$row->SiteName		=	'vinacorp';	
	$row->Domain		=	'vinacorp.vn';	
	$row->SourceURL		=	$content->link;
	$row->created		= 	date("Y-m-d H:i:s",strtotime($content->content_date));	
	$row->title			=	$content->title;
	$row->title_alias	=	$content->alias;
	$row->introtext		=	$content->intro;
	$row->fulltext		=	$content->fulltext;
	$row->sectionid		=	$content->secid;
	$row->catid			=	$content->catid;
	$row->note			=	$content->page;
	$row->catid_original=	$content->cat_id_origional;
	$row->PageHTML 		=	$content->PageHTML;
			
	$fmtsql = "INSERT INTO `#__article2010_new_vinacorp` SET %s ON DUPLICATE KEY UPDATE  %s  ";
	$insert = array();
	$update = array();
	foreach (get_object_vars( $row ) as $k => $v) {		
		if (is_array($v) or is_object($v) or $v === NULL) {
			continue;
		}
		if ($k[0] == '_') { // internal field
			continue;
		}		
		$insert[] = $db->NameQuote( $k ).' = '.$db->Quote( $v );
		if ($k != 'id_original') {
			$update[] = $db->NameQuote( $k ).' = '.$db->Quote( $v );
		}		
	}
	$db->setQuery( sprintf( $fmtsql, implode( ",", $insert ) ,  implode( ",", $update ) ) );
	
	if (!$db->query()) {
		$messege	=	$db->getQuery();
		array_push($arrErr,$messege);
		return false;
	}	
	$id = mysql_insert_id();
	mosModelNewsSaveMedia($content->arr_image,$id,$SiteID);
	
	$obj	=	new stdClass();
	$obj->SiteID	=	$SiteID;
	$obj->aid	=	$id;
	$obj->id_origional	=	$row->id_original;	
	mosStoreOBJ('#__article2010_totalcontent',$obj);
	mosUpdateOther($content->id_content,$content->title,$id,'#__article2010_new_vinacorp');
	return true;
}

function mosModelNewsSaveMedia($arr_media,$contenid, $SiteID = 'vnc355')
{
	global $database, $arrErr;
	$db	=	$database;
	foreach ($arr_media as $media)
	{
		$row = new mosVovSmedia2010_new( $db );
		$query	=	'SELECT id 
					 FROM `#__smedia2010_new` 
					 WHERE aid='.$contenid.'
						AND media_url = '.$db->quote($media->media_url);
		$db->setQuery($query);
		if (!$result = $db->loadResult()) {			
			$row->firstRunDate	=	date ( 'Y-m-d H:i:s' );	
		}else {
			$row->load($result);
		}
		$row->latestRunDate 	=	date ( 'Y-m-d H:i:s' );	
		$row->aid 				=	$contenid;	
		$row->SiteID 			=	$SiteID;
		$row->media_url			=	$media->media_url;
		$row->SourceURL			=	$media->SourceURL;	
		$row->Size				=	$media->Size;	
		$row->FileName			=	$media->FileName;	
		$row->Path				=	$media->Path;	
		$row->FileType			=	$media->FileType;	
		$row->MediaType			=	$media->MediaType;
		if (! $row->store ()) {
			$message	=	'#562 models news mosModelNewsSaveMedia. Invalid store media for '.$media->Path.'. Link: '.$media->media_url.' sql error: '.$row->getError ();
			array_push($arrErr,$message);				
		}			
	}
	return true;
}