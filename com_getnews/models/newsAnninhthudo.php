<?php
//mosCurrentDate('%Y-%m-%d %H:%M:%S'); 

function mosModelNewsAnninhthudoGetNews($date_started = null, $get_existing = true)
{	
	global $arrErr,$database, $mosConfig_live_site, $mosConfig_offset;
	$db = $database;
	$arr_obj 	=	getCatFromData();
	if (count($arr_obj) <2 ) {
		echo ' sucessfull';
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
		$page	=	1;
		
	$bool	=	1;	
	
	$data_content = mosModelAnninhthudoGetListContent($obj_cat->domain, $page);
	$arr_ID		=	$data_content->arrID;
	$arr_link	=	$data_content->arr_link;
	
	$arr_result	=	array();
	
	if (count($arr_ID) <1 and $getold) {
		$bool	=	1;		
	}
	else {
		$bool	=	0;
		if ($get_existing==false) {
			$db = $database;
			$_id	=	implode(',',$arr_ID);
			$query = "SELECT id_original 
					FROM #__article2010_new_anninhthudo
					WHERE id_original in($_id)";
			$db->setQuery($query);		
			$arr_result	=	$db->loadResultArray();	
			if (count($arr_ID) == count($arr_result)) 
			{
				$bool = 1;			
			}	
		}		
	}
	
	$number_run	=	1;
	while ($bool and $number_run <10) {
		$page ++ ;
		$data_content = mosModelAnninhthudoGetListContent($obj_cat->domain, $page);
		$arr_ID		=	$data_content->arrID;
		$arr_link	=	$data_content->arr_link;		
		if (count($arr_ID) <1 and $data_content->isNext) {
			$bool	=	1;			
		}
		else if ($get_existing==false) {
			$db = $database;
			$_id	=	implode(',',$arr_ID);
			$query = "SELECT id_original 
					FROM #__article2010_new_anninhthudo
					WHERE id_original in($_id)";
			$db->setQuery($query);		
			$arr_result	=	$db->loadResultArray();
			if (count($arr_ID) == count($arr_result) and $data_content->isNext) {
				$bool	=	1;				
			}else {
				$bool	=	0;
			}
		}
		$number_run ++;	
	}	
	
	$arr_result	=	array();
	if ($get_existing==false and is_array($arr_ID) and count($arr_ID)>0) {
		$db = $database;
		$_id	=	implode(',',$arr_ID);
		$query = "SELECT id_original 
					FROM #__article2010_new_anninhthudo
					WHERE id_original in($_id)";
		$db->setQuery($query);
		$arr_result	=	$db->loadResultArray();
		
		$arr_ban	=	mosBanidGet('anninhthudo.vn',array('id_origional'),"id_origional  in($_id)");	
		
		if (count($arr_result)) {
			if (count($arr_ban)) {
				$arr_result	=	array_merge($arr_result,$arr_ban);
			}
		}else {
			$arr_result	=	$arr_ban;
		}
	}
	
	$number_getContent	=	0;
	$i = 0;
	$option	=	$_REQUEST['option'];
	
	echo '<hr />';
	echo '$arr_ID: '; var_dump($arr_ID);
	echo '<br />';
	echo '<br />';
	echo '$arr_result: '; var_dump($arr_result);
	echo '<hr />';
	
	for ($i = 0; $i < count($arr_ID) ; $i++)
	{
		if ($get_existing==false && in_array($arr_ID[$i],$arr_result))
		{
			array_push($arrErr,'#444 '.$arr_ID[$i].' is existing');
			continue;
		}
		
		$id_content	=	$arr_ID[$i];
		$url		=	$mosConfig_live_site."/index.php?option=$option";
	
		$arr_post	=	array();		
		$begin		=	md5('BEGIN_GET_CONTENT_ANTD');
		$end		=	md5('END_GET_CONTENT_ANTD');
	
		$arr_post['begin_get_content']	=	$begin;
		$arr_post['end_get_content']	=	$end;
		$arr_post['task']				=	'getnewAnninhthudo';
		$arr_post['content_id']			=	$arr_ID[$i];
		$arr_post['content_link']		=	$arr_link[$i];		
		$arr_post['secid']		=	$obj_cat->secid;
		$arr_post['catid']		=	$obj_cat->catid;
		$arr_post['catid_origional']	=	$obj_cat->id_origional;
		$arr_post['cat_title']	=	$obj_cat->title;		
		
//		echo $url;
//		echo '<br /><hr />';
//		$a	=	array();
//		
//		foreach ($arr_post as $k=>$v) {
//			$a[]	=	"$k=$v";
//		}
//		echo implode('&',$a);
//		die();
		
		$browser	=	new phpWebHacks();
		$info	=	$browser->post($url,$arr_post);
        
         if (preg_match('/' . $begin . '(.*?)' . $end . '/ism', $info, $match)) 
         {                   
             $info=trim($match[1]);
         }
         else {
         	$message	=	'ERROR_GET_CONTENT_ANTD| #123 API false '.$id_content.' '.$info;
         	array_push($arrErr,$message);
            continue;
         }
      	 if (stristr($info,'ERROR_GET_CONTENT_ANTD')) {
  	 		$message	=	'ERROR_GET_CONTENT_ANTD| '.$info;
     		array_push($arrErr,$message);
            continue;
         }
          $number_getContent	=	$number_getContent + 1;
	}
	mosSaveAnninhthudoGetParam($obj_cat->id, $getold, $page, $data_content->isNext);	

	$arr_obj[0]->number_getcontent	=	$number_getContent;
	$arr_obj[0]->page	=	$page;
	$arr_obj[0]->next	=	$data_content->isNext?'YES':'NO';
	$arr_obj[0]->date	=	date('Y-m-d');
	return $arr_obj;
}


function mosSaveAnninhthudoGetParam($catid, $getold, $page, $isNext)
{	
	global $arrErr,$database;
	$db	=	$database;
	$query	=	'';
	$lastGet_param	=	'';
	if ($isNext == false) {
//		$page--;
		$lastGet_param	=	$db->quote("getold=0;page=$page;");			
	}else {
			$lastGet_param	=	$db->quote("getold=$getold;page=$page;");			
	}
	$query	=	'UPDATE `#__article2010_category_anninhthudo` 
					SET `last_run` = '.$db->quote(date ( 'Y-m-d H:i:s' )).', 
						`lastGet_param` = '.$lastGet_param.'
					WHERE `id` ='. $catid;	
	$db->setQuery($query);	
	$db->query();
}

function getCatFromData()
{
	global $database;
	$db	=	& $database;
	$arr_obj	=	array();
	if (isset($_REQUEST['catid_origional'])) {
		$id_origional	=	$_REQUEST['catid_origional'];
		$query = "SELECT *
			FROM `#__article2010_category_anninhthudo`
			WHERE publish = 1 and id_origional = $id_origional
			ORDER BY `last_run`
			LIMIT 0,2";	
		$db->setQuery($query);
		$db->loadObject($obj);
		
		$arr_obj[]	=	$obj;
		$arr_obj[]	=	$obj;
	}else {
		$query = "SELECT *
			FROM `#__article2010_category_anninhthudo`
			WHERE publish = 1 
			ORDER BY `last_run`
			LIMIT 0,2";	
//		AND (`lastGet_param` like '%getold=1;%' OR  `lastGet_param` = '')
		$db->setQuery($query);
		$arr_obj	=	$db->loadObjectList();
	}	
	return $arr_obj;
}

function mosModelAnninhthudoGetListContent($link, $page)
{	
	global $arrErr,$database, $mosConfig_live_site;	
//	trang3.antd
	$link	=	preg_replace('/\.antd$/ism','/trang'.$page.'.antd',$link);	
	$link		=	str_replace(' ','%20',$link);	

	echo '<br />';
	echo $link;

	$browser	=	new phpWebHacks();
	$response	=	$browser->get($link);
	
	$html		=	loadHtmlString($response);
	$href		=	new href();
	$arr_link	=	array();
	$arr_ID		=	array();	
	$root		=	'http://www.anninhthudo.vn/';
	$reg_id		=	'/\/(\d+)\.antd$/ism';
	// tin noi bat
	if ($page == 1) {
//		if ($list_item	=	$html->find('div[id="spotlight-listing"]',0)) {
//			if ($items		=	$list_item->find('div[class="spotlight"]')) {
//				for ($i=0; $i<count($items); $i++)
//				{
//					$item	=	$items[$i]->find('h1[class="title"]',0)->first_child();
//					$link_content	=	$href->process_url(trim($item->href), $root);
//					if (!preg_match($reg_id, $link_content, $matches_id)) {
//						continue;
//					}			
//					$arr_link[]	=	$link_content;			
//					$arr_ID[]	=	$matches_id[1];	
//				}			
//			}	
//		}
		
		if ($list_item	=	$html->find('div[class="latest"]',0)) {
			if ($items		=	$list_item->find('li')) {
				for ($i=0; $i<count($items); $i++)
				{
					$item	=	$items[$i]->first_child();
					
					$link_content	=	$href->process_url(trim($item->href), $root);
					if (!preg_match($reg_id, $link_content, $matches_id)) {
						continue;
					}			
					$arr_link[]	=	$link_content;			
					$arr_ID[]	=	$matches_id[1];	
				}			
			}	
		}
		if ($list_item	=	$html->find('div[id="popular"]',0)) {
			if ($items		=	$list_item->find('p[class="title"]')) {
				for ($i=0; $i<count($items); $i++)
				{
					$item	=	$items[$i]->first_child();
					
					$link_content	=	$href->process_url(trim($item->href), $root);
					if (!preg_match($reg_id, $link_content, $matches_id)) {
						continue;
					}			
					$arr_link[]	=	$link_content;			
					$arr_ID[]	=	$matches_id[1];	
				}			
			}	
		}
	}
	
	if ($list_item	=	$html->find('div[class="column-listing"]',0)) {
		
		$items	=	$list_item->find('div[class="item"]');
				
		for ($i=0; $i<count($items); $i++)
		{
			$item	=	$items[$i];
			$link_content	=	$href->process_url(trim($item->find('a[class="title"]',0)->href), $root);			
			if (!preg_match($reg_id, $link_content, $matches_id)) {
				continue;
			}			
			$arr_link[]	=	$link_content;			
			$arr_ID[]	=	$matches_id[1];			
		}
	}
	
	$page++;
	$isNext	=	false;
	if ($items = $html->find('div[class="page"]',0)) {
		$item	=	$items->innertext;
//		echo htmlspecialchars($item);
		$reg_page	=	'/<a[^>]*href="[^"]*\/trang'.$page.'\.antd"[^>]*>/ism';		
		if (preg_match($reg_page,$item)) {
			$isNext	=	true;
		}
	}
	$_arrID	=	array();
	$_arr_link	=	array();
	for ($i = 0 ; $i<count($arr_ID); $i++)
	{
		if (in_array($arr_ID[$i],$_arrID)) {
			continue;
		}
		$_arrID[]	=	$arr_ID[$i];
		$_arr_link[]	=	$arr_link[$i];
	}
	$obj_return	=	new stdClass();
	$obj_return->arrID	=	$_arrID;
	$obj_return->arr_link	=	$_arr_link;
	$obj_return->isNext	=	$isNext;	
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
function mosModelNewsAnninhthudoGetANTD($param, $path_image, $link_image, $SiteID = 'antd175')
{
	global $arrErr;
	$link = $param['content_link'];
	
	if (!$content	=	mosModelNewsAnninhthudoGetContent($param['content_link'], $param['content_id']))
	{
		$message_ban	=	$arrErr[count($arrErr)-1];		
		$message	=	'#389 models news mosModelNewsAnninhthudoGetTN. Not get content.'.$param['content_id'];
		array_push($arrErr,$message);
		mosBanidStore('anninhthudo.vn','',$param['content_id'],1,$message_ban."\r\n".$message);
		return false;
	}

//	$content->intro		=	mosModelNewsAnninhthudoProcessOther($content->intro,$SiteID, $id_content);
	$content->fulltext	=	mosModelNewsAnninhthudoProcessOther($content->fulltext,$SiteID, $param['content_id']);
	
	$root	=	'http://www.anninhthudo.vn/';
	$arr_Images	=	array();
	mosGetImages($content,$root, $arr_Images,$path_image, $link_image);
	
	$content->arr_image		=	$arr_Images;
	$content->id_content	=	$param['content_id'];
	$content->secid	=	$param['secid'];
	$content->catid	=	$param['catid'];
	$content->catid_origional	=	$param['catid_origional'];
	$content->cat_title			=	trim(str_replace("\r\n",'',$param['cat_title']));
	
	if (!mosModelAnninhthudoSave($content, $SiteID)) {
		$message	=	'#391 models news mosModelNewsAnninhthudoGetANTD. Not save content.'.$id_content;
		array_push($arrErr,$message);
		return false;
	}
}

function mosModelNewsAnninhthudoGetContent($link,$id_content)
{
	echo $link;
	global $arrErr,$database;
	$db	=	$database;
	
	$browser	=	new phpWebHacks();
	$response	=	$browser->get($link);
	
	$html	=	loadHtmlString($response);
	$href	=	new href();
	
	if (!$article	=	$html->find('div[class="article"]',0)) {
		$message	=	'#397 models news mosModelNewsAnninhthudoGetContent. Not get article.'.$id_content. ' '. $link;
		array_push($arrErr,$message);
		return false;
	}
	
	if (!$title = $article->find('p[class="title"]',0)) {
		if (!$title = $article->find('h1[class="title"]',0)) {
			$message	=	'#398 models news mosModelNewsAnninhthudoGetContent. Not get title.'.$id_content. ' '. $link;
			array_push($arrErr,$message);
			return false;
		}		
	}
	$title		 =	$title->innertext;
	$title_alias = $href->convertalias($title);
	$article->find('p[class="title"]',0)->outertext = '';
	
	if (!$intro	=	strip_tags($article->find('div[class="sapo"]',0)->innertext)) {
		$message	=	'#498 models news mosModelNewsAnninhthudoGetContent. Not get intro.'.$id_content. ' '. $link;
		array_push($arrErr,$message);
		return false;
	}
	$intro	=	str_replace('(ANTĐ)','', $intro);
	@$article->find('div[class="sapo"]',0)->outertext	=	"";	
		
	if (!$date_time	=	$article->find('p[class="adate"]',0)) {
		if (!$date_time	=	$article->find('p[class="date"]',0)) {
			$message	=	'#598 models news mosModelNewsAnninhthudoGetContent. Not get date time.'.$id_content. ' '. $link;
			array_push($arrErr,$message);
			return false;	
		}		
	}
	$date_time	=	strip_tags($date_time->innertext);
	@$article->find('p[class="date"]',0)->outertext	=	"";	
	@$article->find('p[class="adate"]',0)->outertext	=	"";	
	$author	=	'';
	if ($author	=	$article->find('p[class="author"]',0)) {		
		$author	=	$author->outertext;
		@$article->find('p[class="author"]',0)->outertext	=	"";		
	}
	@$article->find('div[class="action"]',0)->outertext	=	"";	
	@$article->find('div[class="tag"]',0)->outertext	=	"";	
	
	$reg_date	=	'/(\d+)\/(\d+)\/(\d+)\s*(\d+)\:(\d+)/ism';
	if (!preg_match($reg_date, $date_time, $matches_time)) {
		
	}
	
	$date_time	=	$matches_time[3].'-'.$matches_time[2].'-'.$matches_time[1].' '.$matches_time[4].':'.$matches_time[5].':00';	
	
	$full_text	=	str_replace('(ANTĐ)','', $article->innertext);
	
	$obj_content			=	new stdClass();
	$obj_content->title		=	trim(str_replace("\r\n",' ',$title));
	$obj_content->intro		=	mostidy_clean(trim(str_replace("\r\n",' ',$intro)));
	$obj_content->fulltext	=	mostidy_clean(trim(str_replace("\r\n",' ',$full_text)));	
	$obj_content->link		=	$link;
	$obj_content->alias		=	strtolower($title_alias);
	$obj_content->content_date		=	$date_time;
	$obj_content->author	=	$author;
	$obj_content->PageHTML	=	$response;

	return $obj_content;
}

function mosModelNewsAnninhthudoProcessOther($str_in, $SiteID,$id_original)
{
	global $database,$error;
	$db	=	$database;
	$reg_link_other = '/<a.*?href=["\' ]*([^"\' ]*)["\' ]*.*?>(.*?)<\/a>/ism';
	$reg_id_other = '/\/(\d+)\.antd/ism';
	$href	=	new href();
	$root	=	'http://www.anninhthudo.vn';
	
	if (!preg_match_all($reg_link_other,$str_in,$matches_link)) {		
		return $str_in;
	}
	
	for ($i=0; $i< count($matches_link[0]); $i++)
	{		
		$link	=	$href->process_url($matches_link[1][$i], $root); 
		if (!preg_match($reg_id_other, $link,$matches)) {			
//			ArticleID=105174&ChannelID=92
			if (!preg_match('/ArticleID=(\d+)\&ChannelID/ism',$link,$matches)) {
				$title		=	strip_tags($matches_link[2][$i]);
				$str_in		=	str_replace($matches_link[0][$i],$title,$str_in);				
				continue;	
			}			
		}
		$id_orgional_other	=	$matches[1];
		
		if ($id_result	=	mosModelNewsANTDSaveOther($SiteID, $id_original, $matches[1],$matches_link[0][$i], $link)) {
			$title		=	strip_tags($matches_link[2][$i]);
			$link_content = '<a title=\''.str_replace(array('&gt;','\''),array(' ','"'),$title).'\' href="/index.php?option=com_content&task=view&id=' . $id_result.'" >'.$title."</a>";
			$str_in		=	str_replace($matches_link[0][$i],$link_content,$str_in);
		}
	}	
	return $str_in;
}

function mosModelNewsANTDSaveOther ($SiteID,$id_original,$id_original_other,$str_replace,$link_other)
{
	global $database,$error;
	
	$db	=	& $database;
	$query = "SELECT * FROM #__article2010_new_anninhthudo WHERE id_original = ".trim($id_original_other);
	$db->setQuery($query);	
	$id_result	=	false;
	
	$state = 0;
	if ($db->loadObject($obj)) {		 
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


function mosModelAnninhthudoSave($content, $SiteID = 'antd175')
{	
	global $database, $my, $mainframe, $mosConfig_offset, $arrErr;
	$db	=	$database;		
	// insert into
	$date	=	date('Y-m-d H:i:s');
	
	$nullDate = $database->getNullDate ();
	$row = new mosVovArticle2010_new2( $db );
	
	$row->firstRunDate  =	$row->firstRunDate ? $row->firstRunDate : date ( 'Y-m-d H:i:s' );
	$row->latestRunDate =	date ( 'Y-m-d H:i:s' );	
	$row->id_original	=	$content->id_content;	
	$row->SiteID		=	$SiteID;	
	$row->SiteName		=	'anninhthudo.vn';	
	$row->Domain		=	'anninhthudo.vn';	
	$row->SourceURL		=	$content->link;
	$row->created		= 	date("Y-m-d H:i:s",strtotime($content->content_date));	
	$row->title			=	$content->title;
	$row->title_alias	=	$content->alias;
	$row->introtext		=	$content->intro;
	$row->fulltext		=	$content->fulltext;
	$row->sectionid		=	$content->secid;
	$row->catid			=	$content->catid;
	$row->CatName		=	$content->cat_title;
	$row->catid_original=	$content->catid_origional;
	$row->PageHTML 		=	$content->PageHTML;

	$fmtsql = "INSERT INTO `#__article2010_new_anninhthudo` SET %s ON DUPLICATE KEY UPDATE  %s  ";
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
	mosUpdateOther($content->id_content,$content->title,$id,'#__article2010_new_anninhthudo');
	return true;
}

function mosModelNewsSaveMedia($arr_media,$contenid, $SiteID = 'antd175')
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