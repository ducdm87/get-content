<?php

function mosModelVirGetData($max_number = 1000, $get_existing = true)
{
	global $arrErr,$database, $mosConfig_live_site;
	$db = $database;

	if (!$arr_obj 	=	getCatFromData()) {
		return false;
	}
	if (count($arr_obj) < 2) {
		echo 'success';
		die();
	}
	$obj_cat	=	$arr_obj[0];
	$param		=	$obj_cat->lastGet_param;
	preg_match('/start=([^;]*);param=([^;]*);end=([^;]*);/ism',$param,$matches_param);
	
	$param_start	=	$param_param	=	$param_end	=	0;
	if(isset($matches_param[1]))
		$param_start	=	$matches_param[1];
	if(isset($matches_param[2]))
		$param_param	=	$matches_param[2];
	if(isset($matches_param[3]))
		$param_end	=	$matches_param[3];
	//	var_dump($obj_cat);		
//$param_param	=	'http://www.vir.com.vn/news/business/vietnam-to-increase-trained-workforce.html';

	$browser	=	new phpWebHacks();
	if ($param_param) {
		$query = "SELECT PageHTML 
					FROM #__article2010_new_vir 
					WHERE id_original = ". $db->quote(md5($param_param));
		$db->setQuery($query);		

		$response	=	$db->loadResult();
		echo $param_param;
		if (!$response) {				
			$response	=	$browser->get($param_param);			
			$html	=	loadHtmlString($response);
		}else {
			$html		=	loadHtmlString($response);
		}		
		$obj		= 	$html->find('div[class="box-3 Box_More_News"]',0);		
		$obj		= 	$obj->find('ul[class="content-more"]',0);		
		$response	= 	$obj->outertext;
//		echo $response;
//		die();
		$obj_return	=	getListContent($response);
	}else {		
		$response	=	$browser->get($obj_cat->link);
		$html		= 	loadHtmlString($response);
		
		$obj_1		= 	$html->find('div[class="all-items clearfix"]',0);
		$obj_2		= 	$html->find('ul[class="content-more clearfix"]',0);
		$response	= 	$obj_1->outertext . '<ul class="content-more">'.$obj_2->innertext.'</ul>';		
		$obj_return	=	getListContent($response);
		$start		=	$obj_return->arr_link[0];
	}	
	
	$links_code	=	$obj_return->arr_link;
	$arr_link	=	$obj_return->arr_link;
	
	$arr_result	=	array();

	if ($get_existing==false) {
		for ($i=0;$i<count($links_code); $i++)
		{
			$links_code[$i]	=	$db->quote(md5(trim($links_code[$i])));
			$arr_link[$i]	=	md5(trim($arr_link[$i]));
		}
		$_id	=	implode(',',$links_code);		
		$query = "SELECT id_original
					FROM #__article2010_new_vir 
					WHERE id_original in($_id)";
		$db->setQuery($query);
		
		$arr_result	=	$db->loadResultArray();

		$arr_ban	=	mosBanidGet('vir.com.vn',array('id_origional'),"id_origional  in($_id)");
	
		if (count($arr_result))
		{
			if (count($arr_ban))	$arr_result	=	array_merge($arr_result,$arr_ban);
		}
		else $arr_result	=	$arr_ban;
		$arr_result	=	is_array($arr_result)?$arr_result:array();
		$arr_result	=	array_unique($arr_result);	
	}
	$links_code	=	$arr_link;
	$arr_link	=	$obj_return->arr_link;
	$arr_title	=	$obj_return->arr_title;	
	
	echo " arr_link "; echo count($arr_link);
	echo '<hr />';
	echo " arr_result "; echo count($arr_result);	
	
	$option	=	$_REQUEST['option'];
	$number_getContent	=	0;	

	for ($i = 0; $i < count($arr_link) ; $i++)
	{
		if ($get_existing==false && in_array($links_code[$i],$arr_result))
		{
			array_push($arrErr,'#444 '.$arr_link[$i].' is existing');
			continue;
		}		
		$url		=	$mosConfig_live_site."/index.php?";
		
		$begin		=	md5('BEGIN_GET_CONTENT_KTDT');
		$end		=	md5('END_GET_CONTENT_KTDT');
		// echo $link;
		$arr_post	=	array();
		$arr_post['task']				=	'getnewsvir';
		$arr_post['option']				=	$option;
		$arr_post['link_content']		=	$arr_link[$i];		
		$arr_post['title_content']		=	$arr_title[$i];		
		$arr_post['secid']				=	$obj_cat->secid;
		$arr_post['catid']				=	$obj_cat->catid;
		$arr_post['cat_alias']			=	$obj_cat->alias_origional;
		$arr_post['begin_get_content']	=	$begin;
		$arr_post['end_get_content']	=	$end;
		
//		echo $url;
//		echo '<hr />';
//		$postdata	=		'';
//		$postdata	=	array();
//		foreach ($arr_post as $k=>$v)
//		{
//			$postdata[]	=	$k.'='.$v;
//		}
//		echo implode('&',$postdata);
//		die();
		
		$info	=	$browser->post($url,$arr_post);		

         if (preg_match('/' . $begin . '(.*?)' . $end . '/ism', $info, $match)) 
         {
             $info=trim($match[1]);
         }
         else {
         	$message	=	'ERROR_GET_CONTENT_VIR| #123 API false'.$info;
         	array_push($arrErr,$message);
            continue;
         }
      	 if (stristr($info,'ERROR_GET_CONTENT_VIR')) {
  	 		$message	=	'ERROR_GET_CONTENT_VIR| '.$info;
     		array_push($arrErr,$message);
            continue;
         }
         $number_getContent	=	$number_getContent + 1;
	}

	$param	=	'';	
	if((in_array($param_start,$arr_link) or $param_start == 0) and count($arr_link))
	{
		$param_start	=	$arr_link[0];
	}
	if($param_end and in_array($param_end,$arr_link))
	{	
		$param	=	'start='.$param_start.';param=0;end='.$param_start.';';
	}else if (count($arr_link) and count($arr_link) <> count($arr_result)) {
		$param	=	'start='.$param_start.';param='.$arr_link[count($arr_link)-1].';end='.$param_end.';';		
	}else {
		$param	=	'start='.$param_start.';param=0;end='.$param_start.';';		
	}
//	echo '<hr />'.$param;
//	echo '<hr />'.$arr_link[count($arr_link)-1];
	$query	=	'UPDATE `#__article2010_category_vir` 
						SET `last_run` = '.$db->quote(date ( 'Y-m-d H:i:s' )).', 
							`lastGet_param` = '.$db->quote($param).'
						WHERE `id` ='. $obj_cat->id;

	$arr_obj[0]->number_getcontent	=	$number_getContent;

	$db->setQuery($query);
//	echo $db->getQuery();
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
	if (isset($_REQUEST['cat_id'])) {
		$arr_obj	=	array();
		$id_origional	=	$_REQUEST['cat_id'];
		$query = "SELECT *
			FROM `#__article2010_category_vir`
			WHERE publish = 1 and id = $id_origional
			ORDER BY `last_run`
			LIMIT 0,1";	
		$db->setQuery($query);
		$db->loadObject($obj);
		$arr_obj[0]	=	$obj;
		$arr_obj[1]	=	$obj;
	}else {
		$query = "SELECT *
			FROM `#__article2010_category_vir`
			WHERE publish = 1 ".
			" ORDER BY `last_run`
			LIMIT 0,2";	
		$db->setQuery($query);
		$arr_obj	=	$db->loadObjectList();
	}	
	return $arr_obj;
}

function getListContent($str_html)
{
	$html	=	loadHtmlString($str_html);
	$arr_link	=	array();
	$arr_title	=	array();
	if ($content = $html->find('div[class="all-items clearfix"]',0)) {
		$items		=	$content->find('a[class="heading"]');
		for ($i=0;$i<count($items); $i++)
		{
			$item	=	$items[$i];
			$arr_link[]	=	$item->href;
			$arr_title[]	=	trim(strip_tags($item->innertext));
		}	
	}	
	if ($content = $html->find('ul[class="content-more"]',0)) {
		$items	=	$content->children();
		for ($i=0; $i<count($items);$i++)
		{
			$item	=	$items[$i];
			$arr_link[]	=	$item->first_child()->href;
			$arr_title[]	=	trim(strip_tags($item->first_child()->innertext));
		}				
	}
	$isNext	=	false;
	if (count($arr_link)) {
			$isNext	=	true;
		}
	$obj_return	=	new stdClass();
	$obj_return->arr_link	=	$arr_link;
	$obj_return->arr_title	=	$arr_title;	
	$obj_return->isNext	=	$isNext;
	return $obj_return;
}

function mosModelNewsGetVIR($link_content, $title_content, $section_id, $catid, $cat_alias, $path_image = 'images', $link_image ='images/vr305', $SiteID ='vr305')
{
	global $database, $my, $mainframe, $mosConfig_offset, $arrErr;
	$db	=	$database;
	
	if (!$content	=	mosModelNewsvirGetcontent($link_content,$title_content, $section_id, $catid, $cat_alias))
	{
		$message_ban	=	$arrErr[count($arrErr)-1];
		$message	=	'#389 models news mosModelNewsGetVOV. Not get content.'.$id_content;
		array_push($arrErr,$message);
		mosBanidStore('vir.com.vn','',$id_content,1,$message_ban."\r\n".$message);
		return false;
	}	
	// check content:
	$root	=	'http://www.vir.com.vn/';
	$arr_Images	=	array();

	mosGetImages($content,$root, $arr_Images,$path_image, $link_image);
	
	$content->arr_image			=	$arr_Images;

	$query	=	'SELECT id '.
				' FROM `#__article2010_new_vir` '.
				' WHERE title = '.$db->quote($content->title).
					' AND created = '. $db->quote(date("Y-m-d H:i:s",strtotime($content->content_date)));
	$db->setQuery($query);
	if ($id = $db->loadResult()) {
		$message	=	'#791 models news mosModelNewsGetVIR.['.$id.'] duplicate title and created .'.$link_content . "( $content->title - $content->content_date)";
		mosBanidStore('vir.com.vn',0,$id,1,'duplicate title and created .'.$link_content . "( $content->title - $content->content_date)");
		array_push($arrErr,$message);
		return false;
	}
	
	if (!mosModelNewsVIRSave($content, $SiteID)) {
		$message	=	'#391 models news mosModelNewsGetVIR. Not save content.'.$link_content;
		array_push($arrErr,$message);
		return false;
	}
	return $content->PageHTML;
}

function mosModelNewsvirGetcontent($link_content,$title_content, $section_id, $catid, $cat_alias)
{
	global $arrErr;
	
	$browser	=	new phpWebHacks();
	$response	=	$browser->get($link_content);
	
	$html	=	loadHtmlString($response);	
	
// Tìm title_alias bài viết	
	$href = new href();
	$title_alias = $href->convertalias($title_content);
	
// Tìm thời gian	
	if (!$date_time = $html->find('label[class="source"]',0)->innertext) {
		$message	=	'#335 models newsbaomoi mosModelNewsbaomoiGetContent. Invalid get time for '.$link;
		array_push($arrErr,$message);
		return false;
	}
	
//	Ha An | vir.com.vn | Jul 28, 2011 10:40 am
//	| VIR/VNA | Jul 26, 2011 09:28 am
	$date_time	=	preg_replace('/[^\|]*\|[^\|]+\|/ism','',$date_time);	
	$date_time	=	preg_replace('/(am|pm|,)/ism','',$date_time);
	$date_time	=	strftime('%Y-%m-%d %H:%M:00',strtotime($date_time));
	
//	Jul 29 2011 19:15 pm
//	Jul 29 2011 19:15 pm
	$content	=	$html->find('div[class="content_article"]',0);
	$obj_cuttext	=	new AutoCutText($content->innertext,10);
	$introtext	=	$obj_cuttext->getIntro();
	$fulltext	=	$obj_cuttext->getFulltext();
	
	$obj_content	=	new stdClass();
	$obj_content->id_content	=	md5($link_content);
	$obj_content->SourceURL		=	$link_content;
	$obj_content->section_id	=	$section_id;
	$obj_content->catid			=	$catid;
	$obj_content->cat_alias		=	$cat_alias;
	$obj_content->content_date	= 	$date_time;
	$obj_content->title			=	trim(str_replace("\r\n",' ',$title_content));
	$obj_content->title_alias	=	$title_alias;
	$obj_content->intro			=	mostidy_clean(trim(str_replace("\r\n",' ',$introtext)));
	$obj_content->fulltext		=	mostidy_clean(trim(str_replace("\r\n",' ',$fulltext)));		
	$response	=	preg_replace('/<meta[^>]*name="description"[^>]*\/>/ism','',$response);	
	$obj_content->PageHTML 		=	$response;	
	return $obj_content;
}

function mosModelNewsEnVIRProcessOther($str_in, $SiteID,$id_original,$catname = null)
{
	global $database,$arrErr;
	$db	=	$database;
	$reg_link_other = '/<a.*?href=["\' ]*([^"\' ]*)["\' ]*.*?>(.*?)<\/a>/ism';
	$reg_id_other = '/[\w\d-_\/]*NewsId=(\d+)\&CatId=(\d+)/ism';
	$href	=	new href();
	$root	=	'http://www.vir.com.vn';	
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
		if ($id_result	=	mosModelNewsEnVIRSaveOther($SiteID, $id_original, $matches[1],$matches_link[0][$i], $link, $matches[2])) {
			$title		=	strip_tags($matches_link[2][$i]);
			$link_content = '<a title=\''.str_replace(array('&gt;','\''),array(' ','"'),$title).'\' href="/index.php?option=com_content&task=view&id=' . $id_result.'" >'.$title."</a>";
			$str_in		=	str_replace($matches_link[0][$i],$link_content,$str_in);
		}	
	}
	return $str_in;
}

function mosModelNewsEnVIRSaveOther ($SiteID,$id_original,$id_original_other,$str_replace,$link_other,$catID)
{
	global $database,$arrErr;
	
	$db	=	& $database;
	$query = "SELECT * FROM #__article2010_new_vir WHERE id_original = ".trim($id_original_other);
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

function mosModelNewsVIRSave($data, $SiteID = 'vr305')
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
	$row->SiteName		=	'vir.com.vn';	
	$row->Domain		=	'vir.com.vn';	
	$row->SourceURL		=	$data->SourceURL;
	$row->created		= 	date("Y-m-d H:i:s",strtotime($data->content_date));
	$row->title			=	$data->title;
	$row->title_alias	=	$data->title_alias;
	$row->introtext		=	str_replace("\r\n",' ',$data->intro);
	$row->fulltext		=	str_replace("\r\n",' ',$data->fulltext);
	$row->sectionid		=	$data->section_id;
	$row->catid			=	$data->catid;
	$row->CatName		=	$data->cat_alias;	
	$row->PageHTML 		=	$data->PageHTML;

	$fmtsql = "INSERT INTO `#__article2010_new_vir` SET %s ON DUPLICATE KEY UPDATE  %s  ";
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
		if ($k != 'id_original' and $k!= 'title' and $k != 'created') {
			$update[] = $db->NameQuote( $k ).' = '.$db->Quote( $v );
		}		
	}
	$db->setQuery( sprintf( $fmtsql, implode( ",", $insert ) ,  implode( ",", $update ) ) );
//	echo $db->getQuery();
//	die();
	if (!$db->query()) {
		$messege	= $db->getErrorMsg();
		array_push($arrErr,$messege);
		return false;
	}
	$id = mysql_insert_id();
	
	mosModelNewsVIRSaveMedia($data->arr_image,$id,$SiteID);
//	mosUpdateOther($content->id_content,$content->title,$id,'#__article2010_new_vir');
//	mosModelNewsbaomoiSaveParam($content,$row->id,$SiteID);	
	return true;
}

function mosModelNewsVirSaveMedia($arr_media,$contenid, $SiteID = 'vr305')
{	
	global $database, $arrErr;
	$db	=	$database;

	for ($i = 0; $i <count($arr_media); $i++)
	{
		$media	=	$arr_media[$i];
		$row = new mosVovSmedia2010_new( $db );
		$query	=	'SELECT id 
					 FROM `#__smedia2010_new` 
					 WHERE aid='.$db->quote($contenid).
					 	' AND media_url = '.$db->quote($media->media_url).
						' AND SiteID='.$db->quote($SiteID);
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
