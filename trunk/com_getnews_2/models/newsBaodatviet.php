<?php

function mosModelBDVGetData($get_existing = true)
{		
	global $arrErr,$database, $mosConfig_live_site;
	
	$arr_obj 	=	mosModelBaodatvietGetCat();
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
	$data_content = mosModelNewsBaodatvietGetListContent($obj_cat->link, $page, $obj_cat->id_origional, $obj_cat->parent);
	
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
					FROM #__article2010_new_baodatviet 
					WHERE id_original in($_id)";
		$db->setQuery($query);	

		$arr_result	=	$db->loadResultArray();		
		$arr_ban	=	mosBanidGet('baodatviet.vn',array('id_origional'),"id_origional  in($_id)");
		$arr_geted	=	mosGetDBOBJ('#__article2010_totalcontent',array('id_origional'),'id_origional  in('.$_id.') AND SiteID='.$db->quote('bdv290'));		

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
	echo '$arr_result: ';	var_dump($arr_result);
	echo '<br /> ';
	echo '$arr_ID: ';	var_dump($arr_ID);
	echo '<br /> [$arr_ID] : '. count($arr_ID);

	for ($i = 0; $i < count($arr_ID) ; $i++)
	{
		if ($get_existing == false && count($arr_result) && in_array($arr_ID[$i],$arr_result))
		{
			array_push($arrErr,'#444 '.$arr_ID[$i].' is existing');
			continue;
		}		
		
		$url		=	$mosConfig_live_site."/index.php?option=$option";		
		
		$begin		=	md5('BEGIN_GET_CONTENT_VOV');
		$end		=	md5('END_GET_CONTENT_VOV');
		
		$arr_post	=	array();
		$arr_post['begin_get_content']	=	$begin;
		$arr_post['end_get_content']	=	$end;
		$arr_post['task']				=	'getbaodatviet';
		$arr_post['secid']				=	$obj_cat->secid;			
		$arr_post['catid']				=	$obj_cat->catid;			
		$arr_post['catalias_origional']	=	$obj_cat->alias_origional;
		$arr_post['content_id']			=	$arr_ID[$i];
		$arr_post['content_link']		=	$arr_link[$i];
		$arr_post['content_alias']		=	$arr_alias[$i];
		$arr_post['content_title']		=	$arr_title[$i];
//		if ($arr_ID[$i] != 96353) {
//			continue;
//		}
//		
//		echo $url;		
//		$a	=	array();
//		foreach ($arr_post as $k=>$v) {
//			$a[]	=	"$k=$v";
//		}
//		echo '<br /> <hr />';
//		echo implode('&',$a);
//		die();
		
//		$now = date('Y-m-d H:i:s');
//		echo '<br /> ';
//		echo $arr_ID[$i].' : '.$now;
		
		$info	=	$browser->post($url,$arr_post);

         if (preg_match('/' . $begin . '(.*?)' . $end . '/ism', $info, $match)) 
         {
             $info=trim($match[1]);
         }
         else {
         	$message	=	'ERROR_GET_CONTENT_PHAPLUATXAHOI| #123 API false '.$arr_ID[$i].' '.$info;
         	array_push($arrErr,$message);
            continue;
         }
      	 if (stristr($info,'ERROR_GET_CONTENT_PHAPLUATXAHOI')) {
  	 		$message	=	'ERROR_GET_CONTENT_PHAPLUATXAHOI| '.$info;
     		array_push($arrErr,$message);
            continue;
         }
//         $now = date('Y-m-d H:i:s');
//         echo ' => '.$now;
//         echo '<br />';
         $number_getContent	=	$number_getContent + 1;
	}
	
	mosSaveBDVGetParam($obj_cat->id, $getold, $page, $data_content->isNext,$obj_cat);
	
	$arr_obj[0]->number_getcontent	=	$number_getContent;
	$arr_obj[0]->page	=	$page;
	$arr_obj[0]->next	=	$data_content->isNext?'YES':'NO';
	$arr_obj[0]->date	=	date('Y-m-d');
	return $arr_obj;
}

function mosSaveBDVGetParam($catid, $getold, $page, $isNext, $obj_cat)
{	
	global $arrErr,$database;
	$db	=	$database;
	$query	=	'';
	if ($isNext == false) {
		$query	=	'UPDATE `#__article2010_category_baodatviet` 
					SET `last_run` = '.$db->quote(date ( 'Y-m-d H:i:s' )).', 
						`lastGet_param` = '.$db->quote("getold=0;page=1;").'
					WHERE `id` ='. $catid;
		}else {
			// con nh trang
			$query	=	'UPDATE `#__article2010_category_baodatviet` 
					SET `last_run` = '.$db->quote(date ( 'Y-m-d H:i:s' )).', 
						`lastGet_param` = '.$db->quote("getold=$getold;page=$page;").'
					WHERE `id` ='. $catid;
	}
	$db->setQuery($query);
//	echo $db->getQuery();
	$db->query();
	if ($isNext == false) {
		$file_name	=	dirname(__FILE__).DS.'..'.DS.'log'.DS.'bdv_max.txt';
		echo $file_name;
		$fp = fopen( $file_name, 'a');
		fputs($fp, "__________________________________________________\r\n");
		fputs($fp, "page: $page\r\n");
		fputs($fp, "id: $obj_cat->id\r\n");
		fputs($fp, "id_origional: $obj_cat->id_origional\r\n");
		fputs($fp, "link: $obj_cat->link\r\n");
		fclose($fp);
	}	
}

// Lấy category từ cơ sở dữ liệu
function mosModelBaodatvietGetCat()
{
	global $database;
	$db	=	& $database;
	$arr_obj	=	array();
	if (isset($_REQUEST['cat_id'])) {
		$cat_id	=	$_REQUEST['cat_id'];
		$query = "SELECT *
			FROM `#__article2010_category_baodatviet`
			WHERE id = $cat_id";
		$db->setQuery($query);
		$db->loadObject($obj);
		
		$arr_obj[]	=	$obj;
		$arr_obj[]	=	$obj;
	}else {
		$query = "SELECT *
			FROM `#__article2010_category_baodatviet`
			WHERE publish = 1 
			ORDER BY `last_run`
			LIMIT 0,2";
		$db->setQuery($query);		
		$arr_obj	=	$db->loadObjectList();
		if (count($arr_obj) == 1) {
			$arr_obj[]		=	$arr_obj[0];
		}
	}	
	return $arr_obj;
}

function mosModelNewsBaodatvietGetListContent($link,$page =1, $catid_origional, $cat_parent)
{
	global $arrErr,$database, $mosConfig_live_site;
	$root	=	'http://baodatviet.vn/';
	$href	=	new href();	
	$link	=	str_replace('.datviet','/Trang-'.$page.'.datviet',$link);
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
	$reg_id	=	'/\/([^\/]+)\/\d+\/(\d+)\.datviet/ism';	
	if ($item_large	=	$html->find('div[class="categories_item_large_title"]',0)) {
		$item	=	$item_large->children(1);
		$content_link	=	$href->process_url(trim($item->href),$root);
		if (preg_match($reg_id,$content_link,$matches_id)) {
			$arr_title[]	=	strip_tags($item->innertext);
			$arr_link[]		=	$content_link;
			$arr_id[]		=	$matches_id[2];
			$arr_alias[]	=	$matches_id[1];
		}
	}
	if ($item_small = $html->find('div[class="categories_item_small_title"]')) {
		for ($i=0;$i<count($item_small); $i++)
		{
			$item	=	$item_small[$i]->children(1);
			
			$content_link	=	$href->process_url(trim($item->href),$root);
			if (preg_match($reg_id,$content_link,$matches_id)) {
				$arr_title[]	=	strip_tags($item->innertext);
				$arr_link[]		=	$content_link;
				$arr_id[]		=	$matches_id[2];
				$arr_alias[]	=	$matches_id[1];
			}
		}
	}

	$page++;
	$isNext	=	false;
	if ($items = $html->find('div[class="paging"]',0)) {
		$item	=	$items->innertext;
		$reg_page	=	'/<a[^>]*href="[^\"]*\/Trang-'.$page.'\.datviet"[^>]*class=\'pageViewPrevNext\'[^>]*>/ism';
		if (preg_match($reg_page,$item)) {
			$isNext	=	true;
		}
	}

	$obj_return	=	new stdClass();

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
function mosModelNewsBaodatvietGetBDV($id_content, $link_content, $alias_content, $title_content, $cat_alias, $secid, $catid, $path_image = 'images', $link_image,$SiteID = 'plxh285')
{
	global $arrErr;
	
	if (!$content	=	mosModelNewsBaodatvietGetContent($link_content, $id_content, $title_content, $alias_content))
	{
		$message_ban	=	$arrErr[count($arrErr)-1];
		$message	=	'#389 models newsphapluat mosModelNewsPhapluatGetPLHCM. Not get content.'.$id_content;
		array_push($arrErr,$message);
		mosBanidStore('baodatviet.vn','',$id_content,1,$message_ban."\r\n".$message);
		return false;
	}

//	$content->intro		=	mosModelNewsBaodatvietProcessOther($content->intro,$SiteID, $id_content);
	$content->fulltext	=	mosModelNewsBaodatvietProcessOther($content->fulltext,$SiteID, $id_content);
	
	$content->secid	=	$secid;
	$content->catid	=	$catid;
	$content->CatName	=	$cat_alias;

	$root	=	'http://baodatviet.vn/';
	$arr_Images	=	array();
	mosGetImages($content, $root, $arr_Images, $path_image, $link_image);
	
	$content->arr_image		=	$arr_Images;
	$content->id_content	=	$id_content;	
	
	if (!mosModelNewsBaodatvietSave($content, $SiteID)) {
		$message	=	'#521 models news mosModelNewsPhapluatxahoiGetPLXH. Not save content.'.$id_content;
		array_push($arrErr,$message);
		return false;
	}
}

function mosModelNewsBaodatvietGetContent($link,$id_content, $title_content, $alias_content)
{
	global $arrErr,$database;

	$db	=	$database;
	$browser	=	new phpWebHacks();
	$response	=	$browser->get($link);
	$html	=	loadHtmlString($response);

	if (!$detail_content = $html->find('td[class="td_content td_border_right"]',0)) {
		$message	=	'#333 models news mosModelNewsPhapluatGetContent. Invalid get detail-cont for '.$link;
		array_push($arrErr,$message);
		return false;
	}	
	if (!$detail_content->find('div[class="detail_sapo"]',0)) {
		$message	=	'#453 models news mosModelNewsPhapluatGetContent. Invalid get introtext for '.$link;
		array_push($arrErr,$message);
		return false;
	}

	$intro	=	$detail_content->find('div[class="detail_sapo"]',0);
	@$intro->find('a[class="dvmark"]',0)->outertext	=	'';
	$intro	=	$intro->innertext;	
	$intro	=	strip_tags($intro);
	
	$full_text	=	$detail_content->find('div[id="VietAd"]',0)->innertext;	

	// get time
	if (!$item = $html->find('span[id="DetailtBody1_lbDate"]',0)) {
		$message	=	'#533 models news mosModelNewsPhapluatGetContent. Invalid get date time for '.$link;
		array_push($arrErr,$message);
		return false;
	}
	$date_time	=	strip_tags($item->innertext);

//	9:32 AM, 15/07/2011
	$reg_time	=	'/(\d+):(\d+)[^\d]*(\d+)\/(\d+)\/(\d+)/ism';
	if (!preg_match($reg_time,$date_time,$matches_time)) {
		$message	=	'#733 models news mosModelNewsPhapluatGetContent. Invalid get date time for '.$link;
		array_push($arrErr,$message);
		return false;
	}
	$content_date		=	trim($matches_time[5]).'-'.trim($matches_time[4]).'-'.trim($matches_time[3]).' '.$matches_time[1].':'.$matches_time[2];			
	$href		=	new href();	
	$obj_content			=	new stdClass();
	$obj_content->title		=	trim(str_replace("\r\n",' ',$title_content));
	$obj_content->intro		=	mostidy_clean(trim(str_replace("\r\n",' ',$intro)));
	$obj_content->fulltext	=	mostidy_clean(trim(str_replace("\r\n",' ',$full_text)));
	$obj_content->link		=	$link;	
	$obj_content->alias		=	$alias_content;
	$obj_content->content_date		=	$content_date;
	$obj_content->PageHTML	=	$response;
	return $obj_content;
}

function mosModelNewsBaodatvietProcessOther($str_in, $SiteID,$id_original)
{	
	global $database,$error;
	$db	=	$database;
	$reg_link_other = '/<a.*?href=["\' ]*([^"\' ]*)["\' ]*.*?>(.*?)<\/a>/ism';
	$reg_id_other = '/\/(\d+)\.datviet/ism';
	$href	=	new href();
	$root	=	'http://baodatviet.vn';
	
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

		if ($id_result	=	mosModelNewsBaodatvietSaveOther($SiteID, $id_original, $matches[1],$matches_link[0][$i], $link)) {
			$title		=	strip_tags($matches_link[2][$i]);
			$link_content = '<a title=\''.str_replace(array('&gt;','\''),array(' ','"'),$title).'\' href="/index.php?option=com_content&task=view&id=' . $id_result.'" >'.$title."</a>";
			$str_in		=	str_replace($matches_link[0][$i],$link_content,$str_in);
		}		
	}
	return $str_in;
}

function mosModelNewsBaodatvietSaveOther ($SiteID,$id_original,$id_original_other,$str_replace,$link_other)
{
	global $database,$error;
	
	$db	=	& $database;
	$query = "SELECT * FROM #__article2010_new_baodatviet WHERE id_original = ".trim($id_original_other);
	$db->setQuery($query);	
	$id_result	=	false;
	$arr_geted	=	mosGetDBOBJ('#__article2010_totalcontent',array('aid'),'id_origional  ='.$id_original_other.' AND SiteID='.$db->quote('bdv290'));		
	
	$state = 0;
	if (count($arr_geted)>0) {		
		 $id_result	=	$arr_geted[0];
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

function mosModelNewsBaodatvietSave($content, $SiteID = 'bdv290')
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
	$row->SiteName		=	'baodatviet';	
	$row->Domain		=	'baodatviet.vn';	
	$row->SourceURL		=	$content->link;
	$row->created		= 	date("Y-m-d H:i:s",strtotime($content->content_date));	
	$row->title			=	$content->title;
	$row->title_alias	=	$content->alias;
	$row->introtext		=	$content->intro;
	$row->fulltext		=	$content->fulltext;
	$row->sectionid		=	$content->secid;
	$row->catid			=	$content->catid;
	$row->CatName		=	$content->CatName;
	$row->PageHTML 		=	$content->PageHTML;
			
	$fmtsql = "INSERT INTO `#__article2010_new_baodatviet` SET %s ON DUPLICATE KEY UPDATE  %s  ";
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
	mosModelNewsBaodatvietSaveMedia($content->arr_image,$id,$SiteID);
	
	$obj	=	new stdClass();
	$obj->SiteID	=	$SiteID;
	$obj->aid	=	$id;
	$obj->id_origional	=	$row->id_original;	
	mosStoreOBJ('#__article2010_totalcontent',$obj);
	
	mosUpdateOther($content->id_content,$content->title,$id,'#__article2010_new_baodatviet');
	return true;
}

function mosModelNewsBaodatvietSaveMedia($arr_media,$contenid, $SiteID = 'bdv290')
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
			$message	=	'#562 models news mosModelNewsBaodatvietSaveMedia. Invalid store media for '.$media->Path.'. Link: '.$media->media_url.' sql error: '.$row->getError ();
			array_push($arrErr,$message);
		}
	}
	return true;
}