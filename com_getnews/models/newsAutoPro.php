<?php


function mosModelAutoProGetNews($get_existing = true)
{	
	
	global $arrErr,$database, $mosConfig_live_site;
	$db = $database;
	$arr_obj 	=	mosModelAutoProGetCat();	
	
	$obj_cat	=	$arr_obj[0];
	
	$param	=	$obj_cat->lastGet_param;	
	preg_match('/getold=([^;]*);page=([^;]*);/ism',$param,$matches_param);
	
	$getold		=	1;	$page	=	0;	
	if(isset($matches_param[1]))
		$getold	=	$matches_param[1];
	
	if(isset($matches_param[2]) and $matches_param[2])
		$page	=	$matches_param[2];
		
	$page		=	intval($page)	+	1;
	if($getold == 0 and $page >2)
		$page	=	1;
		
	if ($obj_cat->alias_origional == 'video') {
//		$data_content = mosModelAutoProGetListContentVideo($obj_cat->alias_origional, $page);
		return false;
	}
	else $data_content = mosModelAutoProGetListContent($obj_cat->alias_origional, $page);
		
	$arr_ID		=	$data_content->arrID;
	$arr_alias		=	$data_content->arrAlias;
	
	$arr_result	=	array();
	if ($get_existing==false and is_array($arr_ID) and count($arr_ID)>0) {
		$db = $database;
		$_id	=	implode(',',$arr_ID);
		
		$query = "SELECT id_original 
					FROM #__article2010_new_autopro
					WHERE id_original in($_id)";
		$db->setQuery($query);
		$arr_result	=	$db->loadResultArray();
		
		$arr_ban	=	mosBanidGet('autopro.com.vn', array('id_origional'), "id_origional  in($_id)");	
		
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
	
	for ($i = 0; $i < count($arr_ID) ; $i++)
	{
		
		if ($get_existing==false && $arr_result && in_array($arr_ID[$i],$arr_result))
		{
			continue;
		}
		
		$id_content	=	$arr_ID[$i];
		$link		=	$mosConfig_live_site."/index.php?option=$option&task=autopro.vn&conten_id=".$arr_ID[$i]."&conten_alias=".$arr_alias[$i];

		$web 		=	parse_url($link);
		$begin		=	md5('BEGIN_GET_CONTENT_TN');
		$end		=	md5('END_GET_CONTENT_TN');
		 
		$postdata	=	$web['query'];
		$postdata	.=	'&begin_get_content='.$begin;
		$postdata	.=	'&end_get_content='.$end;
		$postdata	.=	'&cat_alias='.$obj_cat->alias_origional;
		
			
		$fp = @fsockopen($web['host'], 80, $errnum, $errstr, 30);
		$info = '';
        if (!$fp)	echo $errnum.': '.$errstr;
        else {
                fputs($fp, "POST ".$web['path']." HTTP/1.1\r\n");
                fputs($fp, "Host: ".$web['host']."\r\n");
                fputs($fp, "Content-type: application/x-www-form-urlencoded\r\n");
                fputs($fp, "Content-length: ".strlen($postdata)."\r\n");
                fputs($fp, "Connection: close\r\n\r\n");
                fputs($fp, $postdata . "\r\n\r\n");    

                while(!feof($fp)) {
                        $info .= @fgets($fp, 1024);
                }
                fclose($fp);                   
        }       
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
	
	if ($data_content->isNext == false) {
		$query	=	'UPDATE `#__article2010_category_autopro` 
					SET `last_run` = '.$db->quote(date ( 'Y-m-d H:i:s' )).', 
					`lastGet_param` = '.$db->quote("getold=0;page=1;").'
					WHERE `id` ='. $obj_cat->id;
		$arr_obj[0]->isNext	=	false;
		}else {
			// con nh trang
			$query	=	'UPDATE `#__article2010_category_autopro` 
					SET `last_run` = '.$db->quote(date ( 'Y-m-d H:i:s' )).', 
						`lastGet_param` = '.$db->quote("getold=$getold;page=$page;").'
					WHERE `id` ='. $obj_cat->id;
			$arr_obj[0]->isNext	=	true;		
	}
	
	$db->setQuery($query);	
	$db->query();
//	

	$arr_obj[0]->number_getcontent	=	$number_getContent;


	return $arr_obj;
}


// Lấy category từ cơ sở dữ liệu
function mosModelAutoProGetCat()
{
	global $database;
	$db	=	& $database;
	if (isset($_REQUEST['catid_origional'])) {
		$id_origional	=	$_REQUEST['catid_origional'];
		$query = "SELECT *
			FROM `#__article2010_category_autopro`
			WHERE publish = 1 and id_origional = $id_origional
			ORDER BY `last_run`
			LIMIT 0,2";	
		$db->setQuery($query);
		$obj	=	$db->loadObjectList();
		$obj[1]	=	$obj;
	}else {
		$query = "SELECT *
			FROM `#__article2010_category_autopro`
			WHERE publish = 1
			ORDER BY `last_run`
			LIMIT 0,2";	
		$db->setQuery($query);
		$obj	=	$db->loadObjectList();
	}
	
	return $obj;
}

// Get List content normal
function mosModelAutoProGetListContent($cat_alias, $page =1)
{
	
	global $arrErr,$database, $mosConfig_live_site;

	$url		=	'http://autopro.com.vn/'.$cat_alias."/trang-$page.chn";

	$browser	=	new phpWebHacks();
	$response	=	$browser->get($url);
	
	$reg_link_to_article = '/<div class="clearfix">.*?<h3>\s*<a[^>]*>.*?<\/a>\s*<a href="\/([^"]*)\/([^"]*).chn"[^>]*>([^"]*)<\/a>\s*<\/h3>/ism';
	
	if (!preg_match_all($reg_link_to_article,$response,$matches)) {
		$message	=	'#341 models news mosModelAutoProGetListContent. Invalid get news content. Cat_parent id:'.$cat_parent;
		array_push($arrErr,$message);
	}

	$obj_return	=	new stdClass();
	
	$obj_return->arrID		=	$matches[1];
	$obj_return->arrAlias	=	$matches[2];

	// get next page
	$page	=	$page + 1;

	$reg_next_page	=	'/<div class="paging">.*?<a[^>]+>'.$page.'<\/a>/ism';
	
	if (preg_match($reg_next_page,$response)) {
		$obj_return->isNext	=	true;
		$obj_return->page	=	$page;
	}else {
		$obj_return->isNext	=	false;		
	}

	return $obj_return;
}


// Get list content video
function mosModelAutoProGetListContentVideo($cat_alias, $page =1)
{
	
	global $arrErr,$database, $mosConfig_live_site;

	$url		=	'http://autopro.com.vn/'.$cat_alias.'.chn';

	$browser	=	new phpWebHacks();
	$response	=	$browser->get($url);

	$reg_group_content = '/<h2 class="fon1 mt1 ml2">Video mới nhất<\/h2>(.*?)<div id="ctl00_clpVideo_Video1_UcVideoChonLoc1_trVideoChonLoc"[^>]*>/ism';
	
	preg_match($reg_group_content,$response,$matches_group);
	$group_content = $matches_group[1];
	
	$reg_link_to_article = '/<h3>\s*<a[^>]*href="\/video\/([^"]*)\/[^"]*\/([^"]*).chn"[^>]*>([^"]*)<\/a>\s*<\/h3>/ism';
	
	if (!preg_match_all($reg_link_to_article,$group_content,$matches)) {
		$message	=	'#341 models news mosModelAutoProGetListContent. Invalid get news content. Cat_parent id:'.$cat_alias;
		array_push($arrErr,$message);
	}

	$obj_return	=	new stdClass();
	
	$obj_return->arrID		=	$matches[1];
	$obj_return->arrAlias	=	$matches[2];

	// get next page
	$page	=	$page + 1;

	$reg_next_page	=	'/<div class="paging">.*?<a[^>]+>'.$page.'<\/a>/ism';
	
	if (preg_match($reg_next_page,$response)) {
		$obj_return->isNext	=	true;
		$obj_return->page	=	$page;
	}else {
		$obj_return->isNext	=	false;		
	}

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
function mosModelAutoProGetATP($id_content, $alias_content ,$alias_cat , $section_id = '1', $catid =1 , $path_image , $link_image , $SiteID )
{
	global $arrErr; 
	$link_content	=	'http://autopro.com.vn/'.$id_content.'/'.$alias_content.'.chn';
	if (!$content	=	mosModelAutoProGetContent($link_content,$id_content,$alias_content,$SiteID,$alias_cat))
	{
		$message_ban	=	$arrErr[count($arrErr)-1];		
		$message	=	'#389 models newsEnvov mosModelAutoProGetVOV.<b> Not get content</b>.'.$id_content;
		array_push($arrErr,$message);
		mosBanidStore('autopro.com.vn','',$id_content, 1, $message_ban."\r\n".$message);
		return false;
	}
	
	$root	=	'http://autopro.com.vn';
	$arr_Images	=	array();
	
	mosGetImages($content,$root, $arr_Images,$path_image, $link_image);
	
	$content->arr_image		=	$arr_Images;
	$content->id_content	=	$id_content;
	if (!mosModelAutoProSave($content,$section_id , $catid, $SiteID)) {
		$message	=	'#391 models newsEnvov mosModelNewsGetVOV. <b>Not save content</b>.'.$id_content;
		array_push($arrErr,$message);
		return false;
	}
}

function mosModelAutoProGetContent($link, $id_content, $alias_content, $SiteID, $alias_cat)
{
	global $arrErr;
	$browser	=	new phpWebHacks();
	$source_content	=	$browser->get($link);
	$html	=	loadHtmlString($source_content);

	$obj_response_content 	= $html->find('div[class="w550 mauto"]',0);
	$response_content 		= $obj_response_content->innertext;
	
	$obj_response_title 	= $obj_response_content->find('h1[class="fon7"]',0);
	$title			 		= $obj_response_title->innertext;
	
	$obj_writer 				= $obj_response_content->find('div[class="box10 mt2"]',0);
	
	$obj_response_cat 		= $obj_response_content->find('h2[class="fon1"]',0);
	
	$obj_response_date 		= $obj_response_content->find('span[class="fr fon8"]',0);
	$date			 		= $obj_response_date->innertext;


// Thứ sáu, 13/5/2011, 14:31(GMT+7)
	$date_time	=	explode(',',$date);
	$arr_date	= 	trim($date_time[1]);
		
	$date		=	explode('/',$arr_date);
	$date		=	$date[2].'-'.$date[1].'-'.$date[0];
	
	$time		=	trim($date_time[2]);
	$time 		= 	str_replace('(GMT+7)','',$time);

	$content_date	=	$date.' '.trim($time).':00';
	
	$obj_response_content 	= str_replace($obj_response_title,'',$obj_response_content);
	$obj_response_content 	= str_replace($obj_response_cat,'',$obj_response_content);	
	$obj_response_content 	= str_replace($obj_writer,'',$obj_response_content);	
	
// get image full not in block
	$reg_image	=	'/<img[^>]*src=\'([^\']*)\'[^>]*(\/>|>)/ism';
	if (preg_match($reg_image,$obj_response_content,$matches)) {
		$link_image		=	$matches[1];
		$tag_images			=	'<img src="'.$link_image.'" title="'.$title.'" alt="'.$title.'" style="float:right;" />';
		$obj_response_content 	= str_replace($matches[0],'',$obj_response_content);
	}

// get intro, fulltext
	$obj_cuttext	=	new AutoCutText($obj_response_content,20);
	
	$intro			=	strip_tags($obj_cuttext->getIntro());
	
	
	
// Fulltext
	$full_text		=	$obj_cuttext->getFulltext();

// Lấy các bài liên quan
	$list_other_article = $html->find('ul[id="ctl16_ulTinLienQuan"]',0);
	if ($list_other_article) {
		$arr_other_article = $list_other_article->innertext;
		$full_text = $arr_other_article. $full_text;
	
		// Tìm và thay đổi tin liên quan	
		$full_text	=	mosModelAutoProProcessOther($full_text, $SiteID, $id_content);
	}
	

// Get comment http://autopro.com.vn/newscomments/0/2011050810040738.chn
	$obj_comment 	= $html->find('div[id="divShowComment"]',0);
	if($obj_comment)
	{
		$id_commeny = explode('ca',$id_content);
		$arr_comment = mosModelAutoProGetComment($id_commeny[0]);
	}
	

	$obj_content			=	new stdClass();
	
	$obj_content->title		=	trim(str_replace("\r\n",' ',$title));
	$obj_content->intro		=	mostidy_clean(trim(str_replace("\r\n",' ',$intro)));
	$obj_content->fulltext	=	mostidy_clean(trim(str_replace("\r\n",' ',$full_text)));
	$obj_content->cat_title	=	$alias_cat;
	$obj_content->link		=	$link;
	$obj_content->alias		=	$alias_content;
	$obj_content->content_date		=	$content_date;
	$obj_content->PageHTML	=	$source_content;
	$obj_content->comment	=	$arr_comment;
	$obj_content->id_original = $matches_id[1];

	return $obj_content;
}

// Lấy comment
function mosModelAutoProGetComment ($id_content)
{
	global $arrErr;
	
// http://autopro.com.vn/newscomments/0/2011050810040738.chn

	$link = 'http://autopro.com.vn/newscomments/1/'.$id_content.'.chn';

	$html = loadHtml($link);

	$list_comment = $html->find('li');
	
	$arr_comment = array();
	for($i=0;$i<count($list_comment);$i++)
	{
		$obj_comment		= 	new stdClass();
		$obj_comment_item 	= 	$list_comment[$i];
		
		$user 				= 	$obj_comment_item->find('span[class="userName"]',0);
		$obj_comment->name 	= 	trim($user->innertext);
		
		$obj_date 			= 	$obj_comment_item->find('span[class="timeStamp"]',0);
		$date 				= 	trim($obj_date->innertext);
		
		$arr_date 			= 	explode(' ',trim($date));
		
		$date				=	explode('/',$arr_date[0]);
		$date				=	$date[2].'-'.$date[0].'-'.$date[1];
		
		$time				=	trim($arr_date[1]);
		$arr_time 			= 	explode(':',trim($time));
		
		if($arr_date[2] == 'PM')
		{
			$h =  $arr_time[0] + 12;
			$m =  $arr_time[1];
			$s =  $arr_time[2];
			$str_time = $h.':'.$m.':'.$s;
		}
		else $str_time = $time;
		
		$obj_comment->datetime	=	$date.' '.$str_time;
		$content_comment 		= 	str_replace($user,'',$obj_comment_item);
		$content_comment 		= 	str_replace($obj_date,'',$content_comment);
		
		$obj_comment->comment 	= 	trim($content_comment);
		
		$arr_comment[] = $obj_comment;
	}
	return $arr_comment;
}

// lấy thông tin link khác
function mosModelAutoProProcessOther($str_in, $SiteID,$id_original)
{
	global $database,$error;
	$db	=	$database;
	// <a href="/2011042602394462ca3945/ban-co-kha-nang-hay-tham-gia-greena-team.chn" title="Bạn c&#243; khả năng? H&#227;y tham gia Green-A Team">Bạn c&#243; khả năng? H&#227;y tham gia Green-A Team</a>
	$reg_link_other = '/<a[^>]*href="(\/([^"]*)\/[^"]+.chn)"[^>]*>(.*?)<\/a>/ism';
	$href	=	new href();
	$root	=	'http://autopro.com.vn';
	
	if (!preg_match_all($reg_link_other,$str_in,$matches_link)) {
		return $str_in;
	}

	for ($i=0; $i< count($matches_link[1]); $i++)
	{		
		$link	=	$href->process_url($matches_link[1][$i], $root); 

		if (!preg_match($reg_id_other, $link,$matches)) {
			$title		=	strip_tags($matches_link[2][$i]);
			$str_in		=	str_replace($matches_link[0][$i],$title,$str_in);
			continue;
		}
				
		$id_orgional_other	=	$matches_link[2][$i];		

		$title	=	strip_tags($matches_link[3][$i]);

		if ($id_result	=	mosModelAutoProSaveOther($SiteID, $id_original, $id_orgional_other,$matches_link[0][$i], $link)) {
			$link_content = '<a title="'.str_replace('&gt;','',$title).'" href="/index.php?option=com_content&task=view&id=' . $id_result.'" >'.$title."</a>";
			$str_in		=	str_replace($matches_link[0][$i],$link_content,$str_in);
		}	
	}
	return $str_in;
}

function mosModelAutoProSaveOther ($SiteID,$id_original,$id_original_other,$str_replace,$link_other)
{
	global $database,$error;
	
	$db	=	& $database;
	$query = "SELECT id FROM #__article2010_new_autopro WHERE id_original = ".trim($id_original_other);
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
		switch ($content_type)
		{
			case 'text/html':
				$type	=	1;
				break;
			case 'image/jpeg':
				$type	=	2;
				break;
			default:
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


// Lưu nội dung bài viết
function mosModelAutoProSave($content, $section_id = 1, $catid = 1, $SiteID)
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
	$row->SiteName		=	'autopro.com.vn';	
	$row->Domain		=	'autopro.com.vn';	
	$row->SourceURL		=	$content->link;
	$row->created		= 	date("Y-m-d H:i:s",strtotime($content->content_date));	
	$row->title			=	$content->title;
	$row->title_alias	=	$content->alias;
	$row->introtext		=	$content->intro;
	$row->fulltext		=	$content->fulltext;
	$row->sectionid		=	$section_id;
	$row->catid			=	$catid;
	$row->CatName		=	$content->cat_title;
	$row->catid_original=	$content->catid_original;
	$row->PageHTML 		=	$content->PageHTML;

	$fmtsql = "INSERT INTO `#__article2010_new_autopro` SET %s ON DUPLICATE KEY UPDATE  %s  ";
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
	mosModelAutoProSaveMedia($content->arr_image,$id,$SiteID);
	mosModelAutoProSaveParam($content,$id,$SiteID);
	mosUpdateOther($content->id_content,$content->title,$id,'#__article2010_new_autopro');
	return true;
}
function mosModelAutoProSaveMedia($arr_media,$contenid, $SiteID)
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
			$message	=	'#562 models newsEnvov mosModelAutoProSaveMedia.<b> Invalid store media</b> for '.$media->Path.'. Link: '.$media->media_url.' sql error: '.$row->getError ();
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
function mosModelAutoProSaveParam($content,$contenid, $SiteID)
{
	global $arrErr,$database;
	
	// store comment	
	for ($i = 0; $i<count($content->comment); $i++)
	{
		// $aid, $domain = "", $name,$datetime,$comment, $param = ''
		
		mosCommentStore($contenid,'autopro.com.vn',$content->comment[$i]->name, $content->comment[$i]->datetime, $content->comment[$i]->comment, $param = '');
	}
	
//	for ($j = 0; $j<count($content->videos); $j++)
//	{
//		
//		mosVideosStore($contenid,$content->id_original,$content->alias.'.'.$content->video_ext[$j],'autopro.com.vn','http://autopro.com.vn',$content->videos[$j],$state='1',$public, $param = '');
//	}	
}