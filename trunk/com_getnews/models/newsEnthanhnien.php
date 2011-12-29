<?php

function mosModelNewsEnthanhNienGetNews($date_started = null, $get_existing = true)
{	
	global $arrErr,$database, $mosConfig_live_site;
	$db = $database;
	$arr_obj 	=	getCatFromData();	
	$obj_cat	=	$arr_obj[0]; 
	$startdate	=	$currentdate	=	 strtotime(date('Y-m-d')); 
	$param	=	$obj_cat->lastGet_param;
	preg_match('/startdate=([^;]*);currentdate=([^;]*);page=([^;]*);enddate=([^;]*);/ism',$param,$matches_param);
	
	$page		=	1;	$enddate	=	0;	
	if(isset($matches_param[1]) and $matches_param[1] )
		$startdate	=	$matches_param[1];
	if(isset($matches_param[2]) and $matches_param[2])
		$currentdate	=	$matches_param[2];
	if(isset($matches_param[3]) and $matches_param[3])
		$page	=	$matches_param[3];
	if(isset($matches_param[4]))
		$enddate	=	$matches_param[4];
		
	
	$data_content = mosModelThanhNienGetListContent($obj_cat->title,$currentdate, $page); 
	$arr_ID		=	$data_content->arrID;
	$arr_year_alias	=	$data_content->year_alias;	
	

	$arr_result	=	array();
	if ($get_existing==false and is_array($arr_ID) and count($arr_ID)>0) {
		$db = $database;
		$_id	=	implode(',',$arr_ID);
		$query = "SELECT id_original 
					FROM #__article2010_new_thanhnien_en
					WHERE id_original in($_id)";
		$db->setQuery($query);
		$arr_result	=	$db->loadResultArray();
		
		$arr_ban	=	mosBanidGet('en.thanhniennews.com',array('id_origional'),"id_origional  in($_id)");	
		
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
			continue;
		}
		
		$id_content	=	$arr_ID[$i];
		$link		=	$mosConfig_live_site."/index.php?option=$option&task=getnewsthanhnien.en&conten_id=".$arr_ID[$i];
	
		$web 		=	parse_url($link);
		$begin		=	md5('BEGIN_GET_CONTENT_TN');
		$end		=	md5('END_GET_CONTENT_TN');
		 
		$postdata	=	$web['query'];
		$postdata	.=	'&begin_get_content='.$begin;
		$postdata	.=	'&end_get_content='.$end;
		$postdata	.=	'&year_alias='.$arr_year_alias[$i];
		$postdata	.=	'&cat_title='.$obj_cat->title;
	
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
		
	if($enddate >= $currentdate)
	{		
		$currentdate	=	0;
		$page			=	1;
		$enddate		=	$startdate;
	}
	
	$date_started	=	strtotime($date_started);
	$page			=	$data_content->page;
	
	if ($currentdate < $date_started) {
		$enddate	=	$startdate;
		$startdate	=	0;
		$currentdate	=	0;
		$page	=	1;
	}else if ($data_content->isNext == false) {
		if($currentdate)
		{			
			$currentdate	=	strtotime(date('Y-m-d',$currentdate-86400));
		}	
		$arr_obj[0]->isNext	=	false;		
	}else {		
		// con nh trang			
		$arr_obj[0]->isNext	=	true;
	}	
	$param	=	"startdate=$startdate;currentdate=$currentdate;page=$page;enddate=$enddate;";
	$query	=	'UPDATE `#__article2010_category_thanhnien` 
					SET `last_run` = '.$db->quote(date ( 'Y-m-d H:i:s' )).', 
						`lastGet_param` = '.$db->quote($param).'
					WHERE `id` ='. $obj_cat->id;
	$db->setQuery($query);
	$db->query();

	$arr_obj[0]->number_getcontent	=	$number_getContent;
	$arr_obj[0]->date	=	date('Y-m-d',$currentdate+86400);
	return $arr_obj;
}

function getCatFromData()
{
	global $database;
	$db	=	& $database;
	if (isset($_REQUEST['catid_origional'])) {
		$id_origional	=	$_REQUEST['catid_origional'];
		$query = "SELECT *
			FROM `#__article2010_category_thanhnien`
			WHERE publish = 1 and `isparent` = 0  and id_origional = $id_origional
			ORDER BY `last_run`
			LIMIT 0,2";	
		$db->setQuery($query);
		$obj	=	$db->loadObjectList();
		$obj[1]	=	$obj;
	}else {
		$query = "SELECT *
			FROM `#__article2010_category_thanhnien`
			WHERE publish = 1 and `isparent` = 0
			ORDER BY `last_run`
			LIMIT 0,2";	
		$db->setQuery($query);
		$obj	=	$db->loadObjectList();
	}
	
	return $obj;
}

function mosModelThanhNienGetListContent($cat_title = 'Youth  / Science', $date = '1305820800', $page =1)
{
	
	global $arrErr,$database, $mosConfig_live_site;
	
	$date		=	date('m/d/Y', $date);
	//$date		=	'05/03/2011';
	$url		=	'http://www.thanhniennews.com/Pages/View-by-date.aspx?Date='.$date.'&MainCat='.$cat_title.'&Page='.$page;
	
	$url		=	str_replace(' ','%20',$url);	

	$browser	=	new phpWebHacks();
	$response	=	$browser->get($url);
	
	$reg		=	'/<div class=pno_tinchuyenmuc_dt_title><a href=\'[^\']*\/(\d+)\/Pages\/(\d*).aspx\'>.*?<\/a>/ism';
	if (!preg_match_all($reg,$response,$matches)) {
		$message	=	'#341 models news mosModelNewsGetNews. Invalid get news content';
		array_push($arrErr,$message);
	}
	$obj_return	=	new stdClass();
	// get next page
	$page	=	$page + 1;
	$reg	=	'/<span class=\'searchPage\'>\s*'.$page.'\s*<\/span>/ism';
	$obj_return->isNext	=	false;
	$obj_return->page	=	1;
		
	if (preg_match($reg,$response)) {
		$obj_return->isNext	=	true;
		$obj_return->page	=	$page;
	}
	
	$obj_return->arrID	=	array();	
	$obj_return->year_alias	=	array();
	
	if(count($matches[2]))
	{
		$obj_return->arrID		=	$matches[2];
		$obj_return->year_alias	=	$matches[1];
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
function mosModelNewsEnthanhNienGetTN($cat_title,$year_alias = '2010', $id_content, $section_id = '1', $catid =1 , $path_image = 'images', $link_image,$SiteID = 'tne25')
{
	global $arrErr;
	$link = "http://www.thanhniennews.com/$year_alias/Pages/$id_content.aspx";
	
	if (!$content	=	mosModelNewsEnthanhNienGetContent($link,$id_content))
	{
		$message_ban	=	$arrErr[count($arrErr)-1];		
		$message	=	'#389 models news mosModelNewsEnthanhNienGetTN. Not get content.'.$id_content;
		array_push($arrErr,$message);
		mosBanidStore('www.thanhniennews.com','',$id_content,1,$message_ban."\r\n".$message);
		return false;
	}	
	
	$content->intro		=	mosModelNewsEnthanhnienProcessOther($content->intro,$SiteID, $id_content);
	$content->fulltext	=	mosModelNewsEnthanhnienProcessOther($content->fulltext,$SiteID, $id_content);
	
	$root	=	'http://www.thanhniennews.com';
	$arr_Images	=	array();
	mosGetImages($content,$root, $arr_Images,$path_image, $link_image);
	
	$content->arr_image		=	$arr_Images;
	$content->id_content	=	$id_content;
	$content->cat_title		=	trim(str_replace("\r\n",'',$cat_title));
	
	if (!mosModelThanhNienSave($content,$section_id , $catid, $SiteID)) {
		$message	=	'#391 models news mosModelNewsEnthanhNienGetTN. Not save content.'.$id_content;
		array_push($arrErr,$message);
		return false;
	}
}


function mosCountWord($str_in)
{
	$str_in = strip_tags($str_in);
	$_str_out = preg_split('/\s+/',$str_in);	
	return count($_str_out);
}


function mosModelNewsEnthanhNienGetContent($link,$id_content)
{
	global $arrErr,$database;
	$db	=	$database;
	
	$source	=	loadHtml($link);
	$source_html = $source->innertext;
	$source_content = $source->find('div[id="print-news"]',0);
	
	$source_content = $source_content->innertext;

// get title
	$reg_title = '/<div class="newsTitle">(.*?)<\/div>/ism';
	if (!preg_match($reg_title,$source_content,$matches)) {
		$message	=	'#361 models news mosModelNewsGetNews. Invalid get id of news content';
		array_push($arrErr,$message);
		return false;
	}
	$title = $matches[1];

// get alias
	$href		=	new href();
	$alias		=	$href->take_file_name($title);
	
// Get source 	
	$pageContent = $source->find('div[class="pageContent"]',0);
	
	$html			=	$pageContent->innertext;

	$obj_cuttext	=	new AutoCutText($html,20);
	
	$intro			=	$obj_cuttext->getIntro();
	$full_text		=	$obj_cuttext->getFulltext();
	
// get image full not in block <img border=0 src="/images/newsimages/travel-024-10w.jpg">
	$reg_image	=	'/<img[^>]*src=("|\'|)([^"\' ]*)("|\'|)[^>]*>/ism';	
	
	if (preg_match($reg_image,$source_content,$matches)) {
		$link_image		=	$matches[2];
		
		$source_content = str_replace($matches[0],'',$source_content);
		$tag_images			=	'<img src="'.$link_image.'" title="'.$title.'" alt="'.$title.'" />';
		$intro	=	$tag_images.$intro;
	}
	
// remove image
	$full_text	= str_replace($matches[0],'',$full_text);
// remove follow
	$full_text	= preg_replace('/<span[^>]*>\s*Reported by[^<>]*<\/span>/ism','',$full_text);
	$full_text	= preg_replace('/<p[^>]*>\s*Reported by[^<>]*<\/p>/ism','',$full_text);
	$full_text	= preg_replace('/<font[^>]*>\s*Reported by[^<>]*<\/font>/ism','',$full_text);
	$full_text	= preg_replace('/<div[^>]*class="byLine"[^>]*>\s*Source[^<>]*<\/div>/ism','',$full_text);

// get date
	$reg_date	=	'/<div class="newsModified">(.*?)<\/div>/ism';
	if (!preg_match($reg_date,$source_content,$matches)) {
		$message	=	'#333 models newsbaomoi mosModelNewsbaomoiGetContent. Invalid get introtext for '.$link;
		array_push($arrErr,$message);
	}
	
	$date_content = $matches[1];
	$date_content = preg_replace('/Last updated:/ism','',$date_content);
	$date_content = trim($date_content);
	$date_content = preg_replace('/\s/ism','|',$date_content);
	$arr_time = explode('|',$date_content);

// 5/4/2011 14:00

	$date = $arr_time[0];
	$time = $arr_time[1];	
	
	$arr_time = explode(':',$time);
	$hour = intval($arr_time[0]);
	$minutes = intval($arr_time[1]);
	$seconds = 12;
	
	if(isset($arr_time[2]) && $arr_time[2] == 'PM'){
		$hour = $arr_time[0] + 12;
	}
	
	$arr_day = explode('/',$date);
	$day = intval(trim($arr_day[1]));
	$month = intval(trim($arr_day[0]));
	$year = intval(trim($arr_day[2]));	
	
	$day_now = mktime($hour,$minutes,$seconds,$month,$day,$year);
	$created = date('Y-m-d H:m:00',$day_now);
	
	$obj_content			=	new stdClass();
	$obj_content->title		=	trim(str_replace("\r\n",' ',$title));
	$obj_content->intro		=	mostidy_clean(trim(str_replace("\r\n",' ',$intro)));
	$obj_content->fulltext	=	mostidy_clean(trim(str_replace("\r\n",' ',$full_text)));	
	$obj_content->link		=	$link;
	$obj_content->alias		=	$alias;
	$obj_content->content_date		=	$created;
	$obj_content->PageHTML	=	$source_html;
	$obj_content->catid_original	=	'';

	return $obj_content;
}

function mosModelNewsEnthanhnienProcessOther($str_in, $SiteID,$id_original)
{
	global $database,$error;
	$db	=	$database;
	$reg_link_other = '/<a.*?href=["\' ]*([^"\' ]*)["\' ]*.*?>(.*?)<\/a>/ism';
	$reg_id_other = '/\/(\d+)\.aspx/ism';
	$href	=	new href();
	$root	=	'http://www.thanhniennews.com';
		
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
		
		if ($id_result	=	mosModelNewsvovSaveOther($SiteID, $id_original, $matches[1],$matches_link[0][$i], $link, $catname)) {
			$title		=	strip_tags($matches_link[2][$i]);
			$link_content = '<a title=\''.str_replace(array('&gt;','\''),array(' ','"'),$title).'\' href="/index.php?option=com_content&task=view&id=' . $id_result.'" >'.$title."</a>";
			$str_in		=	str_replace($matches_link[0][$i],$link_content,$str_in);
		}	
	}
	
	return $str_in;
}

function mosModelNewsvovSaveOther ($SiteID,$id_original,$id_original_other,$str_replace,$link_other)
{
	global $database,$error;
	
	$db	=	& $database;
	$query = "SELECT * FROM #__article2010_new_thanhnien_en WHERE id_original = ".trim($id_original_other);
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


function mosModelThanhNienSave($content, $section_id = 1, $catid = 1, $SiteID = 'tne25')
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
	$row->SiteName		=	'en.thanhniennews.com';	
	$row->Domain		=	'www.thanhniennews.com';	
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

	$fmtsql = "INSERT INTO `#__article2010_new_thanhnien_en` SET %s ON DUPLICATE KEY UPDATE  %s  ";
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
	mosUpdateOther($content->id_content,$content->title,$id,'#__article2010_new_thanhnien_en');
	return true;
}

function mosModelNewsSaveMedia($arr_media,$contenid, $SiteID = 'tne25')
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