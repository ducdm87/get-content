<?php

function mosModelNewsAfamilyGetNews($get_existing = true)
{		
	global $arrErr,$database, $mosConfig_live_site;
	
	$arr_obj 	=	mosModelAfamilyGetCat();	
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
		$page	=	1;
		
	$bool	=	1;	
	
	$data_content = mosModelNewsAfamilyGetListContent($obj_cat->link, $page, $obj_cat->id_origional, $obj_cat->parent);

	$arr_ID		=	$data_content->arrID;
	$arr_link	=	$data_content->arr_link;
	$arr_title	=	$data_content->arr_title;
	$arr_alias	=	$data_content->arrAlias;
	
	$arr_result	=	array();
	
	if ($get_existing==false) {
		$db = $database;
		$aid	=	array();
		for ($i=0 ; $i<count($arr_ID); $i++)
		{
			$aid[]	=	$db->quote($arr_ID[$i]);
		}
		$_id	=	implode(',',$aid);
		$query = "SELECT id_original 
					FROM #__article2010_new_afamily 
					WHERE id_original in($_id)";
		$db->setQuery($query);		
		$arr_result	=	$db->loadResultArray();
	
		$arr_ban	=	mosBanidGet('afamily.vn',array('id_origional'),"id_origional  in($_id)");
				
		if (count($arr_result)) {
			if (count($arr_ban)) {
				$arr_result	=	array_merge($arr_result,$arr_ban);
			}
		}else {
			$arr_result	=	$arr_ban;
		}
	}

	$i = 0;
	$number_getContent	=	0;
	$option	=	$_REQUEST['option'];
	$browser	=	new phpWebHacks();
	for ($i = 0; $i < count($arr_ID); $i++)
	{
		if ($get_existing == false && count($arr_result) && in_array($arr_ID[$i],$arr_result))
		{
			array_push($arrErr,'#444 '.$arr_ID[$i].'(<b>'.$arr_title[$i].'</b>)('. $arr_link[$i] .') is existing and update');			
//			continue;
		}		
		$url		=	$mosConfig_live_site."/index.php?option=$option";		
		
		$begin		=	md5('BEGIN_GET_CONTENT_VOV');
		$end		=	md5('END_GET_CONTENT_VOV');
		
		$arr_post	=	array();
		$arr_post['begin_get_content']	=	$begin;
		$arr_post['end_get_content']	=	$end;
		$arr_post['task']				=	'getnewsafamily';
		$arr_post['secid']				=	$obj_cat->secid;
		$arr_post['catid']				=	$obj_cat->catid;
		$arr_post['catid_origional']	=	$obj_cat->id;
		$arr_post['cattitle_origional']	=	$obj_cat->title;
		$arr_post['content_id']			=	$arr_ID[$i];
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
         	$message	=	'ERROR_GET_CONTENT_VOV| #123 API false '.$arr_ID[$i].' '.$info;
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
	mosSaveAfamilyGetParam($obj_cat->id, $getold, $page, $data_content->isNext);
	
	$arr_obj[0]->number_getcontent	=	$number_getContent;
	$arr_obj[0]->page	=	$page;
	$arr_obj[0]->next	=	$data_content->isNext?'YES':'NO';
	$arr_obj[0]->date	=	date('Y-m-d');
	return $arr_obj;
}

function mosSaveAfamilyGetParam($catid, $getold, $page, $isNext)
{	
	global $arrErr,$database;
	$db	=	$database;
	$query	=	'';
	if ($isNext == false) {
		$query	=	'UPDATE `#__article2010_category_afamily` 
					SET `last_run` = '.$db->quote(date ( 'Y-m-d H:i:s' )).', 
						`lastGet_param` = '.$db->quote("getold=0;page=1;").',
						`publish` = '.$db->quote("-1").'
					WHERE `id` ='. $catid;
		}else {
			// con nh trang
			$query	=	'UPDATE `#__article2010_category_afamily` 
					SET `last_run` = '.$db->quote(date ( 'Y-m-d H:i:s' )).', 
						`lastGet_param` = '.$db->quote("getold=$getold;page=$page;").'
					WHERE `id` ='. $catid;
	}
	$db->setQuery($query);
	echo $db->getQuery();
	$db->query();
}

// Lấy category từ cơ sở dữ liệu
function mosModelAfamilyGetCat()
{
	global $database;
	$db	=	& $database;
	$arr_obj	=	array();
	if (isset($_REQUEST['cat_id'])) {
		$cat_id	=	$_REQUEST['cat_id'];
		$query = "SELECT *
			FROM `#__article2010_category_afamily`
			WHERE id = $cat_id";	
		$db->setQuery($query);
		$db->loadObject($obj);
		
		$arr_obj[]	=	$obj;
		$arr_obj[]	=	$obj;		
	}else {
		$query = "SELECT *
			FROM `#__article2010_category_afamily`
			WHERE publish = 1 AND (`lastGet_param` like '%getold=1;%' OR  `lastGet_param` = '')
			ORDER BY `last_run`
			LIMIT 0,2";			
		$db->setQuery($query);
		
		$arr_obj	=	$db->loadObjectList();
	}
	return $arr_obj;
}

function mosModelNewsAfamilyGetListContent($link,$page =1, $catid_origional, $cat_parent)
{
	global $arrErr,$database, $mosConfig_live_site;
	$root	=	'http://afamily.vn/';
	$href	=	new href();	
	$link	=	$link . 'trang-'.$page.'.chn';
	echo '$link: '. $link.' <br /> ';
	$browser	=	new phpWebHacks();
	$response	=	$browser->get($link);
	$html		=	loadHtmlString($response);
	
	$arr_link_article	=	array();
	
	$html		=	loadHtmlString($response);	
//	box6
	if ($page == 1) {
		if ($box6	=	$html->find('div[class="box6"]',0)) {
			if ($item	=	$box6->find('h1',0)) {
				$obj_article	=	new stdClass();
				$obj_article->title	=	strip_tags($item->innertext);
				$obj_article->link	=	$href->process_url(trim($item->first_child()->href),$root);
				$arr_link_article[]	=	$obj_article;
			}
			if ($item	=	$box6->find('ul',0)) {
				if ($items	=	$box6->find('li')) {
					for ($i=0; $i<count($items); $i++)
					{
						$item	=	$items[$i];
						$obj_article	=	new stdClass();
						$obj_article->title	=	strip_tags($item->innertext);
						$obj_article->link	=	$href->process_url(trim($item->first_child()->href),$root);
						$arr_link_article[]	=	$obj_article;
					}
				}
			}
		}
	}
	
	//	box8
	if ($box8	=	$html->find('div[class="box8"]',0)) {
		$box8	=	$box8->outertext;
		$reg_block	=	'/(<p class="time">.*?)<p class="line2">\s*<\/p>/ism';
		if (preg_match_all($reg_block, $box8, $matches_content)) {
			$arr_block	=	$matches_content[1];
			// blocks
			for ($i=0; $i<count($arr_block); $i++)
			{
				$sub_html	=	loadHtmlString($arr_block[$i]);
				if ($time	=	$sub_html->find('p[class="time"]',0)) {
					$item	=	$time->next_sibling();
					if ($item->tag == 'a') {
						$obj_article	=	new stdClass();
						$obj_article->title	=	strip_tags($item->innertext);
						$obj_article->link	=	$href->process_url(trim($item->href),$root);
						$arr_link_article[]	=	$obj_article;
					}									
				}
				// list
				if ($items	=	$sub_html->find('li')) {
					for ($j=0; $j<count($items); $j++)
					{
						$item	=	$items[$j];
						$obj_article	=	new stdClass();
						$obj_article->title	=	strip_tags($item->innertext);
						$obj_article->link	=	$href->process_url(trim($item->first_child()->href),$root);
						$arr_link_article[]	=	$obj_article;
					}
				}			
			}
		}
	}
	
	
	$page++;
	$isNext	=	false;
	if (count($arr_link_article)) {
		$isNext	=	true;
	}
		
//	if ($items = $html->find('div[class="pag"]',0)) {		
//		$item	=	$items->innertext;
//		$reg_page	=	'/<a[^>]*href="[^"]*trang-'.$page.'\.chn"[^>]*>\s*'.$page.'\s*<\/a>/ism';
//
//		if (preg_match($reg_page,$item)) {
//			$isNext	=	true;
//		}
//	}	
	
	$obj_return	=	new stdClass();
	
	$arr_link	=	array();
	$arr_id		=	array();
	$arr_alias	=	array();
	$arr_title	=	array();
	$reg_id	=	'/\/(\d+)\/([^\/]+)\/$/ism';
	
	for($i=0; $i < count($arr_link_article); $i++)
	{
		$item	=	$arr_link_article[$i];		
		$arr_link[]		=	$item->link;
		$arr_title[]	=	$item->title;
		if (!preg_match($reg_id,$item->link,$matches_id)) {			
			continue;
		}		
		$arr_id[]		=	$matches_id[1];
		$arr_alias[]	=	$matches_id[2];
	}
	$obj_return->arr_link	=	$arr_link;
	$obj_return->arr_title	=	$arr_title;
	$obj_return->arrID		=	$arr_id;
	$obj_return->arrAlias	=	$arr_alias;
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
function mosModelNewsAfamilyGetAFML($id_content, $link_content, $alias_content, $title_content, $cattitle_origional, $catid_origional, $secid, $catid, $path_image = 'images', $link_image,$SiteID = 'plh190')
{
	global $arrErr;
	
	if (!$content	=	mosModelNewsAfamilyGetContent($link_content, $id_content, $title_content, $alias_content))
	{
		$message_ban	=	$arrErr[count($arrErr)-1];
		$message	=	'#389 models newsvov mosModelNewsAfamilyGetAFML. Not get content.'.$id_content;
		array_push($arrErr,$message);
		mosBanidStore('afamily.vn','',$id_content,1,$message_ban."\r\n".$message);
		return false;
	}
	$content->intro		=	mosModelNewsAfamilyProcessOther($content->intro,$SiteID, $id_content);
	$content->fulltext	=	mosModelNewsAfamilyProcessOther($content->fulltext,$SiteID, $id_content);
//	
	$content->secid	=	$secid;
	$content->catid	=	$catid;
	$content->cat_title			=	$cattitle_origional;
	$content->catid_original	=	$catid_origional;

	$root	=	'http://afamily.vn/';
	$arr_Images	=	array();
	mosGetImages($content, $root, $arr_Images, $path_image, $link_image);

	$content->arr_image		=	$arr_Images;
	$content->id_content	=	$id_content;	
	
	if (!mosModelNewsAfamilySave($content, $SiteID)) {
		$message	=	'#521 models news mosModelNewsAfamilyGetAFML. Not save content.'.$id_content;
		array_push($arrErr,$message);
		return false;
	}
}

function mosModelNewsAfamilyGetContent($link,$id_content, $title_content, $alias_content)
{
	global $arrErr,$database;
	
	$db	=	$database;		
	$browser	=	new phpWebHacks();
	$response	=	$browser->get($link);
	
	$html	=	loadHtmlString($response);
	
	// get intro
	if (!$item1 = $html->find('h1[class="title"]',0) and !$item2 = $html->find('h2[class="title"]',0) ) {
		$message	=	'#333 models news mosModelNewsAfamilyGetContent. Invalid get introtext for '.$link;
		array_push($arrErr,$message);
		return false;
	}
	$item	=	$item1?$item1:$item2;
	$intro	=	strip_tags($item->innertext);

	if ($item = $html->find('ul[class="relativenews"]',0)) {
		$intro	.=	$item->outertext;
	}
	
	// get full text	
	if (!$item = $html->find('div[id="divChiTiet"]',0)) {
		$message	=	'#433 models news mosModelNewsGetContent. Invalid get fulltext for '.$link;
		array_push($arrErr,$message);
		return false;
	}
	
	$full_text	=	$item->innertext;
	
	// get date	detail_time
	
	// get full text	
	if (!$item = $html->find('p[class="time"]',0)) {
		$message	=	'#533 models news mosModelNewsGetContent. Invalid get date time for '.$link;
		array_push($arrErr,$message);
		return false;
	}
	$time	=	trim(strip_tags($item->find('span',0)->innertext));
	$item->find('span',0)->outertext	=	'';
	$date	=	strip_tags($item->innertext);	
	//
	$date		=	explode('-',$date);
	$date		=	trim($date[2]).'-'.trim($date[1]).'-'.trim($date[0]);	
	$date_time	=	$date.' '.$time;

	$href		=	new href();	
	$obj_content			=	new stdClass();
	$obj_content->title		=	trim(str_replace(array("\r\n","\t"),array(' ',' '),$title_content));
	
	$obj_content->intro		=	trim(str_replace(array("\r\n","\t"),array(' ',' '),mostidy_clean($intro)));
	
	$obj_content->fulltext	=	trim(str_replace(array("\r\n","\t"),array(' ',' '),mostidy_clean($full_text)));	
	
	$obj_content->link		=	$link;	
	$obj_content->alias		=	$alias_content;
	$obj_content->content_date		=	$date_time;	
	$obj_content->PageHTML	=	$response;
	
	return $obj_content;
}

function mosModelNewsAfamilyProcessOther($str_in, $SiteID,$id_original)
{
	global $database,$error;
	$db	=	$database;
	$reg_link_other = '/<a.*?href=["\' ]*([^"\' ]*)["\' ]*.*?>(.*?)<\/a>/ism';
//	/van-hoa/2011060505158324/Jennifer-Aniston-chinh-thuc-cong-khai-ban-trai-moi/	
	$reg_id_other = '/\/(\d+)\/([^\/]+)\/$/ism';
	$href	=	new href();
	$root	=	'http://afamily.vn';
	
	if (!preg_match_all($reg_link_other,$str_in,$matches_link)) {
		return $str_in;
	}
	
	for ($i=0; $i< count($matches_link[0]); $i++)
	{		
		$link	=	$href->process_url($matches_link[1][$i], $root);
		
		if (!preg_match($reg_id_other, $link,$matches)) {
			$title		=	strip_tags($matches_link[2][$i]);
			$str_in		=	str_replace($matches_link[0][$i],$title,$str_in);
			continue;
		}
		
		$id_orgional_other	=	$matches[1];		
		
		if ($id_result	=	mosModelNewsAfamilySaveOther($SiteID, $id_original, $matches[1],$matches_link[0][$i], $link)) {
			$title		=	strip_tags($matches_link[2][$i]);
			$link_content = '<a title=\''.str_replace(array('&gt;','\''),array(' ','"'),$title).'\' href="/index.php?option=com_content&task=view&id=' . $id_result.'" >'.$title."</a>";
			$str_in		=	str_replace($matches_link[0][$i],$link_content,$str_in);
		}	
	}	
	return $str_in;
}

function mosModelNewsAfamilySaveOther ($SiteID,$id_original,$id_original_other,$str_replace,$link_other)
{
	global $database,$error;
	
	$db	=	& $database;
	$query = "SELECT * FROM #__article2010_new_afamily WHERE id_original = ".$db->quote(trim($id_original_other));
	$db->setQuery($query);
	
	$id_result	=	false;
	
	if ($db->loadObject($obj)) {
		 $state = 0;	
		 $id_result	=	$obj->id;
		 $type	=	1;
	}else {
		$browser		=	new phpWebHacks();	
		$browser->get($link_other);		
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
	$result	=	mosOtherStore($SiteID, $id_original, $id_original_other, $str_replace, $link_other, $type, $state);
	
	if (! $result) {
		$error->arr_err[]	=	"Error insert or update data for other table ";
		return false;
	}
	return $id_result;
}

function mosModelNewsAfamilySave($content, $SiteID = 'af270')
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
	$row->SiteName		=	'afamily';	
	$row->Domain		=	'afamily.vn';	
	$row->SourceURL		=	$content->link;
	$row->created		= 	date("Y-m-d H:i:s",strtotime($content->content_date));	
	$row->title			=	$content->title;
	$row->title_alias	=	$content->alias;
	$row->introtext		=	$content->intro;
	$row->fulltext		=	$content->fulltext;
	$row->sectionid		=	$content->secid;
	$row->catid			=	$content->catid;
	$row->CatName		=	$content->cat_title;
	$row->catid_original=	$content->catid_original;
	$row->PageHTML 		=	$content->PageHTML;
			
	$fmtsql = "INSERT INTO `#__article2010_new_afamily` SET %s ON DUPLICATE KEY UPDATE  %s  ";
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
	mosModelNewsAfamilySaveMedia($content->arr_image,$id,$SiteID);
	mosUpdateOther($content->id_content,$content->title,$id,'#__article2010_new_afamily');
	return true;
}

function mosModelNewsAfamilySaveMedia($arr_media,$contenid, $SiteID = 'af270')
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
			$message	=	'#562 models news mosModelNewsAfamilySaveMedia. Invalid store media for '.$media->Path.'. Link: '.$media->media_url.' sql error: '.$row->getError ();
			array_push($arrErr,$message);
		}
	}
	return true;
}