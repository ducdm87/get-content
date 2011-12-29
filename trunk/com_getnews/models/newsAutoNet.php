<?php


function mosModelNewsautoNetGetNews($get_existing = true)
{	
	
	global $arrErr,$database, $mosConfig_live_site;
	$db = $database;
	$arr_obj 	=	mosModelNewsautonetGetCat();	
	if (count($arr_obj) <2 ) {
		echo 'sucess';
		dump_data($arr_obj);
		die();
	}
	$obj_cat	=	$arr_obj[0];
	
	$param	=	$obj_cat->lastGet_param;

	preg_match('/getold=([^;]*);start=([^;]*);/ism',$param,$matches_param);
	
	$rows		=	30;
	$getold		=	1;	$start	=	0;	
	if(isset($matches_param[1]))
		$getold	=	$matches_param[1];
	
	if(isset($matches_param[2]) and $matches_param[2])
		$start	=	$matches_param[2];		
	
	if($getold == 0 and $start >=2*$rows)
		$start	=	0;
	
	$data_content = mosModelNewsautonetGetListContent($obj_cat->id_origional, $start, $rows);
	
	$start		=	intval($start)	+	$rows;
	
	$arr_ID		=	$data_content->arrID;
	
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
					FROM #__article2010_new_autonet
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
		$data_content = mosModelNewsautonetGetListContent($obj_cat->id_origional, $start, $rows);
		$start		=	intval($start)	+	$rows;
		$arr_ID		=	$data_content->arrID;
			
		if (count($arr_ID) <1 and $data_content->isNext) {
			$bool	=	1;			
		}
		else if ($get_existing==false) {
			$db = $database;
			$_id	=	implode(',',$arr_ID);
			$query = "SELECT id_original 
					FROM #__article2010_new_autonet
					WHERE id_original in($_id)";
			$db->setQuery($query);		
			$arr_result	=	$db->loadResultArray();
			if (count($arr_ID) == count($arr_result)) {
				$bool	=	1;				
			}else {
				$bool	=	0;
			}
		}
		$number_run ++;	
	}	
	
	
	if ($get_existing==false and is_array($arr_ID) and count($arr_ID)>0) {
		$db = $database;
		$_id	=	implode(',',$arr_ID);
		$query = "SELECT id_original 
					FROM #__article2010_new_autonet
					WHERE id_original in($_id)";
		$db->setQuery($query);
		
		$arr_result	=	$db->loadResultArray();
		
		$arr_ban	=	mosBanidGet('autonet.com.vn',array('id_origional'),"id_origional  in($_id)");	
		
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
		if ($get_existing==false && in_array($arr_ID[$i],$arr_result))
		{
			array_push($arrErr,'#444 '.$arr_ID[$i].' is existing');
			continue;
		}

		$id_content	=	$arr_ID[$i];
		$browser	=	new phpWebHacks();
		
		$url		=	$mosConfig_live_site."/index.php?option=$option";			
			
			$arr_post	=	array();
			$arr_post['begin_get_content']	=	md5('BEGIN_GET_CONTENT_AUTONET');
			$arr_post['end_get_content']	=	md5('END_GET_CONTENT_AUTONET');
			$arr_post['task']				=	'getnewsautonet';			
			$arr_post['content_id']			=	$arr_ID[$i];
			$arr_post['content_link']		=	$data_content->arrLink[$i];
			$arr_post['content_title']		=	$data_content->arrTitle[$i];
			$arr_post['content_date']		=	$data_content->arrDate[$i];
			$arr_post['cat_title']			=	$obj_cat->title;
			$arr_post['cat_id_original']	=	$obj_cat->id_origional;
		
			//
		echo $url;
		$a	=	array();
		foreach ($arr_post as $k=>$v) {
			$a[]	=	"$k=$v";
		}
		
		echo '&'.implode('&',$a);
		die();
				
		$response	=	$browser->post($url,$arr_post);
//		echo $response; die();
		
         if (preg_match('/' . md5('BEGIN_GET_CONTENT_AUTONET') . '(.*?)' . md5('END_GET_CONTENT_AUTONET') . '/ism', $response, $match)) 
         {
             $response=trim($match[1]);
         }
         else {
         	$message	=	'ERROR_GET_CONTENT_AUTNET| #123 API false '.$arr_ID[$i].' '.$data_content->arrLink[$i].' '.$response;
         	array_push($arrErr,$message);
            continue;
         }
      	 if (stristr($response,'ERROR_GET_CONTENT_TN')) {
  	 		$message	=	'ERROR_GET_CONTENT_TN| '.$response;
     		array_push($arrErr,$message);
            continue;
         }
          $number_getContent	=	$number_getContent + 1; 
	}
	
	if ($data_content->isNext == false) {
		$param	=	"getold=0;start=0;";
		$arr_obj[0]->isNext	=	false;
	}else {
			// con nh trang
			$param	=	"getold=$getold;start=$start;";			
			$arr_obj[0]->isNext	=	true;		
	}
//echo '<br />';
//	echo $param;
	$query	=	'UPDATE `#__article2010_category_autonet` 
					SET `last_run` = '.$db->quote(date ( 'Y-m-d H:i:s' )).', 
						`lastGet_param` = '.$db->quote($param).'						
					WHERE `id` ='. $obj_cat->id;
	$db->setQuery($query);	
	$db->query();	

	$arr_obj[0]->number_getcontent	=	$number_getContent;

	return $arr_obj;
}


// Lấy category từ cơ sở dữ liệu
function mosModelNewsautonetGetCat()
{
	global $database;
	$db	=	& $database;
	if (isset($_REQUEST['catid_origional'])) {
		$arr_obj	=	array();
		$id_origional	=	$_REQUEST['catid_origional'];
		$query = "SELECT *
			FROM `#__article2010_category_autonet`
			WHERE publish = 1 and `parent` != 0  and id_origional = $id_origional
			ORDER BY `last_run`
			LIMIT 0,1";	
		$db->setQuery($query);
		$db->loadObject($obj);
		$arr_obj[0]	=	$obj;
		$arr_obj[1]	=	$obj;
	}else {
		$query = "SELECT *
			FROM `#__article2010_category_autonet`
			WHERE publish = 1 and `parent` != 0 
			ORDER BY `last_run`
			LIMIT 0,2";	
		$db->setQuery($query);		
		$arr_obj	=	$db->loadObjectList();
	}
	
	return $arr_obj;
}

function mosModelNewsautonetGetListContent($cat_id = '128', $start, $rows= 100)
{
	
	global $arrErr,$database, $mosConfig_live_site;

	$url		=	"http://autonet.com.vn/search/select/?q=siteid:258%20AND%20cateid:$cat_id&start=$start&rows=$rows&r=&wt=xml";
//	echo $url;
	$browser	=	new phpWebHacks();
	$response	=	$browser->get($url);
	$html		=	loadHtmlString($response);
	$number		=	$html->find('result',0)->numfound;

	$arr_doc	=	$html->find('doc');
	
	$arr_id		=	array();
	$arr_link	=	array();
	$arr_title	=	array();
	$arr_date	=	array();

	for ($i=0;$i<count($arr_doc); $i++)
	{
		$arr_child	=	$arr_doc[$i]->children;
		for ($j=0; $j < count($arr_child); $j++)
		{	
			$name	=	$arr_child[$j]->name;
			switch ($name)
			{
				case 'date':
					{						
						$arr_date[]	=	trim($arr_child[$j]->innertext);
						break;
					}
				case 'id':
					{
						$arr_id[]	=	trim($arr_child[$j]->innertext);
						break;
					}
				case 'title':
					{						
						$arr_title[]	=	trim($arr_child[$j]->innertext);
						break;
					}
				case 'url':
					{
						$arr_link[]	=	trim($arr_child[$j]->innertext);
						break;
					}
			}
		}
	}
	$start	=	$start	+	$rows;
	
	$obj_return				=	new stdClass();
	$obj_return->arrID		=	$arr_id;
	$obj_return->arrLink	=	$arr_link;
	$obj_return->arrTitle	=	$arr_title;
	$obj_return->arrDate	=	$arr_date;
	$obj_return->numfound	=	$number;
	$obj_return->numfound	=	$start;	
	
	if ($start > $number) {
		$obj_return->isNext	=	0;
	}else {
		$obj_return->isNext	=	1;
	}
	echo $url;
	echo '&nbsp;&nbsp;|&nbsp;;&nbsp;';
	$next	=	$obj_return->isNext?'yes':'no';
	echo $next;
	echo '&nbsp;&nbsp;|&nbsp;;&nbsp;';
	echo count($arr_id);
	echo '<hr />';
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
function mosModelNewsautonetGetATN($cat_id_original, $cat_title,$id_content,$content_link, $content_title, $content_date, $section_id, $catid, $path_image, $link_image, $SiteID = 'atn135')
{
	global $arrErr; 	
	if (!$content	=	mosModelNewsautonetGetContent($cat_id_original, $cat_title,$content_link,$id_content,$content_title,$content_date, $SiteID))
	{
		$message_ban	=	$arrErr[count($arrErr)-1];		
		$message	=	'#389 models newsEnvov mosModelNewsautonetGetATN.<b> Not get content</b>.'.$id_content;
		array_push($arrErr,$message);
		mosBanidStore('autonet.com.vn','',$id_content, 1, $message_ban."\r\n".$message);
		return false;
	}

	$root	=	'http://autonet.com.vn';
	$arr_Images	=	array();
	
	mosGetImages($content,$root, $arr_Images,$path_image, $link_image);
	if ($_REQUEST['bug_image']) {
		dump_data($arr_Images); die();	
	}
	
	$content->arr_image		=	$arr_Images;
	$content->id_content	=	$id_content;
	if (!mosModelNewsautonetSave($content,$section_id , $catid, $SiteID)) {
		$message	=	'#391 models newsAutonet mosModelNewsautonetGetATN. <b>Not save content</b>.'.$id_content;
		array_push($arrErr,$message);
		return false;
	}
}

function mosModelNewsautonetGetContent($cat_id_original, $cat_title,$content_link,$id_content,$title,$content_date, $SiteID = 'atn135')
{	
	global $arrErr;
	$browser		=	new phpWebHacks();
	if (isset($_REQUEST['show_debug']) && $_REQUEST['show_debug'] === 1) {
		var_dump(func_get_args());
	}
	$source_content	=	$browser->get($content_link);
	
	$html			=	loadHtmlString($source_content);
	$arr_video		=	array();
	// galleryview
	if ($html->find('div[id="photos"]',0)) {
		$obj_response_content	=	$html->find('div[id="photos"]',0);
		$intro		=	'';
		$full_text	=	'<!--{type:galleryview}-->'.($obj_response_content->outertext);	
	}else if ($html->find('div[id="article"]',0)) {
		// article normal
		$obj_response_content 	= $html->find('div[id="article"]',0);
		if ($obj_response_content->find('div[class="video"]')) {			
			$video	=	$obj_response_content->find('div[class="video"]',0);
			
			$content	=	$obj_response_content->find('div[id="content"]',0);
			$content	=	$content->innertext;
			
			$content	=	preg_replace('/<img[^>]*src="[^"]*c_images372565_t\.gif"[^>]*>/ism','<!--remove image here-->',$content);
			$content	=	preg_replace('/<img[^>]*src="[^"]*autonet\.gif"[^>]*>/ism','<!--remove image here-->',$content);
			
			$content	=	$video	.	$content;
			$arr_video	=	mosModelNewsautonetProcessVideo($content, $SiteID);
			
			$arr_codes	=	array();
			$next = 0;			
			if(preg_match_all('/<p[^>]*id="video_vb[^"]*"[^>]*>.*?<\/p>/ism', $content, $matches)){
				for ($i = 0; $i < count($matches[0]); $i++){
					$content = str_replace($matches[0][$i], 'DUC_CODE_'.$next.'_CODE_DUC', $content);
					array_push($arr_codes, $matches[0][$i]);
					$next++; 
				}
			}
			
			$obj_cuttext	=	new AutoCutText($content,10);			
			$intro			=	mostidy_clean($obj_cuttext->getIntro());
			$full_text		=	'<!--{type:video}-->'.mostidy_clean($obj_cuttext->getFulltext());
			
			$content	=	$intro.'_SPERATOR_AUTONET_'.$full_text;
			for ($i = 0; $i < count($arr_codes); $i++){
				$content = str_replace('DUC_CODE_'.$i.'_CODE_DUC', $arr_codes[$i], $content);
			}			
			$content	=	explode('_SPERATOR_AUTONET_',$content);
			$intro	=	$content[0]; 
			$full_text	=	$content[1];			
			
		}else {
			$avatar	=	'';
			if ($obj_response_content->find('div[id="avatar"]',0)) {
				$avatar		=	$obj_response_content->find('div[id="avatar"]',0)->children[0];
				$avatar		=	$avatar->outertext;
			}
			
			$content	=	$obj_response_content->find('div[id="content"]',0);
			$content	=	$avatar.' '.$content->innertext;
			
			$content	=	preg_replace('/<img[^>]*src="[^"]*c_images372565_t\.gif"[^>]*>/ism','<!--remove image here-->',$content);
			$content	=	preg_replace('/<img[^>]*src="[^"]*autonet\.gif"[^>]*>/ism','<!--remove image here-->',$content);
		// get intro, fulltext
			$obj_cuttext	=	new AutoCutText($content,10);
					
			$intro			=	$obj_cuttext->getIntro();	
			$full_text		=	$obj_cuttext->getFulltext();	
		
		// Tìm và thay đổi tin liên quan	
			$intro		=	mostidy_clean(mosModelNewsautonetProcessOther($intro, $SiteID, $id_content));
			$full_text	=	'<!--{type:article}-->'.mostidy_clean(mosModelNewsautonetProcessOther($full_text, $SiteID, $id_content));
		}		
	}
	else if ($html->find('div[id="list-photo"]',0)){
		// special
		return false;
	}else {
		$message	=	'#465 models newsAutonet mosModelNewsautonetGetContent. <b>Not get content</b>.'.$id_content.' | '. $content_link;
		array_push($arrErr,$message);
		return false;
	}	

	$content_date	=	trim(preg_replace('/[tTzZ]/ism',' ', $content_date));

	$obj_content			=	new stdClass();
	$href	=	new href();
	$title_alias = strtolower($href->convertalias($title));
	$obj_content->title		=	trim(str_replace("\r\n",' ',$title));
	$obj_content->intro		=	trim(str_replace("\r\n",' ',$intro));
	$obj_content->fulltext	=	trim(str_replace("\r\n",' ',$full_text));
	$obj_content->cat_title	=	trim(str_replace("\r\n",' ', $cat_title));
	$obj_content->catid_original	=	trim(str_replace("\r\n",' ', $cat_id_original));
	$obj_content->link		=	$content_link;
	$obj_content->alias		=	$title_alias;
	$obj_content->content_date		=	$content_date;
	$obj_content->PageHTML	=	$source_content;
	$obj_content->arr_video	=	$arr_video;
	$obj_content->id_original = $id_content;

	return $obj_content;
}
function mosModelNewsautonetProcessVideo(& $str_in, $SiteID)
{
	$html	=	loadHtmlString($str_in);
	if (! $videos = $html->find('embed')) {

	}
	$arr_video	=	array();
	$root		=	'http://autonet.com.vn/';
	$href		=	new href();
	$reg_fileflv	=	'/&file=(.*?)&image=/ism';
	for ($i=0; $i<count($videos); $i++)
	{
		$obj_video	=	new stdClass();
		$video	= $html->find('embed',$i);
		$anchor	=	'<p id="'. uniqid('video_vb') .'" style="display:none;">video autonet</p>';
		$obj_video->anchor	=	$anchor;			
		if (!preg_match($reg_fileflv,$video->flashvars,$matches_video)) {
			continue;
		}
		$obj_video->video	=	$href->process_url($matches_video[1],$root);
		$arr_video[]		=	$obj_video;
		$html->find('embed',$i)->outertext	=	$anchor;		
	}
	$str_in		=	$html->outertext;
	return $arr_video;
}

// lấy thông tin link khác
function mosModelNewsautonetProcessOther($str_in, $SiteID,$id_original)
{
	global $database,$error;
	$db	=	$database;
	return $str_in;
}

// Lưu nội dung bài viết
function mosModelNewsautonetSave($content, $section_id = 1, $catid = 1, $SiteID = 'atn135')
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
	$row->SiteName		=	'autonet.com.vn';	
	$row->Domain		=	'autonet.com.vn';	
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

	$fmtsql = "INSERT INTO `#__article2010_new_autonet` SET %s ON DUPLICATE KEY UPDATE  %s  ";
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
//	echo $db->getQuery(); die();
	if (!$db->query()) {
		$messege	=	$db->getQuery();
		array_push($arrErr,$messege);
		return false;
	}
	$id = mysql_insert_id();	
	mosModelNewsautonetSaveMedia($content->arr_image,$id,$SiteID);
	mosModelNewsautonetSaveParam($content,$id,$SiteID);
	mosUpdateOther($content->id_content,$content->title,$id,'#__article2010_new_autonet');
	return true;
}

function mosModelNewsautonetSaveMedia($arr_media,$contenid, $SiteID = 'atn135')
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
			$message	=	'#562 models newsEnvov mosModelNewsautonetSaveMedia.<b> Invalid store media</b> for '.$media->Path.'. Link: '.$media->media_url.' sql error: '.$row->getError ();
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
function mosModelNewsautonetSaveParam($content,$contenid, $SiteID = 'ttvh135')
{
	global $arrErr,$database;	
	
	$arr_video	=	$content->arr_video;
	$table_name	=	'article2010_new_autonet';
	for ($i = 0; $i<count($content->arr_video); $i++)
	{
		$video	=	$content->arr_video[$i];
		$file_name	=	$content->alias.'_'.$i.'.flv';		
		mosVideosStore($contenid,$content->id_original, $file_name,'autonet.com.vn', 'autonet.com.vn',$video->video,1,1,'',$table_name,$video->anchor);		
	}	
	// store comment	
}