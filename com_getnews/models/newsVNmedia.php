<?php

function mosModelVNMGetData($max_number = 1000, $get_existing = true)
{
	global $arrErr,$database, $mosConfig_live_site;
	$db = $database;
	
	if (!$arr_obj 	=	getCatFromData()) {
		return false;
	}
	
	$obj_cat	=	$arr_obj[0];
	$param		=	$obj_cat->lastGet_param;
	preg_match('/start=(\d*)param=(\d*)end=(\d*)/ism',$param,$matches_param);
	$param_start	=	$param_param	=	$param_end	=	0;
	if(isset($matches_param[1]))
		$param_start	=	$matches_param[1];
	if(isset($matches_param[2]))
		$param_param	=	$matches_param[2];
	if(isset($matches_param[3]))
		$param_end	=	$matches_param[3];	
		
	$browser	=	new phpWebHacks();
	
	$data_content	=	'';
	
	if ($param_param) {
		$query = "SELECT PageHTML 
					FROM #__article2010_new_vnmedia 
					WHERE id_original = ". intval($param_param);
		$db->setQuery($query);
				
		$response	=	$db->loadResult();
		$html	=	'';
	echo "http://www6.vnmedia.vn/newsdetail.asp?NewsId=$param_param&CatId=$obj_cat->id_origional";	
		if (!$response) {
			$url	=	"http://www6.vnmedia.vn/newsdetail.asp?NewsId=$param_param&CatId=$obj_cat->id_origional";			
			$response	=	$browser->get($url);
		}		
		$data_content	=	getListContent($response);
	}else {		
		$url		=	"http://vnmedia.vn/ShowCat.asp?CatId=$obj_cat->id_origional";
		$response	=	$browser->get($url);
		$html		=	loadHtmlString($response);
		$obj		= 	$html->find('table[id="table61"]',0);
		$response	= 	$obj->outertext;
		$data_content	=	getListContent($response);
		$start		=	$data_content['id'][0];
	}	
	$arr_ID		=	$data_content['id'];
	$arr_title	=	$data_content['title'];
	$arr_link	=	$data_content['link'];
	$arr_result	=	array();
	
	if ($get_existing==false) {
		$_id	=	implode(',',$arr_ID);
		$query = "SELECT id_original 
					FROM #__article2010_new_vnmedia
					WHERE id_original in($_id)";
		$db->setQuery($query);
		
		$arr_result	=	$db->loadResultArray();
	
		$arr_ban	=	mosBanidGet('vnmedia.vn',array('id_origional'),"id_origional  in($_id)");
	
		if (count($arr_result))
		{
			if (count($arr_ban))	$arr_result	=	array_merge($arr_result,$arr_ban);
		}
		else $arr_result	=	$arr_ban;

	}

	$option	=	$_REQUEST['option'];
	$number_getContent	=	0;
	$number	=	count($arr_ID)>$max_number?$max_number:count($arr_ID);

	for ($i = 0; $i < $number ; $i++)
	{
		if ($get_existing==false && count($arr_result) >0 && in_array($arr_ID[$i],$arr_result) )
		{
			array_push($arrErr,'#444 '.$arr_ID[$i].' is existing');
			continue;
		}

		$id_content	=	$arr_ID[$i];
		$begin		=	md5('BEGIN_GET_CONTENT_VNMEDIA');
		$end		=	md5('END_GET_CONTENT_VNMEDIA');
		
		$url		=	$mosConfig_live_site."/index.php?option=$option";

		$arr_post	=	array();
		$arr_post['begin_get_content']	=	$begin;
		$arr_post['end_get_content']	=	$end;
		$arr_post['task']				=	'getnewsvnmedia';
		$arr_post['catid_origional']	=	$obj_cat->id_origional;
		$arr_post['content_id']			=	$arr_ID[$i];
		$arr_post['cattitle_origional']	=	$obj_cat->title;
		$arr_post['content_link']		=	$arr_link[$i];
		$arr_post['content_title']		=	$arr_title[$i];

		$info	=	$browser->post($url,$arr_post);
     
         if (preg_match('/' . $begin . '(.*?)' . $end . '/ism', $info, $match)) 
         {                   
             $info=trim($match[1]);
         }
         else {
         	$message	=	'ERROR_GET_CONTENT_VNMEDIA| #123 API false'.$info;
         	array_push($arrErr,$message);
            continue;
         }
      	 if (stristr($info,'ERROR_GET_CONTENT_VNMEDIA')) {
  	 		$message	=	'ERROR_GET_CONTENT_VNMEDIA| '.$info;
     		array_push($arrErr,$message);
            continue;
         }
         
         $number_getContent	=	$number_getContent + 1;
	}
	$param	=	'';	
	
	if(in_array($param_start,$arr_ID) or $param_start == 0)
	{
		$param_start	=	$arr_ID[0];
	}

	if(in_array($param_end,$arr_ID))
	{	
		$param	=	'start='.$param_start.'param=0end='.$param_start;
		
	}else if (count($arr_ID)) {
		$param	=	'start='.$param_start.'param='.$arr_ID[count($arr_ID)-1].'end='.$param_end;

	}else {
		$param	=	'start='.$param_start.'param=0end='.$param_start;
	}
	$query	=	'UPDATE `#__article2010_category_vnmedia` 
						SET `last_run` = '.$db->quote(date ( 'Y-m-d H:i:s' )).', 
							`lastGet_param` = '.$db->quote($param).'
						WHERE `id` ='. $obj_cat->id;
	
	$arr_obj[0]->number_getcontent	=	$number_getContent;
	
	$db->setQuery($query);
	
	$db->query();	
	echo '<br />$param_start: '. $param_start;
	echo '<br />$param_end: '. $param_end;
	echo $db->getQuery();
//	mysql_close($db->_resource);
	if (count($arr_obj>1)) {
		return $arr_obj;
	}else {
		return false;
	}
}

function getCatFromData()
{
	global $database;
	$db	=	& $database;
	if (isset($_REQUEST['catid_origional'])) {
		$arr_obj	=	array();
		$id_origional	=	$_REQUEST['catid_origional'];
		$query = "SELECT *
			FROM `#__article2010_category_vnmedia`
			WHERE publish = 1 and `parent` <> 0  and id_origional = $id_origional
			ORDER BY `last_run`
			LIMIT 0,1";	
		$db->setQuery($query);
		$db->loadObject($obj);
		$arr_obj[0]	=	$obj;
		$arr_obj[1]	=	$obj;
	}else {
		$query = "SELECT *
			FROM `#__article2010_category_vnmedia`
			WHERE publish = 1 and `parent` <> 0 ".
			" ORDER BY `last_run`
			LIMIT 0,2";	
		$db->setQuery($query);
//		echo $db->getQuery();
//		die();
		$arr_obj	=	$db->loadObjectList();
	}
	return $arr_obj;
}

function getListContent($str_html)
{
	// search on home page of category	
	$arr_link	= array();
	$arr_title	= array();
	$arr_id		= array();
	$root	=	'http://www6.vnmedia.vn';
	$href	=	new href();
	
	$reg		=	'/<td[^>]*class="textbody">\s*<a\s*class="title1"\s*href="([^"]+)">(.*?)<\/a>.*?<\/td>/ism';
	if (preg_match_all($reg,$str_html,$matches)) {		
		for ($i=0;$i<count($matches[1]);$i++)
		{	
			$arr_link[] = $href->process_url(trim($matches[1][$i]),$root);
			$arr_title[] = trim(strip_tags($matches[2][$i]));
		}	
	}
	// remove bai viec khac
	$reg	=	'/<!-- [^<>]*tin [^<>]* quan -->\s*<TABLE id="vnmedia_tin_lien_quan" class="style_tin_lien_quan"[^>]*>.*?<\/table>\s*<!-- [^<>]* tin [^<>]* quan -->/ism';
	$str_html	=	preg_replace($reg,'',$str_html);
	$reg_other	=	'/<!-- [^<>]*tin [^<>]* quan -->(.*?)<!-- [^<>]* tin [^<>]* quan -->/ism';
	
	if (preg_match($reg_other,$str_html,$matches_other)) {	
		$reg	=	'/<a[^>]*href="([^"]+)"[^>]*class="news_more"[^>]*>(.*?)<\/a>/ism';
		if (preg_match_all($reg,$matches_other[1],$matches_link_other)) {			
			for ($i=0;$i<count($matches_link_other[1]);$i++)
			{	
				$arr_link[] = $href->process_url(trim($matches_link_other[1][$i]), $root);
				$arr_title[] = trim(strip_tags($matches_link_other[2][$i]));
			}
		}
	}

//	echo htmlspecialchars($str_html);
	///VN/gian_an_do_nga_hoan_tap_tran_chung_17_226740.html
	
	for ($i=0; $i<count($arr_link); $i++)
	{
		$link	=	$arr_link[$i];
		if (preg_match('/\d+_(\d+)\.html/ism',$link,$matches)) {
			$arr_id[]	=	$matches[1];
		}else if(preg_match('/NewsId=(\d+)&CatId=\d+/ism',$link,$matches)) {
//			http://vnmedia.vn/NewsDetail.asp?NewsId=226732&CatId=23
			$arr_id[]	=	$matches[1];
		}else {
			$arr_link[$i]	=	'';
			$arr_title[$i]	=	'';
			$arr_id[$i]	=	'';
			continue;
		}
	}	
	$data_result	=	array();
	$data_result['link']	=	$arr_link;
	$data_result['title']	=	$arr_title;
	$data_result['id']		=	$arr_id;
	
	return $data_result;
}

function mosModelNewsVNMGetVNM($id_content, $catid_origional, $cattitle_origional, $title_content, $link_content, $section_id, $catID, $path_image = 'images', $link_image ='images', $SiteID ='vnm145')
{
	global $arrErr;	
	
	if (!$content	=	mosModelNewsVNMGetcontent($link_content, $id_content, $title_content))
	{
		$message_ban	=	$arrErr[count($arrErr)-1];		
		$message	=	'#389 models news mosModelNewsVNMGetVNM. Not get content.'.$id_content;
		array_push($arrErr,$message);
		mosBanidStore('vnmedia.vn','',$id_content,1,$message_ban."\r\n".$message);
		return false;
	}

	$content->intro		=	mosModelNewsVNmediaProcessOther($content->intro,$SiteID, $id_content);
	$content->fulltext	=	mosModelNewsVNmediaProcessOther($content->fulltext,$SiteID, $id_content);
	
	$root	=	'http://www6.vnmedia.vn';
	$arr_Images	=	array();
	
	mosGetImages($content,$root, $arr_Images,$path_image, $link_image);
	
	$content->arr_image			=	$arr_Images;
	$content->catid_original	=	$catid_origional;
	$content->cattitle_origional=	$cattitle_origional;
	
	if (!mosModelNewsVNmediaSave($content,$section_id , $catID, $SiteID)) {
		$message	=	'#391 models news mosModelNewsVNMGetVNM. Not save content.'.$id_content;
		array_push($arrErr,$message);
		return false;
	}
	return $content->PageHTML;
}

//	$link	=	'http://www6.vnmedia.vn/VN/my_nhan_xu_han_quot_lot_xac_quot_dep_den_sung_so_456_226475.html';
function mosModelNewsVNMGetcontent($link, $id_content, $title_content)
{
	global $arrErr;
	
	$browser	=	new phpWebHacks();
	$response	=	$browser->get($link);
		
// Tìm title_alias bài viết	
	$href = new href();
	$title_alias = $href->convertalias($title_content);
	
// Tìm th�?i gian
	$reg_time	=	'/<td[^>]+class="title_cap3_1"[^>]*>(.*?)<\/td>/ism';
	if (!preg_match($reg_time,$response,$time_matches)) {
		$message	=	'#335 models newsVNmedia mosModelNewsVNMGetcontent. Invalid get time for '.$link;
		array_push($arrErr,$message);
		return false;
	}
	//13/05/2011
	$time	=	strtolower(strip_tags($time_matches[1]));
	
	preg_match('/(\d{1,2})\/(\d{1,2})\/(\d{2,4})/ism',$time,$matches_time);
	
	$time = preg_replace('/(h|,)/ism','/',$time);
	$arr_time = explode('/',$time);
	
	$day_now = mktime(date("h"), date("i"), date("s"),$matches_time[2],$matches_time[1],$matches_time[3]);
	$created = strftime('%Y-%m-%d %H:%M:00',$day_now);

// reg firsh image
	$reg_image	=	'/<!-- pictureid[^>]*-->(.*?)<!-- description -->/ism';
	$tag_images	=	'';
	if (preg_match($reg_image,$response,$image_mathces)) {		
		if (preg_match('/<img[^>]*src="([^"]*)"[^>]*>/ism',$image_mathces[1],$image_mathces)) 
		{		
			$link_image	=	$image_mathces[1];
			$tag_images		=	'<img src="'.$link_image.'" title="'.$title_content.'" alt="'.$title_content.'" style="float:left;" />';
		}
	}	
// reg body	
	$reg_body	=	'/<!-- BODY -->(.*?)<!-- END BODY -->/ism';
	if (!preg_match($reg_body,$response,$matches_body)) {
		$message	=	'#253 models newsVNmedia mosModelNewsVNMGetcontent. Invalid get body '.$link;
		array_push($arrErr,$message);
		return false;
	}	
	
	$html	=	"<div>$matches_body[1]</div>";
	$html	=	loadHtmlString($html);
	$html	=	$html->find('div',0);
	
	$arr_text	=	$html->children();
	
	$body_text	=	implode(' ',$arr_text);	
	$obj_cuttext	=	new AutoCutText($body_text,5);
	$introtext		=	$obj_cuttext->getIntro();
	$fulltext		=	$obj_cuttext->getFulltext();
	$introtext		=	preg_replace('/\(VnMedia\)/ism','',$introtext);
	$introtext	=	$tag_images . strip_tags($introtext);
	
	$reg	=	'/<!-- [^<>]*tin [^<>]* quan -->\s*(<TABLE id="vnmedia_tin_lien_quan" class="style_tin_lien_quan"[^>]*>.*?<\/table>)\s*<!-- [^<>]* tin [^<>]* quan -->/ism';
	
	if (preg_match($reg,$response,$matches_other)) {
		$other =	preg_replace('/<div align="right">.*?<\/div>/ism','',$matches_other[1]);
		if (preg_match_all('/<a[^>]*href="([^"]*)"[^>]*>(.*?)<\/a>/ism',$other,$matches_other)) 
		{
			$other	=	'';
			for ($i=0; $i<count($matches_other[1]); $i++)
			{
				$other	=	$other. '<a href="'.$matches_other[1][$i].'">'.strip_tags($matches_other[2][$i]).'</a> <br />'."\n";
			}
			$fulltext	=	$fulltext . $other;
		}		
	}

	$obj_content	=	new stdClass();
	$obj_content->id_content	=	$id_content;
	$obj_content->SourceURL		=	$link;
	$obj_content->content_date	= 	$created;	
	$obj_content->title			=	trim(str_replace("\r\n",' ',$title_content));
	$obj_content->title_alias	=	$title_alias;
	$obj_content->intro			=	mostidy_clean(trim(str_replace("\r\n",' ',$introtext)));
	$obj_content->fulltext		=	mostidy_clean(trim(str_replace("\r\n",' ',$fulltext)));	
	$obj_content->PageHTML 		=	$response;

	return $obj_content;
}

function mosModelNewsVNmediaProcessOther($str_in, $SiteID,$id_original,$catname = null)
{
	global $database,$error;
	$db	=	$database;
	$reg_link_other = '/<a.*?href=["\' ]*([^"\' ]*)["\' ]*.*?>(.*?)<\/a>/ism';
	$reg_id_other = '/(\d+_|NewsId=)(\d+)(\.html|&CatId=)/ism';
	$href	=	new href();
	$root	=	'http://www6.vnmedia.vn';
	
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
		$id_orgional_other	=	$matches[2];
		
		if ($id_result	=	mosModelNewsVNmediaSaveOther($SiteID, $id_original, $matches[2],$matches_link[0][$i], $link, $catname)) {
			$title		=	strip_tags($matches_link[2][$i]);
			$link_content = '<a title=\''.str_replace(array('&gt;','\''),array(' ','"'),$title).'\' href="/index.php?option=com_content&task=view&id=' . $id_result.'" >'.$title."</a>";
			$str_in		=	str_replace($matches_link[0][$i],$link_content,$str_in);
		}	
	}
	return $str_in;
}

function mosModelNewsVNmediaSaveOther ($SiteID,$id_original,$id_original_other,$str_replace,$link_other,$catID)
{
	global $database,$error;
	
	$db	=	& $database;
	$query = "SELECT * FROM #__article2010_category_vnmedia WHERE id_original = ".trim($id_original_other);
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

function mosModelNewsVNmediaSave($data, $section_id = 1, $catid = 1, $SiteID = 'vnm145')
{
	
	global $database, $my, $mainframe, $mosConfig_offset, $arrErr;
	$db	=	$database;
	
	$date	=	date('Y-m-d H:i:s');
	
	$nullDate = $database->getNullDate ();

	$row = new mosVovArticle2010_new2( $db );

	$row->firstRunDate  =	$row->firstRunDate ? $row->firstRunDate : date ( 'Y-m-d H:i:s' );
	$row->latestRunDate =	date ( 'Y-m-d H:i:s' );	
	$row->id_original	=	$data->id_content;	
	$row->SiteID		=	$SiteID;	
	$row->SiteName		=	'vnmedia.vn';	
	$row->Domain		=	'vnmedia.vn';	
	$row->SourceURL		=	$data->SourceURL;
	$row->created		= 	date("Y-m-d H:i:s",strtotime($data->content_date));
	$row->title			=	$data->title;
	$row->title_alias	=	$data->title_alias;
	$row->introtext		=	str_replace("\r\n",' ',$data->intro);
	$row->fulltext		=	str_replace("\r\n",' ',$data->fulltext);
	$row->sectionid		=	$section_id;
	$row->catid			=	$catid;
	$row->catid_original=	$data->catid_original;
	$row->CatName		=	$data->cattitle_origional;
	$row->PageHTML 		=	str_replace("\r\n",' ',$data->PageHTML);

	$fmtsql = "INSERT INTO `#__article2010_new_vnmedia` SET %s ON DUPLICATE KEY UPDATE  %s  ";
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
		$messege	= $db->getErrorMsg();
		array_push($arrErr,$messege);
		return false;
	}
	$id = mysql_insert_id();
	
	mosModelNewsVNmediaSaveMedia($data->arr_image,$id,$SiteID);
	mosUpdateOther($content->id_content,$content->title,$id,'#__article2010_new_vnmedia');
//	mosModelNewsVNmediaSaveParam($content,$row->id,$SiteID);	
	return true;
}

function mosModelNewsVNmediaSaveMedia($arr_media,$contenid, $SiteID = 'kt18')
{	
	global $database, $arrErr;
	$db	=	$database;

	for ($i = 0; $i <count($arr_media); $i++)
	{
		$media	=	$arr_media[$i];
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
		$row->Size				=	$media->Size;	
		$row->FileName			=	$media->FileName;	
		$row->Path				=	$media->Path;	
		$row->FileType			=	$media->FileType;	
		$row->MediaType			=	$media->MediaType;	
		
		if (! $row->store ()) {
			$message	=	'#562 models news mosModelNewsVNmediaSaveMedia. Invalid store media for '.$media->Path.'. Link: '.$media->media_url.' sql error: '.$row->getError ();
			array_push($arrErr,$message);				
		}
	}
	return true;
}