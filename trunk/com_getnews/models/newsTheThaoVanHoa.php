<?php


function mosModelTheThaoVanHoaGetNews($get_existing = true)
{	
	
	global $arrErr,$database, $mosConfig_live_site;
	$db = $database;
	$arr_obj 	=	mosModelTheThaoVanHoaGetCat();	
	
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
		
	$data_content = mosModelTheThaoVanHoaGetListContent($obj_cat->parent, $obj_cat->id_origional, $page);
		
	$arr_ID		=	$data_content->arrID;
	$arr_alias		=	$data_content->arrAlias;	
	
	$arr_result	=	array(0);
	if ($get_existing==false and is_array($arr_ID) and count($arr_ID)>0) {
		$db = $database;
		$_id	=	implode(',',$arr_ID);
		$query = "SELECT id_original 
					FROM #__article2010_new_thethaovanhoa
					WHERE id_original in($_id)";
		$db->setQuery($query);
		$arr_result	=	$db->loadResultArray();
		
		$arr_ban	=	mosBanidGet('thethaovanhoa.vn',array('id_origional'),"id_origional  in($_id)");	
		
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
		if ($get_existing==false && in_array($arr_ID[$i],$arr_result) && count($arr_result) > 0)
		{
			continue;
		}
		
		$id_content	=	$arr_ID[$i];
		$link		=	$mosConfig_live_site."/index.php?option=$option&task=thethaovanhoa.vn&conten_id=".$arr_ID[$i]."&conten_alias=".$arr_alias[$i];
		
		$web 		=	parse_url($link);
		$begin		=	md5('BEGIN_GET_CONTENT_TN');
		$end		=	md5('END_GET_CONTENT_TN');
		 
		$postdata	=	$web['query'];
		$postdata	.=	'&begin_get_content='.$begin;
		$postdata	.=	'&end_get_content='.$end;
		$postdata	.=	'&cat_parent='.$obj_cat->parent;
		$postdata	.=	'&cat_id='.$obj_cat->id_origional;

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
		$param	=	"getold=0;page=1;";		
		$arr_obj[0]->isNext	=	false;
	}else {
			// con nh trang
			$param	=	"getold=$getold;page=$page;";			
			$arr_obj[0]->isNext	=	true;		
	}
	$query	=	'UPDATE `#__article2010_category_thethaovanhoa` 
					SET `last_run` = '.$db->quote(date ( 'Y-m-d H:i:s' )).', 
						`lastGet_param` = '.$db->quote($param).'
					WHERE `id` ='. $obj_cat->id;
	$db->setQuery($query);
//	echo $db->getQuery();
	$db->query();

	$arr_obj[0]->number_getcontent	=	$number_getContent;	
	return $arr_obj;
}

// Lấy category từ cơ sở dữ liệu
function mosModelTheThaoVanHoaGetCat()
{
	global $database;
	$db	=	& $database;
	if (isset($_REQUEST['catid_origional'])) {
		$id_origional	=	$_REQUEST['catid_origional'];
		$query = "SELECT *
			FROM `#__article2010_category_thethaovanhoa`
			WHERE publish = 1 and `parent` != 0  and id_origional = $id_origional
			ORDER BY `last_run`
			LIMIT 0,2";	
		$db->setQuery($query);
		$obj	=	$db->loadObjectList();
		$obj[1]	=	$obj;
	}else {
		$query = "SELECT *
			FROM `#__article2010_category_thethaovanhoa`
			WHERE publish = 1 and `parent` != 0
			ORDER BY `last_run`
			LIMIT 0,2";	
		$db->setQuery($query);
		$obj	=	$db->loadObjectList();
	}
	
	return $obj;
}


function mosModelTheThaoVanHoaGetListContent($cat_parent = '128', $cat_use_to_get = '144', $page =1)
{
	
	global $arrErr,$database, $mosConfig_live_site;

	$url		=	'http://thethaovanhoa.vn/'.$cat_use_to_get.'CT'.$cat_parent.'/ttvh/trang-'.$page.'.htm';

	$browser	=	new phpWebHacks();
	$response	=	$browser->get($url);
	//// 473N20110517155928124T472
	$reg_link_to_article = '/<(h1|div) class="title">\s*<a href="\/\d+N(\d+)T\d+\/([^"]+).htm">.*?<\/a>/ism';
	
	if (!preg_match_all($reg_link_to_article,$response,$matches)) {
		$message	=	'#341 models news mosModelTheThaoVanHoaGetListContent. Invalid get news content. Cat_parent id:'.$cat_parent;
		array_push($arrErr,$message);
	}

	$obj_return	=	new stdClass();

	$obj_return->arrID		=	$matches[2];
	$obj_return->arrAlias	=	$matches[3];

	// get next page	
	$reg_next_page	=	'/<div class="paging">.*?<a[^>]+href="[^"]+'.$page.'.htm">'.$page.'<\/a>/ism';
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
function mosModelTheThaoVanHoaGetTTVH($cat_parent,$cat_id,$id_content = '20110516131646213', $alias_content = 'danh-bai-cagliari-milan-mo-tiec-o-san-siro', $section_id = '1', $catid =1 , $path_image = 'images', $link_image = '/images/ttvh135', $SiteID = 'ttvh135')
{
	global $arrErr; 
	$id_get = $cat_id.'N'.$id_content.'T'.$cat_parent;
	$link_content	=	'http://thethaovanhoa.vn/'.$id_get.'/'.$alias_content.'.htm';
	if (!$content	=	mosModelTheThaoVanHoaGetContent($link_content,$id_content,$alias_content,$SiteID))
	{
		$message_ban	=	$arrErr[count($arrErr)-1];		
		$message	=	'#389 models newsEnvov mosModelTheThaoVanHoaGetVOV.<b> Not get content</b>.'.$id_content;
		array_push($arrErr,$message);
		mosBanidStore('thethaovanhoa.vn','',$id_content,1,$message_ban."\r\n".$message);
		return false;
	}
	
	$root	=	'http://thethaovanhoa.vn';
	$arr_Images	=	array();
	
	mosGetImages($content,$root, $arr_Images,$path_image, $link_image);
	
	$content->arr_image		=	$arr_Images;
	$content->id_content	=	$id_content;
	if (!mosModelTheThaoVanHoaSave($content,$section_id , $catid, $SiteID)) {
		$message	=	'#391 models newsEnvov mosModelNewsGetVOV. <b>Not save content</b>.'.$id_content;
		array_push($arrErr,$message);
		return false;
	}
}

function mosModelTheThaoVanHoaGetContent($link, $id_content, $alias_content, $SiteID = 'ttvh135')
{
	global $arrErr;
	
	$browser	=	new phpWebHacks();
	$source_content	=	$browser->get($link);
	$html	=	loadHtmlString($source_content);

	$obj_response_content 	= $html->find('div[id="divDetail"]',0);
	$response_content 		= $obj_response_content->innertext;
	
	$obj_response_title 	= $html->find('div[class="title"]',0);
	$response_title 		= $obj_response_title->innertext;
	
	$html_response_content = loadHtmlString($response_content);
	
// get title
	$reg_title	=	'/<h1>\s*<a[^>]*>(.*?)<\/a>/ism';
	if (!preg_match($reg_title,$response_title,$matches)) {
		$message	=	'#332 models newsEnvov mosModelTheThaoVanHoaGetContent. <b>Invalid get title</b> for '.$link;
		array_push($arrErr,$message);
		return false;
	}
	$title	=	strip_tags($matches[1]);

// get intro, fulltext
	$obj_cuttext	=	new AutoCutText($response_content,20);
	
	$intro			=	strip_tags($obj_cuttext->getIntro());
	$intro 			=	trim(str_replace('(TT&amp;VH) -','',$intro));
	$intro 			=	trim(str_replace('(TT&amp;VH/TTXVN) -','',$intro));
	
	$full_text		=	$obj_cuttext->getFulltext();

// Tìm video
	$reg_video = '/<\w* id="FlashPlayer\d+"[^>]+>/ism';
	if (preg_match($reg_video,$response_content,$matches_video)) {
		$reg_file_video = '/"([^"]+\.(flv|mp4|3gp))"/ism';
		preg_match_all($reg_file_video,$response_content,$matches_file_video);
		$arr_file_video = $matches_file_video[1];
		$arr_file_video_ext = $matches_file_video[2];
		
	}
	
	
// Lấy các bài liên quan
	$list_other_article = $html->find('div[class="relation"]',0);
	$arr_other_article = $list_other_article->innertext;
	$full_text = $arr_other_article. $full_text;

// Tìm và thay đổi tin liên quan	
	$full_text	=	mosModelTheThaoVanHoaProcessOther($full_text, $SiteID, $id_content);
	
	
// get date '<div class="print"> <span class="details-subtitle">Vấn đề & Sự kiện</span>Thứ Hai, 16/05/2011 11:31 &nbsp; &nbsp; <a href="javascript:void" rel="nofollow">'
	$reg_date	=	'/<div class="print">\s*<span class="details-subtitle">.*?<\/span>(.*?)<a[^>]*/ism';
	if (!preg_match($reg_date,$source_content,$matches)) {
		$message	=	'#356 models newsEnvov mosModelTheThaoVanHoaGetContent. <b>Invalid get date</b> for '.$link;
		array_push($arrErr,$message);
	} 
	
//1Thứ Hai, 16/05/2011 11:31
	$date_time	=	explode(',',$matches[1]);
	$date_time = trim($date_time[1]);
	$arr_date = explode(' ',$date_time);
	
	$date		=	explode('/',$arr_date[0]);
	$date		=	$date[2].'-'.$date[1].'-'.$date[0];
	$date 		= 	str_replace('&nbsp;','',$date);
	
	$time		=	trim($arr_date[1]);
	$time 		= 	str_replace('&nbsp;','',$time);
	$content_date	=	$date.' '.trim($time).':00';
		
// get image full not in block
	$reg_image	=	'/<img[^>]*src="([^"]*)"[^>]*>/ism';
	if (preg_match($reg_image,$response_content,$matches)) {
		$link_image		=	$matches[1];
		$tag_images			=	'<img src="'.$link_image.'" title="'.$title.'" alt="'.$title.'" style="float:right;" />';
		$intro	=	$tag_images. $intro;
	}
	
// Get comment 473N20110517155928124T472
	$arr_comment = mosModelTheThaoVanHoaGetComment($id_content);

	$obj_content			=	new stdClass();
	
	$obj_content->title		=	trim(str_replace("\r\n",' ',$title));
	$obj_content->intro		=	mostidy_clean(trim(str_replace("\r\n",' ',$intro)));
	$obj_content->fulltext	=	mostidy_clean(trim(str_replace("\r\n",' ',$full_text)));
	$obj_content->cat_title	=	trim(str_replace("\r\n",' ',$cat_title));
	$obj_content->link		=	$link;
	$obj_content->alias		=	$alias_content;
	$obj_content->content_date		=	$content_date;
	$obj_content->PageHTML	=	$source_content;
	$obj_content->comment	=	$arr_comment;
	$obj_content->videos 	= 	$arr_file_video;
	$obj_content->video_ext	= 	$arr_file_video_ext;
	$obj_content->id_original = $matches_id[1];
	var_dump($obj_content->video_ext);
	return $obj_content;
}

// Lấy comment
function mosModelTheThaoVanHoaGetComment ($id_content = '20110512113812227')
{
	global $arrErr;
	
	$link = 'http://thethaovanhoa.vn/Ajax/ListComment.aspx?NewsID='.$id_content.'&PageIndex=1';

	$html = loadHtml($link);

	$list_comment = $html->find('div[class="item"]');
	$arr_comment = array();
	for($i=0;$i<count($list_comment);$i++)
	{
		$obj_comment		= new stdClass();
		$obj_comment_item = $list_comment[$i];
		
		$user 				= $obj_comment_item->find('div[class="user_name"]',0);
		$obj_comment->name 	= trim(strip_tags($user->innertext));
		
		$date 				= $obj_comment_item->find('div[class="date_time"]',0);
		$date 				= str_replace('&nbsp;','',$date->innertext);
		
		$arr_date 			= explode(' ',trim($date));
		
		$date				=	explode('/',$arr_date[0]);
		
		$date				=	$date[2].'-'.$date[1].'-'.$date[0];
		$time				=	trim($arr_date[1]);
		$obj_comment->datetime	=	$date.' '.$time;
		
		$content_comment 				= $obj_comment_item->find('div[class="comment_content"]',0);
		$obj_comment->comment 	= trim($content_comment->innertext);
		$arr_comment[] = $obj_comment;
	}
	return $arr_comment;
}

// lấy thông tin link khác
function mosModelTheThaoVanHoaProcessOther($str_in, $SiteID,$id_original)
{
	global $database,$error;
	$db	=	$database;
	
	$reg_link_other = '/<a[^>]*href=["\' ]*([^"\' ]*)["\' ]*>(.*?)<\/a>/ism';
	$reg_id_other = '/http:\/\/thethaovanhoa\.vn\/\d+N(\d+)T\d+\/[^"]+.htm/ism';
	$href	=	new href();
	$root	=	'http://thethaovanhoa.vn';
	
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
		// 473N20110517155928124T472
		
		$id_orgional_other	=	$matches[1];		

		$title	=	strip_tags($matches_link[2][$i]);

		if ($id_result	=	mosModelTheThaoVanHoaSaveOther($SiteID, $id_original, $id_orgional_other,$matches_link[0][$i], $link)) {
			$link_content = '<a title="'.str_replace('&gt;','',$title).'" href="/index.php?option=com_content&task=view&id=' . $id_result.'" >'.$title."</a>";
			$str_in		=	str_replace($matches_link[0][$i],$link_content,$str_in);
		}	
	}
	return $str_in;
}

function mosModelTheThaoVanHoaSaveOther ($SiteID,$id_original,$id_original_other,$str_replace,$link_other)
{
	global $database,$error;
	
	$db	=	& $database;
	$query = "SELECT id FROM #__article2010_new_thathaovanhoa WHERE id_original = ".trim($id_original_other);
	$db->setQuery($query);
	
	$id_result	=	false;
	
	if ($db->loadObject($obj)) {
		 $state = 0;		 
		 $id_result	=	$obj->id;
		 $type	=	1;
	}else 
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


// Lưu nội dung bài viết
function mosModelTheThaoVanHoaSave($content, $section_id = 1, $catid = 1, $SiteID = 'ttvh135')
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
	$row->SiteName		=	'thethaovanhoa.vn';	
	$row->Domain		=	'thethaovanhoa.vn';	
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

	$fmtsql = "INSERT INTO `#__article2010_new_thethaovanhoa` SET %s ON DUPLICATE KEY UPDATE  %s  ";
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
	mosModelTheThaoVanHoaSaveMedia($content->arr_image,$id,$SiteID);
	mosModelTheThaoVanHoaSaveParam($content,$id,$SiteID);
	mosUpdateOther($content->id_content,$content->title,$id,'#__article2010_new_thethaovanhoa');
	return true;
}
function mosModelTheThaoVanHoaSaveMedia($arr_media,$contenid, $SiteID = 'ttvh135')
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
			$message	=	'#562 models newsEnvov mosModelTheThaoVanHoaSaveMedia.<b> Invalid store media</b> for '.$media->Path.'. Link: '.$media->media_url.' sql error: '.$row->getError ();
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
function mosModelTheThaoVanHoaSaveParam($content,$contenid, $SiteID = 'ttvh135')
{
	global $arrErr,$database;
	
	// store comment	
	for ($i = 0; $i<count($content->comment); $i++)
	{
		// $aid, $domain = "", $name,$datetime,$comment, $param = ''
		
		mosCommentStore($contenid,'thethaovanhoa.vn',$content->comment[$i]->name, $content->comment[$i]->datetime, $content->comment[$i]->comment, $param = '');
	}
	
	for ($j = 0; $j<count($content->videos); $j++)
	{
		
		mosVideosStore($contenid,$content->id_original,$content->alias.'.'.$content->video_ext[$j],'thethaovanhoa.vn','http://thethaovanhoa.vn',$content->videos[$j],$state='1',$public, $param = '');
	}	
}
