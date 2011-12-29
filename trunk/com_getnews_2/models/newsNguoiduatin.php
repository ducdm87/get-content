<?php

function mosModelNewsNguoiduatinGetNews($get_existing = true)
{		
	global $arrErr,$database, $mosConfig_live_site;
	
	$arr_obj 	=	mosModelNguoiduatinGetCat();
	if (count($arr_obj)<2) {
		echo 'success';
		die();
	}
	
	$obj_cat	=	$arr_obj[0];

	$param	=	$obj_cat->lastGet_param;
	preg_match('/getold=([^;]*);page=([^;]*);/ism',$param,$matches_param);
	
	$getold		=	1;	$page	=	0;
	if(isset($matches_param[1]))
		$getold	=	$matches_param[1];
	
	if(isset($matches_param[2]) and $matches_param[2])
		$page	=	$matches_param[2];
		
	$page		=	intval($page)	+	1;
	
	if($getold == 0 and $page >5)
	{		
		$page	=	1;
	}

	$bool	=	1;	
	$data_content = mosModelNewsNguoiduatinGetListContent($obj_cat->link, $page, $obj_cat->id_origional, $obj_cat->parent);
	
	$arr_ID		=	$data_content->arrID;
	$arr_link	=	$data_content->arr_link;	
	
	$arr_result	=	array();
	$arr_geted	=	array();
	if ($get_existing==false) {
		$db = $database;
		$aid	=	array();
		for ($i=0 ; $i<count($arr_ID); $i++)
		{
			$aid[]	=	$db->quote($arr_ID[$i]);
		}
		$_id	=	implode(',',$aid);
		$query = "SELECT id_original 
					FROM #__article2010_new_nguoiduatin 
					WHERE id_original in($_id)";
		$db->setQuery($query);	

		$arr_result	=	$db->loadResultArray();		
		$arr_ban	=	mosBanidGet('nguoiduatin.vn',array('id_origional'),"id_origional  in($_id)");
		$arr_geted	=	mosGetDBOBJ('#__article2010_totalcontent',array('id_origional'),'id_origional  in('.$_id.') AND SiteID='.$db->quote('ndt320'));		

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
	$browser	=	new phpWebHacks();
	echo '<br /> ';
	echo '$arr_result: ';	echo count($arr_result);
	echo '<br /> ';
	echo '$arr_ID: ';	echo count($arr_ID);
	echo '<br /> [$arr_ID] : '. count($arr_ID);
	
	for ($i = 0; $i < count($arr_ID) ; $i++)
	{
		if ($get_existing==false && in_array($arr_ID[$i],$arr_result))
		{
			array_push($arrErr,'#444 '.$arr_ID[$i].' is existing');
			$db = $database;
			$query	=	'UPDATE #__article2010_new_nguoiduatin SET catid_original = '.$obj_cat->id_origional
						.' ,latestRunDate = '. $db->quote(date ( 'Y-m-d H:i:s' ))
						.' ,sectionid = '.$obj_cat->secid
						.' ,catid = '.$obj_cat->catid.
						' WHERE id_original = '.$arr_ID[$i];
			$db->setQuery($query);
			$db->query();
			continue;
		}
		
		$id_content	=	$arr_ID[$i];
		$link		=	$mosConfig_live_site."/index.php?option=$option";
		
		$web 		=	parse_url($link);
		$begin		=	md5('BEGIN_GET_CONTENT_NGUOI_DUA_TIN');
		$end		=	md5('END_GET_CONTENT_NGUOI_DUA_TIN');
		 
		$postdata	=	$web['query'];
		$postdata	.=	'&task=getnewsNDT';
		$postdata	.=	'&conten_id='.$arr_ID[$i];
		$postdata	.=	'&conten_link='.$arr_link[$i];
		$postdata	.=	'&page=p:'.$page." | c:$obj_cat->id_origional";
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
         	$message	=	'ERROR_GET_CONTENT_NGUOI_DUA_TIN| #123 API false '.$id_content.' '.$info;
         	array_push($arrErr,$message);
            continue;
         }
      	 if (stristr($info,'ERROR_GET_CONTENT_NGUOI_DUA_TIN')) {
  	 		$message	=	'ERROR_GET_CONTENT_NGUOI_DUA_TIN| '.$info;
     		array_push($arrErr,$message);
            continue;
         }
         $number_getContent	=	$number_getContent + 1;
	}
	mosSaveNDTGetParam($obj_cat->id, $getold, $page, $data_content->isNext,$obj_cat);
	
	$arr_obj[0]->number_getcontent	=	$number_getContent;
	$arr_obj[0]->page	=	$page;
	$arr_obj[0]->next	=	$data_content->isNext?'YES':'NO';
	$arr_obj[0]->old	=	$getold?'YES':'NO';
	$arr_obj[0]->date	=	date('Y-m-d');
	return $arr_obj;
//	mysql_close($db->_resource);
	return $obj_result;
}

function mosSaveNDTGetParam($catid, $getold, $page, $isNext, $obj_cat)
{	
	global $arrErr,$database;
	$db	=	$database;
	$query	=	'';
	if ($isNext == false) {
		$query	=	'UPDATE `#__article2010_category_nguoiduatin` 
					SET `last_run` = '.$db->quote(date ( 'Y-m-d H:i:s' )).', 
						`lastGet_param` = '.$db->quote("getold=0;page=1;").'
					WHERE `id` ='. $catid;
		}else {
			// con nh trang
			$query	=	'UPDATE `#__article2010_category_nguoiduatin` 
					SET `last_run` = '.$db->quote(date ( 'Y-m-d H:i:s' )).', 
						`lastGet_param` = '.$db->quote("getold=$getold;page=$page;").'
					WHERE `id` ='. $catid;
	}
	if ($isNext == false) {
		$file_name	=	dirname(__FILE__).DS.'..'.DS.'log'.DS.'nguoiduatin_max.txt';
		echo $file_name;
		$fp = fopen( $file_name, 'a');
		fputs($fp, "__________________________________________________\r\n");
		fputs($fp, "page: $page\r\n");
		fputs($fp, "id: $obj_cat->id\r\n");
		fputs($fp, "id_origional: $obj_cat->id_origional\r\n");
		fputs($fp, "link: $obj_cat->link\r\n");
		fclose($fp);
	}	
	$db->setQuery($query);
	$db->query();
}

function mosModelNguoiduatinGetCat()
{
	global $database;
	$db	=	& $database;
	$arr_obj	=	array();
	if (isset($_REQUEST['cat_id'])) {
		$cat_id	=	$_REQUEST['cat_id'];
		$query = "SELECT *
			FROM `#__article2010_category_nguoiduatin`
			WHERE id = $cat_id";
		$db->setQuery($query);
		$db->loadObject($obj);
		
		$arr_obj[]	=	$obj;
		$arr_obj[]	=	$obj;
	}else {
		$query = "SELECT *
			FROM `#__article2010_category_nguoiduatin`
			WHERE publish = 1 and (lastGet_param = '' or `lastGet_param` like ".$db->quote('%getold=1;%').")".
			" ORDER BY `last_run` LIMIT 0,2";
		$db->setQuery($query);		
		$arr_obj	=	$db->loadObjectList();
		if (count($arr_obj) == 1) {
			$arr_obj[]		=	$arr_obj[0];
		}
	}	
	return $arr_obj;
}


function mosModelNewsNguoiduatinGetListContent($link,$page =1, $catid_origional, $cat_parent)
{
	global $arrErr,$database, $mosConfig_live_site;
	$root	=	'http://www.nguoiduatin.vn/';
	$href	=	new href();	
	$link	=	preg_replace('/\/(cat-[^\/]*)\//',"/$1-p$page/",$link);	
	echo '<br/>';
	echo $link;	
	echo '<br/>';
	$browser	=	new phpWebHacks();
	$response	=	$browser->get($link);
	$arr_link_article	=	array();
	$html		=	loadHtmlString($response);
	$arr_link	=	array();
	$arr_id		=	array();
	$arr_title	=	array();
	$arr_alias	=	array();
	$reg_id	 	= '/-a(\d+)\.html/ism';
	
	if ($page == 1 and $item	=	$html->find('a[class="title-cate"]',0)) {		
		if (preg_match($reg_id,$item->href, $mathces)) {
			$arr_id[]		=	$mathces[1];
			$arr_link[]	=	$href->process_url(trim($item->href),$root);	
		}		
	}
	if ($items = $html->find('div[class="box-cate-list"]')) {
		for ($i=0;$i<count($items); $i++)
		{
			$link	=	$href->process_url($items[$i]->find('a[class="title-news"]',0)->href,$root);
			if (preg_match($reg_id,$link, $mathces)) {
				$arr_id[]		=	$mathces[1];
				$arr_link[]	=	$link;
			}
					
		}
	}	
	$page++;
	$isNext	=	false;
	if ($items = $html->find('div[class="paging"]',0)) {
		$item	=	$items->innertext;
		$reg_page	=	"/<a[^>]*>\s*$page\s*<\/a>/ism";
		if (preg_match($reg_page,$item)) {
			$isNext	=	true;
		}
	}
	$obj_return	=	new stdClass();
	$obj_return->arr_link	=	$arr_link;	
	$obj_return->arrID		=	$arr_id;	
	$obj_return->isNext		=	$isNext;
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
function mosModelNewsNguoiduatinGetNDT($id_content, $link_content, $page, $path_image = 'images', $link_image,$SiteID = 'ndt320')
{
	global $arrErr;	
	
	if (!$content	=	mosModelNewsGetContent($link_content,$id_content))
	{
		$message_ban	=	$arrErr[count($arrErr)-1];
		$message	=	'#389 models newsNguoiduatin mosModelNewsNguoiduatinGetNDT. Not get content.'.$id_content;
		array_push($arrErr,$message);
//		echo 'banid was disable. Please enable if real run';
		mosBanidStore('nguoiduatin.vn','',$id_content,$message_ban."\r\n".$message);
		return false;
	}

	$content->intro		=	mosModelNewsNguoiduatinProcessOther($content->intro,$SiteID, $id_content);
	$content->fulltext	=	mosModelNewsNguoiduatinProcessOther($content->fulltext,$SiteID, $id_content);
	$content->page		=	$page;
	
	if(!mosModelNewsNguoiduatinProcessCategory($content))
	{
		$message	=	'#391 models news mosModelNewsNguoiduatinGetNDT. Invalid process category or this category is not get content. id content: '.$id_content.', cat: '. $content->cat_id_origional;
		array_push($arrErr,$message);
		return false;
	}
	
	$root	=	'http://nguoiduatin.vn/';
	$arr_Images	=	array();
	
	mosGetImages($content, $root, $arr_Images, $path_image, $link_image);	
	
	$content->arr_image		=	$arr_Images;
	$content->id_content	=	$id_content;
	if (!mosModelNewsSave($content,$SiteID)) {
		$message	=	'#391 models news mosModelNewsNguoiduatinGetNDT. Not save content.'.$id_content;
		array_push($arrErr,$message);
		return false;
	}
}

function mosModelNewsGetContent($link,$id_content)
{
	global $arrErr,$database;	
	$db	=	$database;
//$link	=	'http://nguoiduatin.vn/dang-bon-bon-chay-xe-tai-no-lop-gay-tai-nan-a8626.html';
	$browser	=	new phpWebHacks();
	echo $link;
	if (!$response	=	$browser->get($link)) {
		$message	=	'#832 models news mosModelNewsGetContent. Invalid get article '.$link;
		array_push($arrErr,$message);
		return false;
	}	
	
	$html	=	loadHtmlString($response);
	$detail	=	$html->find('div[class="detail"]',0);
	$title	=	strip_tags($detail->find('a[class="title-cate"]',0)->innertext);	
	
	// get alias
//	/thang-ngay-lam-lo-cua-kieu-nu-mot-con-a11030.html
	$reg_alias	=	'/\/([^\/]*)-a\d+\.html/ism';
	$alias		=	'';
	if (preg_match($reg_alias,$link,$matches)) {
		$alias	=	$matches[1];
	}
	
	// get intro
	$intro	=	$detail->find('p[class="detail-intro"]',0);	
	$intro	=	strip_tags($intro);
	$intro	=	preg_replace('/\(Nguoiduatin.vn\)\s*-/ism','',$intro);	
	
	$list	=	$detail->find('ul[class="list"]',0);	
	$content=	$detail->find('div[class="cont-news"]',0);	
	$last	=	$content->last_child()->outertext;
	if (preg_match('/text-align: right;/ism',$last)) {
		$content->last_child()->outertext = '';
	}	
	$full_text	=	$list->outertext.$content->outertext;

	// get category vov
	$id_cat	=	0;
	
	$reg_menu	=	'/var\s*current_menu\s*=\s*(\d+);/ism';
	$reg_sub	=	'/var\s*current_sub\s*=\s*(\d+);/ism';

	if (preg_match($reg_sub,$response,$matches_cat)) {
		$id_cat	=	$matches_cat[1];
	}elseif (preg_match($reg_menu,$response,$matches_cat))
	{
		$id_cat	=	$matches_cat[1];
	}
	
	$reg_date	=	'/<h1[^>]*>\s*<a[^>]*class="title-cate"[^>]*>.*?<\/h1>\s*<span[^>]*>(.*?)<p[^>]*class="detail-intro">/ism';
	$detail		=	$detail->innertext; 
	if(!preg_match($reg_date,$detail,$matches_date)){
		$message	=	'#356 models news mosModelNewsGetContent. Invalid get date for '.$link;
		array_push($arrErr,$message);
		return false;
	}	
	$date	=	trim((strip_tags($matches_date[1])));
	$date	=	explode('|',$date);
	$date[0]	=	trim($date[0]);
	$time		=	trim($date[1]).':00';
	$date		=	explode('-',$date[0]);
	$date_time	=	$date[2].'-'.$date[1].'-'.$date[0].' '. $time;
	
	$href		=	new href();	
	$obj_content			=	new stdClass();
	$obj_content->title		=	trim(str_replace('\r\n','',$title));
	$obj_content->intro		=	trim(str_replace('\r\n','',mostidy_clean($intro)));
	$obj_content->fulltext	=	trim(str_replace('\r\n','',mostidy_clean($full_text)));	
	$obj_content->link		=	$link;
	$obj_content->alias		=	$alias;
	$obj_content->cat_id_origional		=	$id_cat;
	$obj_content->content_date		=	$date_time;
	$obj_content->PageHTML	=	$response;
	
	return $obj_content;
}

function mosModelNewsNguoiduatinProcessOther($str_in, $SiteID,$id_original)
{
	global $database,$error;
	$db	=	$database;
	$reg_link_other = '/<a.*?href=["\' ]*([^"\' ]*)["\' ]*.*?>(.*?)<\/a>/ism';	
	$reg_id_other = '/-a(\d+)\.html/ism';
	$href	=	new href();
	$root	=	'http://nguoiduatin.vn/';
	
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
		$id_orgional_other	=	$matches[1];		
		
		if ($id_result	=	mosModelNewsNguoiduatinSaveOther($SiteID, $id_original, $matches[1],$matches_link[0][$i], $link)) {
			$title		=	strip_tags($matches_link[2][$i]);
			$link_content = '<a title="'.str_replace('&gt;','',$title).'" href="/index.php?option=com_content&task=view&id=' . $id_result.'" >'.$title."</a>";
			$str_in		=	str_replace($matches_link[0][$i],$link_content,$str_in);
		}	
	}
	
	return $str_in;
}

function mosModelNewsNguoiduatinSaveOther ($SiteID,$id_original,$id_original_other,$str_replace,$link_other)
{
	global $database,$error;
	
	$db	=	& $database;
	$query = "SELECT * FROM #__article2010_new_nguoiduatin WHERE id_original = ".trim($id_original_other);
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

function mosModelNewsNguoiduatinProcessCategory(& $content)
{
	global $database, $arrErr;
	$db	=	$database;
	$cat_id_origional	=	$content->cat_id_origional;
	
	$db	=	$database;
	$query	=	' SELECT secid,catid '.
				' FROM #__article2010_category_nguoiduatin '.
				' WHERE id_origional = '.$db->quote($cat_id_origional).
				' 	AND publish = 1';
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

function mosModelNewsSave($content, $SiteID = 'vn10')
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
	$row->SiteName		=	'nguoiduatin';	
	$row->Domain		=	'nguoiduatin.vn';	
	$row->SourceURL		=	$content->link;
	$row->created		= 	date("Y-m-d H:i:s",strtotime($content->content_date));	
	$row->title			=	$content->title;
	$row->title_alias	=	$content->alias;
	$row->introtext		=	$content->intro;
	$row->fulltext		=	$content->fulltext;
	$row->sectionid		=	$content->secid;
	$row->catid			=	$content->catid;
	$row->note			=	$content->page;
	$row->catid_original=	$content->cat_id_origional;
	$row->PageHTML 		=	$content->PageHTML;
			
	$fmtsql = "INSERT INTO `#__article2010_new_nguoiduatin` SET %s ON DUPLICATE KEY UPDATE  %s  ";
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
	
	$obj	=	new stdClass();
	$obj->SiteID	=	$SiteID;
	$obj->aid	=	$id;
	$obj->id_origional	=	$row->id_original;	
	mosStoreOBJ('#__article2010_totalcontent',$obj);
	mosUpdateOther($content->id_content,$content->title,$id,'#__article2010_new_nguoiduatin');
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