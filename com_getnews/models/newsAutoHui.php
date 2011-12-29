<?php


function mosModelAutoHuiGetNews($get_existing = true)
{	
	
	global $arrErr,$database, $mosConfig_live_site;
	$db = $database;
	$arr_obj 	=	mosModelAutoHuiGetCat();	
	
	$obj_cat	=	$arr_obj[0];
			
	$data_content = mosModelAutoHuiGetListContent($obj_cat->id, $obj_cat->secid, $obj_cat->catid, $obj_cat->title, $obj_cat->domain);
	
	$arr_ID		=	$data_content->arr_ID;	
	$arr_link	=	$data_content->arr_link;	
	$arr_alias	=	$data_content->arr_alias;	
	
	$arr_result	=	array();
	if ($get_existing==false and is_array($arr_ID) and count($arr_ID)>0) {
		$db = $database;
		$_id	=	implode(',',$arr_ID);
		
		$query = "SELECT id_original 
					FROM #__article2010_new_autohui
					WHERE id_original in($_id)";
		$db->setQuery($query);
		$arr_result	=	$db->loadResultArray();
		
		$arr_ban	=	mosBanidGet('oto-hui.com', array('id_origional'), "id_origional  in($_id)");	
		
		if (count($arr_result)) {
			if (count($arr_ban)) {
				$arr_result	=	array_merge($arr_result,$arr_ban);
			}
		}else {
			$arr_result	=	$arr_ban;
		}
	}
	$browser	=	new phpWebHacks();
	$number_getContent	=	0;
	$i = 0;
	$option	=	$_REQUEST['option'];
	
	for ($i = 0; $i < count($arr_ID) ; $i++)
	{
		
		if ($get_existing==false && $arr_result && in_array($arr_ID[$i],$arr_result))
		{
			continue;
		}
		
		$id_content	=	$arr_ID[$i];
		$begin		=	md5('BEGIN_GET_CONTENT_OTO-HUI');
		$end		=	md5('END_GET_CONTENT_OTO-HUI');
		
		$url		=	$mosConfig_live_site."/index.php?option=$option";
		
		$arr_post	=	array();
		$arr_post['begin_get_content']	=	$begin;
		$arr_post['end_get_content']	=	$end;
		$arr_post['task']				=	'getautohui';
		$arr_post['content_id']			=	$arr_ID[$i];
		$arr_post['content_link']		=	$arr_link[$i];		
		$arr_post['content_alias']		=	$arr_alias[$i];
		
		$info	=	$browser->post($url,$arr_post);
		
        if (preg_match('/' . $begin . '(.*?)' . $end . '/ism', $info, $match)) 
         {                   
             $info=trim($match[1]);
         }
         else {
         	$message	=	'ERROR_GET_CONTENT_TN| #123 API false '.$id_content.' '.$info;
         	array_push($arrErr,$message);
            continue;
         }
      	 if (stristr($info,'ERROR_GET_CONTENT_TN')) {
  	 		$message	=	'ERROR_GET_CONTENT_TN| '.$info;
     		array_push($arrErr,$message);
            continue;
         }
          $number_getContent	=	$number_getContent + 1;
	}
	
	$query	=	'UPDATE `#__article2010_category_autohui` '.
				' SET `last_run` = '.$db->quote(date ( 'Y-m-d H:i:s' )).'
				WHERE `id` ='. $obj_cat->id;	
	
	$db->setQuery($query);
	$db->query();
//	
	$arr_obj[0]->number_getcontent	=	$number_getContent;
	$arr_obj[0]->number_content	=	$data_content->number_content;
	return $arr_obj;
}


// Lấy category từ cơ sở dữ liệu
function mosModelAutoHuiGetCat()
{
	global $database;
	$db	=	& $database;
	if (isset($_REQUEST['id_origional'])) {
		$id_origional	=	$_REQUEST['id_origional'];
		$query = "SELECT *
			FROM `#__article2010_category_autohui`
			WHERE publish = 1 and id_origional = ".$db->quote($id_origional)."
			ORDER BY `last_run`
			LIMIT 0,2";	
		$db->setQuery($query);
		$obj	=	$db->loadObjectList();
		$obj[1]	=	$obj;
	}else {
		$query = "SELECT *
			FROM `#__article2010_category_autohui`
			WHERE publish = 1
			ORDER BY `last_run`
			LIMIT 0,2";	
		$db->setQuery($query);
		$obj	=	$db->loadObjectList();
		if (count($obj) <2) {
			$obj[1]	=	$obj[0];
		}
	}
	
	return $obj;
}

// Get List content normal
function mosModelAutoHuiGetListContent($cat_id, $secid, $catid, $cat_name, $link_cat)
{
	global $arrErr,$database, $mosConfig_live_site;
	$db	=	$database;
	$arr_link_article	=	array();
	/*$browser	=	new phpWebHacks();
	$response	=	$browser->get($link_cat);
	
	$html		=	loadHtmlString($response);
	$heade_news_left	=	$html->find('div[id="heade_news_left"]',0);
	
	$link		=	$heade_news_left->find('a',0);
	$obj_link	=	new stdClass();
	$obj_link->title	=	$link->title;
	$obj_link->link=	$link->href;
	$arr_link_article[]	=	$obj_link;
	
	$head_news_right	=	$html->find('div[id="head_news_right"]',0);
	$linka		=	$head_news_right->find('a');
	for ($i=0; $i<count($linka); $i++)
	{		
		$obj_link	=	new stdClass();
		$obj_link->title	=	$linka[$i]->title;
		$obj_link->link		=	$linka[$i]->href;
		$arr_link_article[]	=	$obj_link;	
	}
	$left_side_under	=	$html->find('div[id="left_side_under"]',0);
	$list_title			=	$html->find('div[class="title"]');
	
	for ($i=0; $i<count($list_title); $i++)
	{		
		$obj_link	=	new stdClass();
		$link		=	$list_title[$i]->find('a',0);			
		$obj_link->title	=	$link->title;
		$obj_link->link		=	$link->href;
		$arr_link_article[]	=	$obj_link;	
	}
	
	$cactinkhac_content	=	$html->find('div[id="cactinkhac_content"]',0);
	$lista	=	$cactinkhac_content->find('a');
	for ($i=0; $i<count($lista); $i++)
	{		
		$obj_link	=	new stdClass();
		$obj_link->title	=	$lista[$i]->title;
		$obj_link->link		=	$lista[$i]->href;		
		$arr_link_article[]	=	$obj_link;		
	}
	
	$reg_id	=	'/\/a(\d+)\/([^\/]+)\.html/ism';
	$href	=	new href();
	$root	=	'http://www.oto-hui.com/';
		
	for ($i=0; $i<count($arr_link_article); $i++)
	{		
		$obj_link	=	$arr_link_article[$i];
		$link	=	$href->process_url($obj_link->link,$root);		
		if (!preg_match($reg_id, $link, $maches_link)) {
			continue;
		}
		$id_original	=	$maches_link[1];
		$alias_original	=	$maches_link[2];
		
		$query = "INSERT INTO `#__article2010_new_autohui` 
					SET id_original = ". $id_original.
						", title = ". $db->quote($obj_link->title).
						", sectionid = ". $secid.
						", catid = ". $catid.
						", catid_original = ". $cat_id.
						", CatName = ".$db->quote($cat_name).
						", SourceURL = ".$db->quote($link).
						", title_alias = ".$db->quote($alias_original).
						", status = 0
						, note = ".$db->quote('status = 0 => need to run get content. catid_original is id of table category: #__article2010_category_autohui').
						" ON DUPLICATE KEY UPDATE ".
						" title = ". $db->quote($obj_link->title).
						", title_alias = ".$db->quote($alias_original);
		$db->setQuery($query);
		if (isset($_REQUEST['id_origional']))
		{
			echo $db->getQuery();
			echo '<br />';		
		}
		$db->query();
	}*/
	if (isset($_REQUEST['id_origional']))
		{
			die();			
		}
	
	$query	=	'SELECT count(*) FROM #__article2010_new_autohui WHERE status = 0';
	$db->setQuery($query);
	$number_content	=	$db->loadResult();
	
	$query	=	'SELECT A.id,A.SourceURL,A.title_alias '.
					' FROM #__article2010_new_autohui as A'.
					' WHERE A.status = 0 '.
						' AND A.id_original not in (SELECT B.id_origional '.
													' FROM `#__article2010_banid` as B '.
													' WHERE B.`host` = '. $db->quote('oto-hui.com') .') '.
					' LIMIT 0,20';
	$db->setQuery($query);

	$arr_content	=	$db->loadObjectList();
	$arr_ID	=	array();
	$arr_link_source	=	array();	
	$arr_alias	=	array();
//	echo $link_cat;
//	echo $db->getQuery();
//	var_dump($arr_content);
//	die();
	for ($i=0; $i<count($arr_content); $i++)
	{
		$arr_ID[]			=	$arr_content[$i]->id;
		$arr_link_source[]	=	$arr_content[$i]->SourceURL;		
		$arr_alias[]	=	$arr_content[$i]->title_alias;	
		
	}
	$data_result	=	new stdClass();
	$data_result->arr_ID	=	$arr_ID;
	$data_result->arr_link	=	$arr_link_source;
	$data_result->arr_alias	=	$arr_alias;
	$data_result->number_content	=	$number_content;
	return $data_result;
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
function mosModelAutoHuiGetATH($id_content, $link_content , $alias_content, $section_id = '1', $catid =1 , $path_image , $link_image , $SiteID )
{
	global $arrErr;

	if (!$content	=	mosModelAutoHuiGetContent($link_content, $id_content, $alias_content,$SiteID))
	{
		$message_ban	=	$arrErr[count($arrErr)-1];		
		$message	=	'#389 models newsAutohui mosModelAutoHuiGetATH.<b> Not get content</b>.'.$id_content . $link_content;
		array_push($arrErr,$message);
		mosBanidStore('oto-hui.com','',$id_content, 1, $message_ban."\r\n".$message);
		return false;
	}
	
	$root	=	'http://oto-hui.com';
	$arr_Images	=	array();
	$title_content	=	$content->title;
	$content->title	=	$alias_content;
	mosGetImages($content,$root, $arr_Images,$path_image, $link_image);	
	$content->title	=	$title_content;
	$content->alias_content	=	$alias_content;
	$content->arr_image		=	$arr_Images;	
	$content->id_content	=	$id_content;
	if (!mosModelAutoHuiSave($content,$section_id , $catid, $SiteID)) {
		$message	=	'#391 models newsEnvov mosModelNewsGetVOV. <b>Not save content</b>.'.$id_content;
		array_push($arrErr,$message);
		return false;
	}
}

function mosModelAutoHuiGetContent($link, $id_content, $alias_content, $SiteID)
{
	
	global $arrErr;
	$browser	=	new phpWebHacks();
	$source_content	=	$browser->get($link);

	$head	=	$browser->get_head();
	if ($head['Status']['Code'] != 200) {
		$message	=	'#391 models newsAutohui mosModelAutoHuiGetContent. <b>Not Get content</b>. <br />'.$link.' <br /> status-code: '.$head['Status']['Code'];
		array_push($arrErr,$message);
		return false;
	}
	$html	=	loadHtmlString($source_content);

	$obj_VietAd 	= $html->find('div[id="VietAd"]',0);
	
	$href = new href();
	
	$title		=	strip_tags($obj_VietAd->first_child()->innertext);
	$title_alias = $alias_content;

	$date_time	=	$obj_VietAd->first_child()->next_sibling()->innertext;
	
	$obj_VietAd->first_child()->outertext	=	'';
	$obj_VietAd->first_child()->next_sibling()->outertext	=	'';
		
	$content	= $obj_VietAd->innertext;
	
	$obj_cuttext	=	new AutoCutText($content,15);
	$intro		=	$obj_cuttext->getIntro();
	$full_text	=	$obj_cuttext->getFulltext();

	$date_time	=	str_replace('[','',$date_time);
	$date_time	=	trim(str_replace(']','',$date_time));
	
	$date		=	explode('/',$date_time);
	$date_time	=	$date[2].'-'.$date[1].'-'.$date[0];	

	$obj_content			=	new stdClass();	
	
	// get video
	$videos	=	'';
	$list_video 	= $obj_VietAd->find('iframe');
	for ($i=0; $i<count($list_video); $i++)
	{		
	//http://www.youtube.com/embed/M-evf06BQFY
		if (!preg_match('/youtube\.com/ism',$list_video[$i]->src)) {
			continue;
		}		
		$videos	=	$videos . $list_video[$i] . '<br />';
	}
	
	$obj_content->title			=	trim(str_replace("\r\n",' ',$title));
	$obj_content->alias	=	$title_alias;
	$obj_content->intro		=	mostidy_clean(trim(str_replace("\r\n",' ',$intro)));
	$obj_content->fulltext	=	mostidy_clean(trim(str_replace("\r\n",' ',$full_text))) . $videos;
	$obj_content->content_date		=	$date_time;
	$obj_content->PageHTML	=	$source_content;	
	return $obj_content;
}
// process other
//http://www.oto-hui.com/a2103/bom-ve-dieu-khien-dien-tu-co-co-cau-ga-dien-tu.html
// Lưu nội dung bài viết
function mosModelAutoHuiSave($content, $section_id = 1, $catid = 1, $SiteID)
{	
	global $database, $my, $mainframe, $mosConfig_offset, $arrErr;
	$db	=	$database;
	
	// insert into
	$date	=	date('Y-m-d H:i:s');
	
	$nullDate = $database->getNullDate ();
	$row = new mosArticle2010_new_autohui( $db );

	$row->load($content->id_content);
	
	$row->firstRunDate  =	date ( 'Y-m-d H:i:s' );
	$row->latestRunDate =	date ( 'Y-m-d H:i:s' );
	$row->title			=	($content->title);
	$row->title_alias	=	($content->alias_content);
	$row->SiteID		=	$SiteID;
	$row->SiteName		=	'oto-hui.com';	
	$row->Domain		=	'oto-hui.com';
	$row->created		= 	date("Y-m-d H:i:s",strtotime($content->content_date));
	$row->introtext		=	trim(str_replace("\r\n",' ',$content->intro));
	$row->fulltext		=	trim(str_replace("\r\n",' ',$content->fulltext));
	$row->sectionid		=	$section_id;
	$row->catid			=	$catid;		
	$row->PageHTML 		=	$content->PageHTML;
	$row->status 		=	1;
	
	if (!$row->store()) {
		$messege	=	$db->getQuery();
		array_push($arrErr,$messege);
		return false;
	}	
	
	mosModelAutoHuiSaveMedia($content->arr_image, $row->id, $SiteID);
	mosUpdateOther($content->id_content,$content->title,$id,'#__article2010_new_autohui');
//	mosModelAutoHuiSaveParam($content, $row->id, $SiteID);
	return true;
}

function mosModelAutoHuiSaveMedia($arr_media,$contenid, $SiteID)
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
			$message	=	'#562 models newsEnvov mosModelAutoHuiSaveMedia.<b> Invalid store media</b> for '.$media->Path.'. Link: '.$media->media_url.' sql error: '.$row->getError ();
			array_push($arrErr,$message);				
		}
	}
	return true;
}

/**
 * store keyword, origional, duplicate
 *
 * @param obj $content
 * @param string id $contenid
 * @param string $SiteID
 */
function mosModelAutoHuiSaveParam($content,$contenid, $SiteID)
{
	global $arrErr,$database;
}