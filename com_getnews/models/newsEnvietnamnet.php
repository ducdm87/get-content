<?php


function mosModelEnvietnamnetGetData($get_existing = true)
{	
	global $arrErr,$database, $mosConfig_live_site;
	$db = $database;
	
	$arr_obj 	=	getCatFromData();	
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

	$data_content = mosModelNewsEnvietnamnetGetListContent($obj_cat->alias_origional,$page);
	
	$arr_ID		=	$data_content->arr_link;

	$arr_result	=	array();

	if ($get_existing==false and is_array($arr_ID) and count($arr_ID)>0) {
		$_id	=	implode(',',$arr_ID);
		$query = "SELECT id_original 
					FROM #__article2010_new_vietnamnet_en 
					WHERE id_original in($_id)
						AND Domain = ".$db->quote('en.vietnamnet.vn');
		$db->setQuery($query);
		$arr_result	=	$db->loadResultArray();		
		
		$arr_ban	=	mosBanidGet('en.vietnamnet.vn',array('id_origional'),"id_origional  in($_id)");
		
		if (count($arr_result)) {
			if (count($arr_ban)) {
				$arr_result	=	array_merge($arr_result,$arr_ban);
			}
		}else {
			$arr_result	=	$arr_ban;
		}
	}
	$option	=	$_REQUEST['option'];	

	$number	=	count($arr_ID);
	$number_getContent	=	0;
	for ($i = 0; $i < $number ; $i++)
	{
		if ($get_existing==false && in_array($arr_ID[$i],$arr_result))
		{
			array_push($arrErr,'#444 '.$arr_ID[$i].' is existing');
			continue;
		}
		$id_content	=	$arr_ID[$i];
		$link		=	$mosConfig_live_site."/index.php?option=$option&task=getnewsvnnet.en&catid_origional=$obj_cat->id_origional";		
		$web 		=	parse_url($link);
		$begin		=	md5('BEGIN_GET_CONTENT_VNNET');
		$end		=	md5('END_GET_CONTENT_VNNET');
		 
		$postdata	=	$web['query'];
		$postdata	.=	'&begin_get_content='.$begin;
		$postdata	.=	'&end_get_content='.$end;
		$postdata	.=	'&catalias_origional='.$obj_cat->alias_origional;
		$postdata	.=	'&cattitle_origional='.$obj_cat->title;
		$postdata	.=	'&conten_id='.$arr_ID[$i];

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
         	$message	=	'ERROR_GET_CONTENT_VNNET| #123 API false'.$info;
         	array_push($arrErr,$message);
            continue;
         }
      	 if (stristr($info,'ERROR_GET_CONTENT_VNNET')) {
  	 		$message	=	'ERROR_GET_CONTENT_VNNET| '.$info;
     		array_push($arrErr,$message);
            continue;
         }
         $number_getContent	=	$number_getContent + 1;         
	}
	
	if ($data_content->isNext == false) {
		$query	=	'UPDATE `#__article2010_category_vietnamnet` 
					SET `last_run` = '.$db->quote(date ( 'Y-m-d H:i:s' )).', 
						`lastGet_param` = '.$db->quote("getold=0;page=1;").' 
					WHERE `id` ='. $obj_cat->id;
		$arr_obj[0]->isNext	=	false;
	}else {
		$query	=	'UPDATE `#__article2010_category_vietnamnet` 
					SET `last_run` = '.$db->quote(date ( 'Y-m-d H:i:s' )).', 
						`lastGet_param` = '.$db->quote("getold=$getold;page=$page;").'
					WHERE `id` ='. $obj_cat->id;
		$arr_obj[0]->isNext	=	true;
	}
	
	$db->setQuery($query);
/**	echo $db->getQuery();
	var_dump($arr_obj);**/
	$db->query();	
	if (count($arr_obj>1)) {
		$arr_obj[0]->number_getcontent	=	$number_getContent;
		$arr_obj[0]->page	=	$page;
		return $arr_obj;
	}else {
		return false;
	}	
}

function getCatFromData()
{
	global $database;
	$db	=	& $database;
	if (isset($_REQUEST['alias_origional'])) {
		$alias_origional	=	$_REQUEST['alias_origional'];
		$query = "SELECT *
			FROM `#__article2010_category_vietnamnet`
			WHERE publish = 1 and `isparent` = 0  and alias_origional = ".$db->quote($alias_origional)."
			ORDER BY `last_run`
			LIMIT 0,2";	
		$db->setQuery($query);
		$obj	=	$db->loadObjectList();
		$obj[1]	=	$obj;
	}else {
		$query = "SELECT *
			FROM `#__article2010_category_vietnamnet`
			WHERE publish = 1 and `isparent` = 0
			ORDER BY `last_run`
			LIMIT 0,2";	
		$db->setQuery($query);
		$obj	=	$db->loadObjectList();
	}	
	
	return $obj;
}

// Lấy các link bài viết trong 1 category
function mosModelNewsEnvietnamnetGetListContent($cat_alias, $page = '')
{	
	global $arrErr,$database, $mosConfig_live_site;
	if ($page != '') {
		$page = $page.'/';
	}
	$link		=	'http://english.vietnamnet.vn/en/'.$cat_alias.'/page'.$page.'index.html';
	$obj		=	loadHtml($link);
	$source 	=	$obj->innertext;
	$reg 		= 	'/<div class="body680">(.*?)<\/div>\s*<div class="body310">/ism';
	
	if (!preg_match($reg,$source,$matches_source)) {
		$message	=	'#361 models news mosModelNewsGetNews. Invalid get id of news content';
		array_push($arrErr,$message);
		return false;
	}

	$reg		=	'/<a class="hc-title" href="([^"]+)">.*?/ism';
	if (!preg_match_all($reg,$matches_source[1],$matches_link)) {
		$message	=	'#361 models news mosModelNewsGetNews. Invalid get id of news content';
		array_push($arrErr,$message);
		return false;
	}
	
	$list_article = $matches_link[1];
	$arr_link_article = array();
	for ($i=0;$i<count($list_article);$i++)
	{
		
		$arr_link_item = explode('/',$list_article[$i]);
		$arr_link_article[] = $arr_link_item[3];
	}
	$obj_return	=	new stdClass();
	$obj_return->arr_link	=	$arr_link_article;
	
	$next = '/<a[^>]*id="ctl00_ContentPlaceHolder1_PanelCateContent1_hplNext"[^>]*>/ism';
	
	$isnext		=	$obj->find('a[id="ctl00_ContentPlaceHolder1_PanelCateContent1_hplNext"]');	
	
	if (count($isnext)) {
		$obj_return->isNext		=	true;
		$obj_return->page		=	intval($page);		
	}else {
		$obj_return->isNext		=	false;
	}
	
	return $obj_return;
}


function mosModelNewsEnvietnamnetGetVNNET($id_content, $catalias_origional, $cattitle_origional, $section_id, $catID, $path_image = 'images', $link_image ='images', $SiteID ='vne20')
{
	global $arrErr;
	
	$link_content	='http://english.vietnamnet.vn/en/'.$catalias_origional.'/'.$id_content.'/vietnamnet.html';	
		
	if (!$content	=	mosModelNewsEnvietnamnetGetContent($link_content, $id_content, $catalias_origional))
	{
		$message_ban	=	$arrErr[count($arrErr)-1];		
		$message	=	'#389 models news mosModelNewsGetVOV. Not get content.'.$id_content;
		array_push($arrErr,$message);
		mosBanidStore('en.vietnamnet.vn','',$id_content,1,$message_ban."\r\n".$message);
		return false;
	}
	
	$content->intro		=	mosModelNewsEnvietnamnetProcessOther($content->intro,$SiteID,$id_content);
	$content->fulltext	=	mosModelNewsEnvietnamnetProcessOther($content->fulltext,$SiteID,$id_content);
	
	$root	=	'http://english.vietnamnet.vn';
	$arr_Images	=	array();
	
	mosGetImages($content,$root, $arr_Images,$path_image, $link_image, array('_referer'=>'image.english.vietnamnet.vn'));
//	var_dump($arr_Images); die();
	$content->arr_image			=	$arr_Images;
	$content->catid_original	=	$catid_origional;
	$content->cattitle_origional=	$cattitle_origional;
	
	if (!mosModelNewsEnvietnamnetSave($content,$section_id , $catid, $SiteID)) {
		$message	=	'#391 models news mosModelNewsGetVOV. Not save content.'.$id_content;
		array_push($arrErr,$message);
		return false;
	}
	return $content->PageHTML;
}

function cuttext($str_in,$length)
{
	$str_in = strip_tags($str_in);
	$_str_out = preg_split('/\s+/',$str_in,$length+1);	
	$_str_out = array_slice($_str_out,0,$length);
	
	$str_out = implode(' ',$_str_out);
	
	if (strlen($str_in) > strlen($str_out)) {
		$str_out .= ' ... ';
	}
	return $str_out;
}

function mosCountWord($str_in)
{
	$str_in = strip_tags($str_in);
	$_str_out = preg_split('/\s+/',$str_in);	
	return count($_str_out);
}

// Lấy thông tin bài viết thông qua cat_alias và id bài viêt
function mosModelNewsEnvietnamnetGetContent($link, $id_content, $catalias_origional)
{
	global $arrErr,$database;
	$db	=	$database;
	$content	=	loadHtml($link);
	
	$date_content = $content->find('div[class="content-update"]',0);
	$date_content = $date_content->innertext;
	
	$html	=	$content->find('div[id="content"]',0);	
	
	$response 	= 	preg_replace('/(<TABLE[^>]*>|<\/TABLE>|<TR[^>]*>|<\/TR>|<TBODY>|<\/TBODY>|<TD[^>]*>|<\/TD>)/ism','',$html->outertext);	

// get title
	$title 		=	$content->find('div[class="content-title"]',0);
	$title		=	$title->innertext;
	
	$reg_remove	=	'/(<div style="text-align: left;">\s*<!--\[if gte mso 9\]>.*?<!\[endif\]-->\s*<\/div>)/ism';
	$response 	= 	preg_replace($reg_remove,'',$response);
	$response 	=	str_replace('VietNamNet Bridge','',$response);
	$response 	=	str_replace('VietNamNet','',$response);
	$response 	=	str_replace('Bridge','',$response);
	$response	=	preg_replace('/<span[^>]*>Source[^<]+<\/span>/ism','',$response);
	
//	Source
// get alias
	$href		=	new href();
	$alias		=	$href->take_file_name($title);	
	
// get intro
	$obj_html	=	loadHtmlString($response);
	
	$arr_child	=	$obj_html->find('div[id="content"]',0)->children;
	
	$intro		=	'';	
	$full_text	=	'';
	$str		=	'';
	
	for ($i=0; $i<count($arr_child); $i++)
	{		
		$str	=	$str.$arr_child[$i]->outertext;
		$str	=	strip_tags($str);
		if (mosCountWord($str)>10) {			
			break;
		}
	}
	
	
	$arr_intro	=	array_splice($arr_child,0,$i+1);
	$intro		=	implode(' ',$arr_intro);
	
	if ($n = count($arr_child) - $i -1) {
		$arr_full	=	array_splice($arr_child,$i+1,$n);
		$full_text	=	implode(' ',$arr_full);
	}	
	$intro = strip_tags($intro);
	$intro	=	trim($intro);
	
// get image full not in block
	$reg_image	=	'/<img[^>]*src="([^"]*)"[^>]*(>|\/>)/ism';
	if (preg_match($reg_image,$response,$matches)) {
		$link_image		=	$matches[1];
		$response = str_replace($matches[0],'',$response);
		$tag_images			=	'<img src="'.$link_image.'" title="'.$title.'" alt="'.$title.'" style="float:right;" />';
		$intro	=	$tag_images. $intro;
	}
	$full_text	= str_replace($matches[0],'',$full_text);
	
	$full_text	=	preg_replace('/<a[^>]*href="[^"]*english\.vietnamnet\.vn[^"]*"[^>]*>(.*?)<\/a>/ism','$1',$full_text);
	
	// get metadesc
	
	
// get date
	$date_content = preg_replace('/(Last update|\(GMT\+7\))/ism','',$date_content);
	$date_content = trim($date_content);
	$date_content = preg_replace('/\s/ism','|',$date_content);
	$arr_time = explode('|',$date_content);
	
	$date = $arr_time[0];
	$time = $arr_time[1];
	$type_time = $arr_time[2];
	
	$arr_time = explode(':',$time);
	$hour = $arr_time[0];
	$minutes = $arr_time[1];
	$seconds = $arr_time[2];
	
	if($type_time == 'PM'){
		$hour = $arr_time[0] + 12;
	}
	
	$arr_day = explode('/',$date);
	$day = $arr_day[0];
	$month = $arr_day[1];
	$year = $arr_day[2];

	$day_now = mktime($hour,$minutes,$seconds,$month,$day,$year);
	$created = strftime('%Y-%m-%d %H:%M:00',$day_now);

// url_source	
	$url_source = 'http://english.vietnamnet.vn/en/'.$catalias_origional.'/'.$id_content.'/'.$alias.'.html';
	
	$obj_content			=	new stdClass();	
	$obj_content->title		=	trim(str_replace("\r\n",'',$title));
	$obj_content->intro		=	mostidy_clean(trim(str_replace("\r\n",'',$intro)));
	$obj_content->fulltext	=	mostidy_clean(trim(str_replace("\r\n",'',$full_text)));
	$obj_content->SourceURL	=	$url_source;
	$obj_content->alias		=	$alias;
	$obj_content->content_date		=	$created;
	$obj_content->id_content=	$id_content;	
	$obj_content->PageHTML	=	$content->innertext;

	return $obj_content;
}

function mosModelNewsEnvietnamnetProcessOther($str_in, $SiteID,$id_original,$catname = null)
{	
	global $database,$error;
	$db	=	$database;
//	7910/vietnamese-mothers-prefer-bringing-up-children-with-western-style.html
	$reg_link_other = '/<a.*?href=["\' ]*([^"\' ]*)["\' ]*.*?>(.*?)<\/a>/ism';
	$reg_id_other = '/\/(\d+)\/[\w\d-]+\.html/ism';
	$href	=	new href();
	$root	=	'http://english.vietnamnet.vn';
	
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
		
		if ($id_result	=	mosModelNewsEnvietnamnetSaveOther($SiteID, $id_original, $matches[1],$matches_link[0][$i], $link, $catname)) {
			$title		=	strip_tags($matches_link[2][$i]);
			$link_content = '<a title=\''.str_replace(array('&gt;','\''),array(' ','"'),$title).'\' href="/index.php?option=com_content&task=view&id=' . $id_result.'" >'.$title."</a>";
			$str_in		=	str_replace($matches_link[0][$i],$link_content,$str_in);
		}	
	}
	return $str_in;
}

function mosModelNewsEnvietnamnetSaveOther ($SiteID,$id_original,$id_original_other,$str_replace,$link_other,$cat_name)
{
	global $database,$error;
	
	$db	=	& $database;
	$query = "SELECT * FROM #__article2010_new_vietnamnet_en WHERE id_original = ".trim($id_original_other);
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

function mosModelNewsEnvietnamnetSave($content, $section_id = 1, $catid = 1, $SiteID = 'vnn10')
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
	$row->SiteName		=	'en.vietnamnet.vn';	
	$row->Domain		=	'en.vietnamnet.vn';	
	$row->SourceURL		=	$content->SourceURL;
	$row->created		= 	date("Y-m-d H:i:s",strtotime($content->content_date));	
	$row->title			=	$content->title;
	$row->title_alias	=	$content->alias;
	$row->introtext		=	$content->intro;
	$row->fulltext		=	$content->fulltext;
	$row->sectionid		=	$section_id;
	$row->catid			=	$catid;
	$row->CatName		=	$content->cattitle_origional;
	$row->PageHTML 		=	$content->PageHTML;

	$fmtsql = "INSERT INTO `#__article2010_new_vietnamnet_en` SET %s ON DUPLICATE KEY UPDATE  %s  ";
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

	mosModelNewsEnvietnamnetSaveSaveMedia($content->arr_image,$id,$SiteID);
	mosUpdateOther($content->id_content,$content->title,$id,'#__article2010_new_vietnamnet_en');
	return true;
}

function mosModelNewsEnvietnamnetSaveSaveMedia($arr_media,$contenid, $SiteID = 'vne20')
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
		$row->SourceURL			=	$media->SourceURL;	
		$row->Size				=	$media->Size;	
		$row->FileName			=	$media->FileName;	
		$row->Path				=	$media->Path;	
		$row->FileType			=	$media->FileType;	
		$row->MediaType			=	$media->MediaType;
		if (! $row->store ()) {
			$message	=	'#562 models news mosModelNewsEnvietnamnetSaveSaveMedia. Invalid store media for '.$media->Path.'. Link: '.$media->media_url.' sql error: '.$row->getError ();
			array_push($arrErr,$message);				
		}
	}
	return true;
}

