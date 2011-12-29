<?php


function mosModelNewsEnvovGetNews(& $lastGet_vovId, $max_number = 1000, $get_existing = true)
{
	
	global $arrErr,$database, $mosConfig_live_site;
	
	if (isset($_REQUEST['id_content']) and $_REQUEST['id_content'] >0) {
		$id_content	=	intval($_REQUEST['id_content']);
		$number		=	$max_number;
	}else {
		$id_content	=	mosModelNewsEvvovGetNewId();
		$number	=	$id_content - $lastGet_vovId;		
		$number	=	$number<$max_number?$number:$max_number;
	}
	
	$id_result		=	$id_content	-	$number;	
	$lastGet_vovId	=	$id_content;	
	$arr_ID			=	array();
	$arr_result		=	array();
	$arr_geted		=	array();
	$arr_ban		=	array();
	
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
					FROM #__article2010_new_vov_en 
					WHERE id_original in($_id)";
		$db->setQuery($query);
		$arr_result	=	$db->loadResultArray();
		$arr_ban	=	mosBanidGet('en.vovnews.vn',array('id_origional'),"id_origional  in($_id)");
		$arr_geted	=	mosGetDBOBJ('#__article2010_totalcontent',array('id_origional'),'id_origional  in('.$_id.') AND SiteID='.$db->quote('ve10'));
		
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
	$number_getContent	=	0;	
	$browser	=	new phpWebHacks();
echo '<hr /> $arr_ID: '. count($arr_ID);
echo '<hr /> $arr_result: '. count($arr_result);
echo '<hr /> $arr_ban: '. count($arr_ban);
echo '<hr /> $arr_geted: '. count($arr_geted);
echo '<hr />';
	$n	=	count($arr_ID) == count($arr_result)?0:count($arr_ID);
//	die();
	for ($i = 0; $i < $n ; $i++)
	{
		if ($get_existing==false && in_array($arr_ID[$i],$arr_result))
		{
			array_push($arrErr,'#444 '.$arr_ID[$i].' is existing');
			continue;
		}
		
		$id_content	=	$arr_ID[$i];
		
		$url		=	$mosConfig_live_site."/index.php?option=$option";		
		
		$begin		=	md5('BEGIN_GET_CONTENT_VOV');
		$end		=	md5('END_GET_CONTENT_VOV');
		
		$arr_post	=	array();
		$arr_post['begin_get_content']	=	$begin;
		$arr_post['end_get_content']	=	$end;
		$arr_post['task']				=	'getnewsvov.en';		
		$arr_post['content_id']			=	$arr_ID[$i];	
		
//		echo $url;
//		echo '<hr />';
//		$a=	array();
//		foreach ($arr_post as $k=>$v) {
//			$a[] = "$k=$v";
//		}
//		echo implode('&',$a);
//		die();
		
		$info	=	$browser->post($url,$arr_post);		
		     
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
         if (stristr($info,'NOTICE_GET_CONTENT_VOV')) {
         	
  	 		$message	=	' &nbsp; '. $id_content. ' &nbsp; '.$info;
     		array_push($arrErr,$message);            
         }
         $number_getContent	=	$number_getContent + 1;
	}	
	
	$obj_result	=	new stdClass();
	$obj_result->number_getcontent	=	$number_getContent;
	$obj_result->id_result			=	$id_result;
	return $obj_result;
}

function mosModelNewsEvvovGetNewId()
{
	global $arrErr,$database, $mosConfig_live_site;
	$url		=	'http://english.vovnews.vn/';
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
function mosModelNewsEnvovGetVOV($id_content, $section_id , $catid , $path_image = 'images', $link_image,$SiteID = 've10')
{
	global $arrErr;
	$link_content	=	'http://english.vovnews.vn/Home/alias-vov/20114/'.$id_content.'.vov';
	
	if (!$content	=	mosModelNewsEnvovGetContent($link_content,$id_content))
	{
		$message_ban	=	$arrErr[count($arrErr)-1];		
		$message	=	'#389 models newsEnvov mosModelNewsEnvovGetVOV.<b> Not get content</b>.'.$id_content;
		array_push($arrErr,$message);
		mosBanidStore('en.vovnews.vn','',$id_content,1,$message_ban."\r\n".$message);
		return false;
	}
	
	$content->intro		=	mosModelNewsEnvovProcessOther($content->intro,$SiteID, $id_content);
	$content->fulltext	=	mosModelNewsEnvovProcessOther($content->fulltext,$SiteID, $id_content);
	
	if(!mosModelNewsvovProcessCategory($content))
	{
		$message	=	'#391 models news mosModelNewsEnvovGetVOV. Invalid process category or this category is not get content. id content: '.$id_content.', cat: '. $content->cat_title;
		array_push($arrErr,$message);
		return false;
	}
	
	$root	=	'http://english.vovnews.vn';
	$arr_Images	=	array();
	
	mosGetImages($content, $root, $arr_Images, $path_image, $link_image);	
	
	$content->arr_image		=	$arr_Images;
	$content->id_content	=	$id_content;
	if (!mosModelNewsEnvovSave($content,$section_id , $catid, $SiteID)) {
		$message	=	'#391 models newsEnvov mosModelNewsGetVOV. <b>Not save content</b>.'.$id_content;
		array_push($arrErr,$message);
		return false;
	}
}

function mosModelNewsEnvovGetContent($link,$id_content)
{
	global $arrErr,$database;	
	$db	=	$database;	
	$browser	=	new phpWebHacks();
	if (!$response	=	$browser->get($link)) {
		$message	=	'#832 models news mosModelNewsGetContent. Invalid get article '.$link;
		array_push($arrErr,$message);
		return false;
	}	
	$link		=	$browser->get_addressbar();	
	
	$html	=	loadHtmlString($response);
	// get title
	$title	=	$html->find('h1[class="title"]',0)->innertext;
	$title	=	strip_tags($title);
	
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
	if($html->find('div[class="story-photo"]',0))
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
	
	$obj_content			=	new stdClass();
	$obj_content->title		=	trim(str_replace("\r\n",'',$title));
	$obj_content->intro		=	trim(str_replace("\r\n",'',mostidy_clean($intro)));
	$obj_content->fulltext	=	trim(str_replace("\r\n",'',mostidy_clean($full_text)));	
	$obj_content->cat_link	=	$cat_link;
	$obj_content->cat_title	=	trim(str_replace('\r\n','',$cat_title));
	$obj_content->link		=	$link;
	$obj_content->alias		=	$alias;
	$obj_content->content_date		=	$content_date;
	$obj_content->PageHTML	=	$response;
	
	return $obj_content;
}

function mosModelNewsEnvovProcessOther($str_in, $SiteID,$id_original)
{
	global $database,$error;
	$db	=	$database;
	$reg_link_other = '/<a.*?href=["\' ]*([^"\' ]*)["\' ]*.*?>(.*?)<\/a>/ism';
	$reg_id_other = '/\/(\d+)\.vov/ism';
	$href	=	new href();
	$root	=	'http://english.vovnews.vn';
	
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
		
		$title	=	strip_tags($matches_link[2][$i]);
		if ($id_result	=	mosModelNewsEnvovSaveOther($SiteID, $id_original, $matches[1],$matches_link[0][$i], $link, $catname)) {
			$link_content = '<a title="'.str_replace('&gt;','',$title).'" href="/index.php?option=com_content&task=view&id=' . $id_result.'" >'.$title."</a>";
			$str_in		=	str_replace($matches_link[0][$i],$link_content,$str_in);
		}	
	}
	return $str_in;
}

function mosModelNewsEnvovSaveOther ($SiteID,$id_original,$id_original_other,$str_replace,$link_other,$cat_name)
{
	global $database,$error;
	
	$db	=	& $database;
	$query = "SELECT * FROM #__article2010_new_vov_en WHERE id_original = ".trim($id_original_other);
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

	$param = '';	
	$result	=	mosOtherStore($SiteID, $id_original, $id_original_other, $str_replace, $link_other, $type, $state);
	
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
				' 	AND getcontent = 1 AND id >42';
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

function mosModelNewsEnvovSave($content, $section_id = 1, $catid = 1, $SiteID = 've10')
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
	$row->SiteName		=	'en.vovnews';	
	$row->Domain		=	'en.vovnews.vn';	
	$row->SourceURL		=	$content->link;
	$row->created		= 	date("Y-m-d H:i:s",strtotime($content->content_date));	
	$row->title			=	$content->title;
	$row->title_alias	=	$content->alias;
	$row->introtext		=	$content->intro;
	$row->fulltext		=	$content->fulltext;
	$row->sectionid		=	$content->secid;
	$row->catid			=	$content->catid;
	$row->CatName		=	$content->cat_title;	
	$row->PageHTML 		=	$content->PageHTML;

	$fmtsql = "INSERT INTO `#__article2010_new_vov_en` SET %s ON DUPLICATE KEY UPDATE  %s  ";
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
	mosModelNewsEnvovSaveMedia($content->arr_image,$id,$SiteID);
	$obj	=	new stdClass();
	$obj->SiteID	=	$SiteID;
	$obj->aid	=	$id;
	$obj->id_origional	=	$row->id_original;	
	mosStoreOBJ('#__article2010_totalcontent',$obj);	
	mosUpdateOther($content->id_content,$content->title,$id,'#__article2010_new_vov_en');
	return true;
}

function mosModelNewsEnvovSaveMedia($arr_media,$contenid, $SiteID = 've10')
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
			$message	=	'#562 models newsEnvov mosModelNewsEnvovSaveMedia.<b> Invalid store media</b> for '.$media->Path.'. Link: '.$media->media_url.' sql error: '.$row->getError ();
			array_push($arrErr,$message);				
		}
	}
	return true;
}