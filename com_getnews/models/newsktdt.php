<?php

function mosModelKtdtGetData($max_number = 1000, $get_existing = true)
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

	if ($param_param) {
		$query = "SELECT PageHTML 
					FROM #__article2010_new_ktdt 
					WHERE id_original = ". intval($param_param);
		$db->setQuery($query);

		$response	=	$db->loadResult();
		$html	=	'';
		echo $url	=	"http://www.ktdt.com.vn/newsdetail.asp?NewsId=$param_param&CatId=".$obj_cat->id_origional;
		
		if (!$response) {			
			$url	=	"http://www.ktdt.com.vn/newsdetail.asp?NewsId=$param_param&CatId=".$obj_cat->id_origional;	
			$html	=	loadHtml($url);
		}else {
			$html		=	loadHtmlString($response);
		}	
		
		$obj		= 	$html->find('table[id="table509"]',0);
		$response	= 	$obj->innertext;
		$arr_ID		=	getListContent($response);
	}else {		
		$url		=	"http://www.ktdt.com.vn/showcat.asp?CatId=$obj_cat->id_origional";
		
		$html		= 	loadHtml($url);
		
		$obj		= 	$html->find('div[id="VietAd"]',0);
		$response	= 	$obj->innertext;		
		$arr_ID		=	getListContent($response);
		$start		=	$arr_ID[0];		
	}	

	$arr_result	=	array();
	
	if ($get_existing==false) {
		$_id	=	implode(',',$arr_ID);
		$query = "SELECT id_original 
					FROM #__article2010_new_ktdt 
					WHERE id_original in($_id)";
		$db->setQuery($query);
		
		$arr_result	=	$db->loadResultArray();	
		$arr_ban	=	mosBanidGet('ktdt.com.vn',array('id_origional'),"id_origional  in($_id)");		
		$arr_geted	=	mosGetDBOBJ('#__article2010_totalcontent',array('id_origional'),'id_origional  in('.$_id.') AND SiteID='.$db->quote('kt18'));		

		if (count($arr_result)) 
		{
			if (count($arr_ban))	$arr_result	=	array_merge($arr_result,$arr_ban);			
		}
		else 
		{	
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
		
	$option	=	$_REQUEST['option'];
	$number_getContent	=	0;
	$number	=	count($arr_ID)>$max_number?$max_number:count($arr_ID);

	echo '<br /> ';
	echo '$arr_result: ';	echo count($arr_result);
	echo '<br /> ';
	echo '$arr_ID: ';	echo count($arr_ID);
	echo '<br /> [$arr_ID] : '. count($arr_ID);
	die();
	for ($i = 0; $i < $number ; $i++)
	{
		if ($get_existing==false && in_array($arr_ID[$i],$arr_result))
		{
			array_push($arrErr,'#444 '.$arr_ID[$i].' is existing');
			continue;
		}
		
		$id_content	=	$arr_ID[$i];
		$link		=	$mosConfig_live_site."/index.php?option=$option&task=getnewsktdt";

		$web 		=	parse_url($link);
		$begin		=	md5('BEGIN_GET_CONTENT_KTDT');
		$end		=	md5('END_GET_CONTENT_KTDT');
		// echo $link;
		$postdata	=	$web['query'];
		$postdata	.=	'&begin_get_content='.$begin;
		$postdata	.=	'&conten_id='.$arr_ID[$i];
		$postdata	.=	'&end_get_content='.$end;
		$postdata	.=	'&catid_origional='.$obj_cat->id_origional;
		$postdata	.=	'&cattitle_origional='.$obj_cat->title;
		$postdata	.=	'&secid='.$obj_cat->secid;
		$postdata	.=	'&catid='.$obj_cat->catid;
//echo $link;
//echo '<hr />';
//echo $postdata;
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
         	$message	=	'ERROR_GET_CONTENT_VOV| #123 API false'.$info;
         	array_push($arrErr,$message);
            continue;
         }
      	 if (stristr($info,'ERROR_GET_CONTENT_VOV')) {
  	 		$message	=	'ERROR_GET_CONTENT_VOV| '.$info;
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
	$query	=	'UPDATE `#__article2010_category` 
						SET `last_run` = '.$db->quote(date ( 'Y-m-d H:i:s' )).', 
							`lastGet_param` = '.$db->quote($param).'
						WHERE `id` ='. $obj_cat->id;
	
	$arr_obj[0]->number_getcontent	=	$number_getContent;
	
	$db->setQuery($query);
	
	$db->query();
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
	
	$query = "SELECT *
			FROM `#__article2010_category`";
	$db->setQuery($query);
	$items = $db->loadObjectList();
	$root	=	'http://www.ktdt.com.vn';
	$href = new href();
	for ($i=0;$i<count($items); $i++)
	{
		$item = $items[$i];
		preg_match('/start=(\d*)param=(\d*)end=(\d*)/ism',$item->lastGet_param,$matches_param);
		$param_start	=	$param_param	=	$param_end	=	0;
		if(isset($matches_param[1]))
			$param_start	=	$matches_param[1];
		if(isset($matches_param[2]))
			$param_param	=	$matches_param[2];
		if(isset($matches_param[3]))
			$param_end	=	$matches_param[3];	
			
		$alias = strtolower($href->take_file_name($item->title));
		$link = "http://www.ktdt.com.vn/news/$item->id_origional/$alias.aspx";
		$param = "start=$param_start"."param=0end=$param_end";
		$query = 'update `#__article2010_category` set lastGet_param = '. $db->quote($param).', domain = '.$db->quote($link).' where id = '.$item->id;
		$db->setQuery($query);
		echo $db->getQuery();
		$db->query();
		echo '<hr />';
	}
	die();
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	if (isset($_REQUEST['catid_origional'])) {
		$arr_obj	=	array();
		$id_origional	=	$_REQUEST['catid_origional'];
		$query = "SELECT *
			FROM `#__article2010_category`
			WHERE publish = 1 and `isparent` = 0  and id_origional = $id_origional
			ORDER BY `last_run`
			LIMIT 0,1";	
		$db->setQuery($query);
		$db->loadObject($obj);
		$arr_obj[0]	=	$obj;
		$arr_obj[1]	=	$obj;
	}else {
		$query = "SELECT *
			FROM `#__article2010_category`
			WHERE publish = 1 and `isparent` = 0
			ORDER BY `last_run`
			LIMIT 0,2";	
		$db->setQuery($query);
		$arr_obj	=	$db->loadObjectList();
	}
	
	return $arr_obj;
}

function getListContent($str_html)
{
	$reg		=	'/<a\s*(class="menu_sub4"|\s*)\s*href="[^"]*NewsId=(\d+)&CatId=\d+"\s*(class="title1"|\s*)\s*>.*?<\/a>/ism';		
	preg_match_all($reg,$str_html,$matches);	
	
	$arr_link = array();
	for ($i=0;$i<count($matches[1]);$i++)
	{	
		$arr_link[] = $matches[2][$i];
	}
	
	return $arr_link;
}

function mosModelNewsGetKTDT($id_content, $catid_origional, $cattitle_origional, $section_id, $catid, $path_image = 'images', $link_image ='images', $SiteID ='kt18')
{
	global $arrErr;
	
	$link_content	=	"http://www.ktdt.com.vn/newsdetail.asp?NewsId=$id_content&CatId=$catid_origional";
	
	if (!$content	=	mosModelNewsktdtGetcontent($link_content, $id_content))
	{
		$message_ban	=	$arrErr[count($arrErr)-1];		
		$message	=	'#389 models news mosModelNewsGetVOV. Not get content.'.$id_content;
		array_push($arrErr,$message);
		mosBanidStore('ktdt.com.vn','',$id_content,1,$message_ban."\r\n".$message);
		return false;
	}
	$content->intro		=	mosModelNewsEnktdtProcessOther($content->intro,$SiteID, $id_content);
	$content->fulltext	=	mosModelNewsEnktdtProcessOther($content->fulltext,$SiteID, $id_content);
	
	$root	=	'http://www.ktdt.com.vn';
	$arr_Images	=	array();
	
	mosGetImages($content,$root, $arr_Images,$path_image, $link_image);
	
	$content->arr_image			=	$arr_Images;
	$content->catid_original	=	$catid_origional;
	$content->cattitle_origional=	$cattitle_origional;
	
	if (!mosModelNewsktdtSave($content,$section_id , $catid, $SiteID)) {
		$message	=	'#391 models news mosModelNewsGetVOV. Not save content.'.$id_content;
		array_push($arrErr,$message);
		return false;
	}
	return $content->PageHTML;
}

function mosModelNewsktdtGetcontent($link, $id_content)
{
	global $arrErr;

	$html = loadHtml($link);
	$source = $html->innertext;
	$obj	= $html->find('div[id="VietAd"]',0);
	$content = $obj->innertext;
	
// Tìm title bài viết	
	$reg_title	=	'/<td[^>]+class="title_cap3"[^>]*>(.*?)<\/td>/ism';
	if (!preg_match($reg_title,$content,$title_mathces)) {
		$message	=	'#332 models newsbaomoi mosModelNewsbaomoiGetContent. Invalid get title for '.$link;
		array_push($arrErr,$message);
		return false;
	}
	$title	=	strip_tags($title_mathces[1]);
	
// Tìm title_alias bài viết	
	$href = new href();
	$title_alias = $href->convertalias($title);
	
// Tìm thời gian
	$reg_time	=	'/<td[^>]+class="title_cap3_1"[^>]*><p>(.*?)<\/p><\/td>/ism';
	if (!preg_match($reg_time,$content,$time_matches)) {
		$message	=	'#335 models newsbaomoi mosModelNewsbaomoiGetContent. Invalid get time for '.$link;
		array_push($arrErr,$message);
		return false;
	}
	$time	=	strtolower(strip_tags($time_matches[1]));
	$time = preg_replace('/(cập nhật lúc |ngày |\s*)/ism','',$time);
	$time = preg_replace('/(h|,)/ism','/',$time);
	$arr_time = explode('/',$time);
	
	$day_now = mktime($arr_time[0],$arr_time[1],0,$arr_time[3],$arr_time[2],$arr_time[4]);
	$created = strftime('%Y-%m-%d %H:%M:00',$day_now);
	
// Tìm introtext	
	$reg_title	=	'/<!--p class="title_cap3_2">(.*?)<\/p-->/ism';
	if (!preg_match($reg_title,$content,$intro_mathces)) {
		$message	=	'#336 models newsbaomoi mosModelNewsbaomoiGetContent. Invalid get introtext for '.$link;
		array_push($arrErr,$message);
		return false;
	}
	$introtext	=	str_replace('KTĐT - ','',strip_tags($intro_mathces[1]));	

	
//Tìm "Nguồn bài viết"	
	$reg_source	=	'/<p align="right" ><b><font color="#999999"><em>(.*?)<\/em><\/font><\/b><\/p>/ism';
	if (!preg_match($reg_source,$content,$source_mathces)) {
		$message	=	'#339 models newsbaomoi mosModelNewsbaomoiGetContent. Invalid get source for '.$link;
		array_push($arrErr,$message);		
	}
	$reg_source	=	$source_mathces[1];
	
//Tìm fulltext
	$fulltext 	= str_replace($introtext,'',$content);
	$fulltext 	= preg_replace('/<tr>\s*<td align="right">(.*?)<\/td>\s*<\/tr>/ism','',$fulltext);
	$fulltext 	= preg_replace('/(<font[^>]*>|<\/font>|<TABLE[^>]*>|<\/TABLE>|<TR[^>]*>|<\/TR>|<TBODY>|<\/TBODY>|<TD[^>]*>|<\/TD>|KTĐT -)/ism','',$fulltext);
	$fulltext	= str_replace($reg_source,'',$fulltext);
	
		
// Tìm ảnh		

	$content=	$html->find('td[class="textbody"]',0)->innertext;
	$reg_image	=	'/<img src="([^"]+)"[^>]*>/ism';
	if (preg_match($reg_image,$content,$img_matches)) {
		
		$link_image		=	$img_matches[1];
		$link_image		= 	$link_image;
		$tag_images		=	'<img src="'.$link_image.'" title="'.$title.'" alt="'.$title.'" style="float:left;" />';
		$introtext		=	$tag_images. $introtext;
		$fulltext		=	str_replace($img_matches[0],'',$fulltext);
	}
		
	$obj_content	=	new stdClass();
	$obj_content->id_content	=	$id_content;
	$obj_content->SourceURL		=	$link;
	$obj_content->content_date	= 	$created;	
	$obj_content->title			=	trim(str_replace("\r\n",' ',$title));
	$obj_content->title_alias	=	$title_alias;
	$obj_content->intro			=	mostidy_clean(trim(str_replace("\r\n",' ',$introtext)));
	$obj_content->fulltext		=	mostidy_clean(trim(str_replace("\r\n",' ',$fulltext)));
	$obj_content->PageHTML 		=	$source;
	
	return $obj_content;
}

function mosModelNewsEnktdtProcessOther($str_in, $SiteID,$id_original,$catname = null)
{
	global $database,$arrErr;
	$db	=	$database;
	$reg_link_other = '/<a.*?href=["\' ]*([^"\' ]*)["\' ]*.*?>(.*?)<\/a>/ism';
	$reg_id_other = '/[\w\d-_\/]*NewsId=(\d+)\&CatId=(\d+)/ism';
	$href	=	new href();
	$root	=	'http://www.ktdt.com.vn';	
	//$link = 'http://www.ktdt.com.vn/newsdetail.asp?NewsId=302546&CatId=140';
	//preg_match($reg_id_other, $link,$matches);
	//var_dump($matches);
		//die("1");
	if (!preg_match_all($reg_link_other,$str_in,$matches_link)) {
		return $str_in;
	}	
	for ($i=0; $i< count($matches_link[0]); $i++)
	{	
	$title		=	strip_tags($matches_link[2][$i]);
	
		$link	=	str_replace('&amp;','&',$href->process_url($matches_link[1][$i], $root)); 
		
		if (!preg_match($reg_id_other, $link,$matches)) {
			$title		=	strip_tags($matches_link[2][$i]);
			$str_in		=	str_replace($matches_link[0][$i],$title,$str_in);
			continue;
		}	
		if ($id_result	=	mosModelNewsEnktdtSaveOther($SiteID, $id_original, $matches[1],$matches_link[0][$i], $link, $matches[2])) {
			$title		=	strip_tags($matches_link[2][$i]);
			$link_content = '<a title=\''.str_replace(array('&gt;','\''),array(' ','"'),$title).'\' href="/index.php?option=com_content&task=view&id=' . $id_result.'" >'.$title."</a>";
			$str_in		=	str_replace($matches_link[0][$i],$link_content,$str_in);
		}	
	}
	return $str_in;
}

function mosModelNewsEnktdtSaveOther ($SiteID,$id_original,$id_original_other,$str_replace,$link_other,$catID)
{
	global $database,$arrErr;
	
	$db	=	& $database;
	$query = "SELECT * FROM #__article2010_new_ktdt WHERE id_original = ".trim($id_original_other);
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

function mosModelNewsktdtSave($data, $section_id = 1, $catid = 1, $SiteID = 'kt18')
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
	$row->SiteName		=	'ktdt.com.vn';	
	$row->Domain		=	'ktdt.com.vn';	
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

	$fmtsql = "INSERT INTO `#__article2010_new_ktdt` SET %s ON DUPLICATE KEY UPDATE  %s  ";
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
	
	mosModelNewsktdtSaveMedia($data->arr_image,$id,$SiteID);
	
	$obj	=	new stdClass();
	$obj->SiteID	=	$SiteID;
	$obj->aid	=	$id;
	$obj->id_origional	=	$row->id_original;	
	mosStoreOBJ('#__article2010_totalcontent',$obj);
	
	mosUpdateOther($content->id_content,$content->title,$id,'#__article2010_new_ktdt');
//	mosModelNewsbaomoiSaveParam($content,$row->id,$SiteID);	
	return true;
}

function mosModelNewsktdtSaveMedia($arr_media,$contenid, $SiteID = 'kt18')
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
			$message	=	'#562 models news mosModelNewsSaveMedia. Invalid store media for '.$media->Path.'. Link: '.$media->media_url.' sql error: '.$row->getError ();
			array_push($arrErr,$message);				
		}
	}
	return true;
}

