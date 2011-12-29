<?php

function mosModelNewsvovGetNews(& $start, & $lastGet_vovId, $max_number = 1000, $get_existing = true)
{		
	global $arrErr,$database, $mosConfig_live_site;

	if ($start == 0 || !isset($_REQUEST['id_content'])) {
		$id_content	=	mosModelNewsvovGetNewId();
		$number	=	$id_content - $lastGet_vovId;		
		$number	=	$number<$max_number?$number:$max_number;
		$start	=	$id_content;
	}else { 
		$id_content	=	intval($_REQUEST['id_content']);
		$number		=	$max_number;
	}

	$id_result		=	$id_content	-	$number;	
	$arr_ID			=	array();
	$arr_result		=	array();
	
	for ($i = 0; $i < $number; $i++)
	{
		$arr_ID[]	=	$id_content;
		$id_content	=	$id_content - 1;
	}	
	$bool			=	0;
	if ($get_existing == false) {
		$db = $database;
		$_id	=	implode(',',$arr_ID);
		$query = "SELECT id_original 
					FROM #__article2010_new_vov 
					WHERE id_original in($_id)";
		$db->setQuery($query);
		$arr_result	=	$db->loadResultArray();	
		if (count($arr_ID) == count($arr_result)) 
		{
			$bool = 1;
			$id_content	=	$id_result;
		}
	}
	$number_run	=	1;
	
	while ($bool and $number_run <10) {		
		$arr_ID			=	array();
		$id_result		=	$id_content	-	$number;
		for ($i = 0; $i < $number; $i++)
		{
			$arr_ID[]	=	$id_content;
			$id_content	=	$id_content - 1;
		}		
		if ($get_existing == false) {
			$db = $database;
			$_id	=	implode(',',$arr_ID);
			$query = "SELECT id_original 
					FROM #__article2010_new_vov 
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

	if ($get_existing==false) {
		$db = $database;
		$_id	=	implode(',',$arr_ID);		
		$arr_ban	=	mosBanidGet('vovnews.vn',array('id_origional'),"id_origional  in($_id)");
				
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
	var_dump($arr_ID);
	/*for ($i = 0; $i < count($arr_ID) ; $i++)
	{
		if ($get_existing==false && in_array($arr_ID[$i],$arr_result))
		{
			array_push($arrErr,'#444 '.$arr_ID[$i].' is existing');
			continue;
		}
		
		$id_content	=	$arr_ID[$i];
		$link		=	$mosConfig_live_site."/index.php?option=$option&task=getnewsvov&conten_id=".$arr_ID[$i];
		
		$web 		=	parse_url($link);
		$begin		=	md5('BEGIN_GET_CONTENT_VOV');
		$end		=	md5('END_GET_CONTENT_VOV');
		 
		$postdata	=	$web['query'];
		$postdata	.=	'&begin_get_content='.$begin;
		$postdata	.=	'&end_get_content='.$end;
		
//echo $link;
//echo '<br />';
//echo $postdata;
//echo '<br />';
//die();
		
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
         	$message	=	'ERROR_GET_CONTENT_VOV| #123 API false '.$id_content.' '.$info;
         	array_push($arrErr,$message);
            continue;
         }
      	 if (stristr($info,'ERROR_GET_CONTENT_VOV')) {
  	 		$message	=	'ERROR_GET_CONTENT_VOV| '.$info;
     		array_push($arrErr,$message);
            continue;
         }
         $number_getContent	=	$number_getContent + 1;
	}*/
	$obj_result	=	new stdClass();
	$obj_result->number_getcontent	=	$number_getContent;
	$obj_result->id_result			=	$id_result;
//	mysql_close($db->_resource);
	return $obj_result;
}

function mosModelNewsvovGetNewId()
{
	global $arrErr,$database, $mosConfig_live_site;
	$url		=	'http://vov.vn/';
	$browser	=	new phpWebHacks();
	$response	=	$browser->get($url);
	$reg		=	'/<div[^>]*class="latest"[^>]*>(.*?)<\/ul>\s*<\/div>/ism';
	if (!preg_match($reg,$response,$matches)) {
		$message	=	'#341 models newsvov mosModelNewsvovGetNewId. <b>Invalid get news content</b>';
		array_push($arrErr,$message);
		return false;
	}
	$list_news	=	$matches[1].'</ul>';
	$html	=	loadHtmlString($list_news);
	$ultags	=	$html->find('ul');
	$href		=	$ultags[0]->children[0]->children[0]->attr['href'];
	$reg		=	'/(\d+)\.vov/ism';
	if (!preg_match($reg,$href,$matches)) {
		$message	=	'#361 models newsvov mosModelNewsvovGetNewId. <b>Invalid get id</b> of news content';
		array_push($arrErr,$message);
		return false;
	}	
	return $id_content	=	intval($matches[1]);
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
function mosModelNewsvovGetVOV($id_content, $section_id = '1', $catid =1 , $path_image = 'images', $link_image,$SiteID = 'vn10')
{
	global $arrErr;
	$link_content	=	'http://vov.vn/Home/alias-vov/20114/'.$id_content.'.vov';	
//	echo $link_content;
	
	if (!$content	=	mosModelNewsGetContent($link_content,$id_content))
	{
		$message_ban	=	$arrErr[count($arrErr)-1];
		$message	=	'#389 models newsvov mosModelNewsGetVOV. Not get content.'.$id_content;
		array_push($arrErr,$message);
		mosBanidStore('vovnews.vn','',$id_content,$message_ban."\r\n".$message);
		return false;
	}

	$content->intro		=	mosModelNewsvovProcessOther($content->intro,$SiteID, $id_content);
	$content->fulltext	=	mosModelNewsvovProcessOther($content->fulltext,$SiteID, $id_content);
	
	if(!mosModelNewsvovProcessCategory($content))
	{
		$message	=	'#391 models news mosModelNewsGetVOV. Invalid process category or this category is not get content. id content: '.$id_content.', cat: '. $content->cat_title;
		array_push($arrErr,$message);
		return false;
	}
	
	$root	=	'http://vov.vn/';
	$arr_Images	=	array();
	
	mosGetImages($content, $root, $arr_Images, $path_image, $link_image);	
	
	
	$content->arr_image		=	$arr_Images;
	$content->id_content	=	$id_content;
	if (!mosModelNewsSave($content,$section_id , $catid, $SiteID)) {
		$message	=	'#391 models news mosModelNewsGetVOV. Not save content.'.$id_content;
		array_push($arrErr,$message);
		return false;
	}
}

function mosModelNewsGetContent($link,$id_content)
{
	global $arrErr,$database;	
	$db	=	$database;

	$browser	=	new phpWebHacks();
	echo $link;
	if (!$response	=	$browser->get($link)) {
		$message	=	'#832 models news mosModelNewsGetContent. Invalid get article '.$link;
		array_push($arrErr,$message);
		return false;
	}
	$link		=	$browser->get_addressbar();	
	
	$html	=	loadHtmlString($response);
	// get title
	$reg_title	=	'/<h1[^>]*class="title">(.*?)<\/h1>/ism';
	if (!$title	=	$html->find('h1[class="title"]',0)->innertext) {
		echo $response;
		die();
		$message	=	'#332 models news mosModelNewsGetContent. Invalid get title for '.$link;
		array_push($arrErr,$message);
		return false;
	}
	
	$title		=	strip_tags($title);
	// get alias
	$reg_alias	=	'/([^\/]*)\/\d+\/\d+\.vov/ism';
	$alias		=	'';
	if (preg_match($reg_alias,$link,$matches)) {
		$alias	=	$matches[1];
	}
	// get intro
	$reg_intro	=	'/<p[^>]*class="summary">(.*?)<\/p>/ism';
	if (!$intro	=	$html->find('p[class="summary"]',0)->innertext) {
		$message	=	'#333 models news mosModelNewsGetContent. Invalid get introtext for '.$link;
		array_push($arrErr,$message);
		return false;
	}
	
	$intro	=	strip_tags($intro);
	$intro	=	preg_replace('/\(VOV\)\s*-/ism','',$intro);
	
	// get story-photo
	if($html->find('div[class="story-photo"]'))
	{
		$photo	=	$html->find('div[class="story-photo"]',0)->innertext;
		$intro	=	$photo . $intro;
	}	
	// get full
	$reg_full	=	'/<span[^>]*id="ctl00_mContent_lbBody">(.*?)<\/span>\s*<p[^>]*class="author">/ism';
	if (!$full_text	=	$html->find('span[id="ctl00_mContent_lbBody"]',0)->innertext) {
		$message	=	'#334 models news mosModelNewsGetContent. Invalid get fulltext for '.$link;
		array_push($arrErr,$message);
		return false;
	}
	
//	$full_text	=	preg_replace('/<p>\s*<strong>\s*&gt;&gt;\s*<\/strong>\s*<a[^>]*>.*?<\/p>/ism','',$full_text);
//	$full_text	=	preg_replace('/<p>\s*<a[^>]*>\s*<strong>\s*&gt;&gt;.*?<\/a>\s*<\/p>/ism','',$full_text);	
//	$full_text	=	preg_replace('/<ul\s*class=story-listing>.*?<\/ul>/ism','',$full_text);
	// get category vov	
	if(!$html->find('a[id="ctl00_mContent_BreadCrumb1_rptBC_ctl00_lnkZone"]',0))
	{
		$message	=	'#339 models news mosModelNewsGetContent. Invalid get category for '.$link;
		array_push($arrErr,$message);
		return false;
	}
	$cat_link	=	trim(strip_tags($html->find('a[id="ctl00_mContent_BreadCrumb1_rptBC_ctl00_lnkZone"]',0)->href));
	$cat_title	=	trim(strip_tags($html->find('a[id="ctl00_mContent_BreadCrumb1_rptBC_ctl00_lnkZone"]',0)->innertext));
	
	// get date
	$reg_date	=	'/<span[^>]*id="ctl00_mContent_lbDate">(.*?)<\/span>/ism';
	if(!$date_time = $html->find('span[id="ctl00_mContent_lbDate"]',0)->innertext){
		$message	=	'#356 models news mosModelNewsGetContent. Invalid get date for '.$link;
		array_push($arrErr,$message);
		return false;
	} //12:21 PM, 26/04/2011
	$date_time	=	strip_tags($date_time);
	
	$date_time	=	explode(',',$date_time);	
	$date		=	explode('/',$date_time[1]);
	
	$date		=	$date[2].'-'.$date[1].'-'.trim($date[0]);
	$time		=	trim($date_time[0]);
	$date_time	=	$date.' '.$time;
	$content_date	=	$date_time;
	
	// get image full not in block
	$reg_image	=	'/<div\s*class="story-photo">\s*<p class="photo">\s*<img[^>]*src="([^"]*)"[^>]*>\s*<\/p>\s*<\/div>/ism';
	if (preg_match($reg_image,$response,$matches)) {
		$link_image		=	$matches[1];
		$tag_images			=	'<img src="'.$link_image.'" title="'.$title.'" alt="'.$title.'" style="float:right;" />';
		$intro	=	$tag_images. $intro;
	}
	
	$href		=	new href();
	$cat_link	=	$href->process_url($cat_link,'http://english.vovnews.vn');
	$obj_content			=	new stdClass();
	$obj_content->title		=	trim(str_replace('\r\n','',$title));
	$obj_content->intro		=	trim(str_replace('\r\n','',mostidy_clean($intro)));
	$obj_content->fulltext	=	trim(str_replace('\r\n','',mostidy_clean($full_text)));
	$obj_content->cat_link	=	$cat_link;
	$obj_content->cat_title	=	trim(str_replace('\r\n','',$cat_title));
	$obj_content->link		=	$link;
	$obj_content->alias		=	$alias;
	$obj_content->content_date		=	$content_date;
	$obj_content->PageHTML	=	$response;	
	return $obj_content;
}

function mosModelNewsvovProcessOther($str_in, $SiteID,$id_original)
{
	global $database,$error;
	$db	=	$database;
	$reg_link_other = '/<a.*?href=["\' ]*([^"\' ]*)["\' ]*.*?>(.*?)<\/a>/ism';
	$reg_id_other = '/\/(\d+)\.vov/ism';
	$href	=	new href();
	$root	=	'http://vov.vn';
	
	if (!preg_match_all($reg_link_other,$str_in,$matches_link)) {
		return $str_in;
	}
	
	for ($i=0; $i< count($matches_link[0]); $i++)
	{		
		$link	=	$href->process_url($matches_link[1][$i], $root);
		
		if (!preg_match($reg_id_other, $link,$matches)) {
			continue;
		}
		$id_orgional_other	=	$matches[1];		
		
		if ($id_result	=	mosModelNewsvovSaveOther($SiteID, $id_original, $matches[1],$matches_link[0][$i], $link)) {
			$title		=	strip_tags($matches_link[2][$i]);
			$link_content = '<a title="'.str_replace('&gt;','',$title).'" href="/index.php?option=com_content&task=view&id=' . $id_result.'" >'.$title."</a>";
			$str_in		=	str_replace($matches_link[0][$i],$link_content,$str_in);
		}	
	}
	
	return $str_in;
}

function mosModelNewsvovSaveOther ($SiteID,$id_original,$id_original_other,$str_replace,$link_other)
{
	global $database,$error;
	
	$db	=	& $database;
	$query = "SELECT * FROM #__article2010_new_vov WHERE id_original = ".trim($id_original_other);
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

function mosModelNewsvovProcessCategory(& $content)
{
	global $database, $arrErr;
	$db	=	$database;
	$catTitle	=	$content->cat_title;
	
	$db	=	$database;
	$query	=	' SELECT secid,catid '.
				' FROM #__article2010_category_vov '.
				' WHERE title = '.$db->quote($catTitle).
				' 	AND getcontent = 1';
	$db->setQuery($query);	
	
	if (!$db->loadObject($obj)) {
		$content->secid	=	0;
		$content->catid	=	0;
		return true;
	}
	$content->secid	=	$obj->secid;
	$content->catid	=	$obj->catid;
	return true;	
}
function mosModelNewsSave($content, $section_id = 1, $catid = 1, $SiteID = 'vn10')
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
	$row->SiteName		=	'vovnews';	
	$row->Domain		=	'vovnews.vn';	
	$row->SourceURL		=	$content->link;
	$row->created		= 	date("Y-m-d H:i:s",strtotime($content->content_date));	
	$row->title			=	$content->title;
	$row->title_alias	=	$content->alias;
	$row->introtext		=	$content->intro;
	$row->fulltext		=	$content->fulltext;
	$row->sectionid		=	$content->secid;
	$row->catid			=	$content->catid;
	$row->CatName		=	$content->cat_title;
//	$row->catid_original=	$content->catid_original;
	$row->PageHTML 		=	$content->PageHTML;
			
	$fmtsql = "INSERT INTO `#__article2010_new_vov` SET %s ON DUPLICATE KEY UPDATE  %s  ";
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
	mosUpdateOther($content->id_content,$content->title,$id,'#__article2010_new_vov');
	return true;
}

function mosModelNewsSaveMedia($arr_media,$contenid, $SiteID = 'vn10')
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

