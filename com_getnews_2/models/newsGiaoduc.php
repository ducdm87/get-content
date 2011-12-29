<?php
/*
 * @filename 	: getnews.php
 * @version  	: 1.0
 * @package	 	: vietbao.vn/get news/
 * @subpackage	: component
 * @license		: GNU/GPL 3, see LICENSE.php
 * @author 		: Team : Đức
 * @authorEmail	: 
 *				: ducdm87@binhhoang.com
 * @copyright	: Copyright (C) 2011 Vi?t b�o�. All rights reserved. 
 */
function mosModelNewsGiaoducGetNews(& $lastGet_vovId, $max_number = 1000, $get_existing = true)
{	
	global $arrErr,$database, $mosConfig_live_site;
	
	if (isset($_REQUEST['id_content']) and $_REQUEST['id_content'] >0) {
		$id_content	=	$_REQUEST['id_content'];
		$number		=	$max_number;
	}else {
		$id_content	=	mosModelNewsGiaoducGetNewId();
		$number	=	$id_content - $lastGet_vovId;
		$number	=	$number<$max_number?$number:$max_number;
	}
	
	$id_result		=	$id_content	-	$number;
	$lastGet_vovId	=	$id_content;	
	$arr_ID			=	array();

	for ($i = 0; $i < $number; $i++)
	{
		$arr_ID[]	=	$id_content;
		$id_content	=	$id_content - 1;
	}
	
	$arr_result	=	array();
	if ($get_existing==false) {
		$db = $database;
		$_id	=	implode(',',$arr_ID);
		$query = "SELECT id_original 
					FROM #__article2010_new_giaoduc 
					WHERE id_original in($_id)
						AND Domain = ".$db->quote('giaoduc.net.vn');
		$db->setQuery($query);		
		$arr_result	=	$db->loadResultArray();
		
		$arr_ban	=	mosBanidGet('giaoduc.net.vn',array('id_origional'),"id_origional  in($_id)");
		
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
	echo '<hr />';
	echo '$arr_ID: '; var_dump($arr_ID);
	echo '<br />';
	echo '<br />';
	echo '$arr_result: '; var_dump($arr_result);
	echo '<hr />';

	for ($i = 0; $i < count($arr_ID) ; $i++)
	{
		if ($get_existing==false && in_array($arr_ID[$i],$arr_result))
		{
			array_push($arrErr,'#444 '.$arr_ID[$i].' is existing');
			continue;
		}
		$id_content	=	$arr_ID[$i];
		$link		=	$mosConfig_live_site."/index.php?option=$option";
		
		$web 		=	parse_url($link);
		$begin		=	md5('BEGIN_GET_CONTENT_VOV');
		$end		=	md5('END_GET_CONTENT_VOV');
		 
		$postdata	=	$web['query'];
		$postdata	.=	'&begin_get_content='.$begin;
		$postdata	.=	'&end_get_content='.$end;
		
		$postdata	.=	'&task=getnewsgiaoduc';
		$postdata	.=	'&conten_id='.$arr_ID[$i];
//echo $link;
//echo $postdata; die();
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
         	$message	=	'ERROR_GET_CONTENT_GIAODUC| #123 API false'.$info;
         	array_push($arrErr,$message);
            continue;
         }
      	 if (stristr($info,'ERROR_GET_CONTENT_GIAODUC')) {
  	 		$message	=	'ERROR_GET_CONTENT_GIAODUC| '.$info;
     		array_push($arrErr,$message);
            continue;
         }
          $number_getContent	=	$number_getContent + 1;
	}
	$obj_result	=	new stdClass();
	$obj_result->number_getcontent	=	$number_getContent;
	$obj_result->id_result			=	$id_result;
//	mysql_close($db->_resource);
	return $obj_result;
}

function mosModelNewsGiaoducGetNewId()
{
	global $arrErr,$database, $mosConfig_live_site;
	$url		=	'http://www.giaoduc.net.vn/';	
	$browser	=	new phpWebHacks();
	$response	=	$browser->get($url);
	
	$html		=	loadHtmlString($response);
	if (!$hotnews	=	$html->find('div[id="hotnews"]',0)) {
		$message	=	'#341 models newsGiaoduc mosModelNewsGiaoducGetNewId. Invalid get news content';
		array_push($arrErr,$message);
		return false;
	}
	$items	=	$hotnews	=	$hotnews->first_child()->children();
	$reg	=	'/\/(\d+)[^\.\/]+\.html/ism';
	$arrID	=	array();
	for ($i=0;$i<count($items); $i++)
	{
		$item	=	$items[$i];
		$link	=	$item->first_child()->href;
		if (!preg_match($reg,$link,$matches)) {
			$message	=	'#361 models news mosModelNewsGetNews. Invalid get id of news content';
			array_push($arrErr,$message);
			return false;
		}	
		$arrID[]	=	intval($matches[1]);	
	}
	sort($arrID);
	return $arrID[count($arrID)-1];
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
//http://giaoduc.net.vn/xa-hoi/40-tin-nong/8129-tng-thc-gi-th-ng-cho-500-i-biu-quc-hi-khoa-xiii.html
function mosModelNewsGiaoducGetGD($id_content, $path_image = 'images', $link_image, $SiteID = 'gd285')
{
	global $arrErr;	
	$link_content	=	'http://giaoduc.net.vn/xa-hoi/40-alias-category/'.$id_content.'-alias-content.html';
	
	if (!$content	=	mosModelNewsGiaoducGetContent($link_content,$id_content))
	{
		$message_ban	=	$arrErr[count($arrErr)-1];		
		$message	=	'#389 models newsGiaoduc mosModelNewsGiaoducGetBM. Not get content.'.$id_content;
		array_push($arrErr,$message);
		mosBanidStore('giaoduc.net.vn','',$id_content,1,$message_ban."\r\n".$message);
		return false;
	}
	
	$root	=	'http://www.giaoduc.net.vn';
		
	$content->intro		=	mosModelNewsGiaoducProcessOther($content->intro,$SiteID, $id_content, $content->catid_original);
	$content->fulltext	=	mosModelNewsGiaoducProcessOther($content->fulltext,$SiteID, $id_content, $content->catid_original);
	
	$arr_Images	=	array();
	mosGetImages($content, $root, $arr_Images, $path_image, $link_image);
	
	$content->arr_image		=	$arr_Images;
	
	$content->id_content	=	$id_content;	
	if (!mosModelNewsGiaoducSave($content, $SiteID)) {
		$message	=	'#391 models news mosModelNewsGetVOV. Not save content.'.$id_content;
		array_push($arrErr,$message);
		return false;
	}
}

function mosModelNewsGiaoducGetContent($link,$id_content)
{
	global $arrErr,$database;
	$db	=	$database;
	
	$browser	=	new phpWebHacks();
	$response	=	$browser->get($link);
	
	$root		=	'http://giaoduc.net.vn/';
	$href		=	new href();
	$html		=	loadDomString($response);
	if (!$content = $html->find('div[id="viewdetails"]',0)) {
		$message	=	'#332 models newsGiaoduc mosModelNewsGiaoducGetContent. Invalid get content for '.$link;
		array_push($arrErr,$message);
		return false;
	}
	if (!$title = $content->find('a[class="contentpagetitle"]',0)) {
		$message	=	'#532 models newsGiaoduc mosModelNewsGiaoducGetContent. Invalid get title for '.$link;
		array_push($arrErr,$message);
		return false;
	}
	
	$link		=	$href->process_url($title->href,$root);
	$title		=	strip_tags($title->innertext);	
	$alias		=	$href->take_file_name($title);
	
	$chitiet	=	$content->find('table[class="c4_chitiet contentpaneopen"]',1);
	$date_time	=	$chitiet->find('td[class="createdate"]',0)->innertext;
	$chitiet->find('td[class="createdate"]',0)->outertext	=	'';
	$tags	=	$chitiet->find('div[id="share_buttons"]',0)->prev_sibling();
	if (preg_match('/Tags\:/ism',$tags->outertext)) {
		$chitiet->find('div[id="share_buttons"]',0)->prev_sibling()->outertext = '';
	}
	$chitiet->find('div[id="share_buttons"]',0)->outertext	=	'';
	
	$obj_cuttext	=	new AutoCutText($chitiet->outertext,20);
	$intro			=	$obj_cuttext->getIntro();
	$intro			=	preg_replace('/\(gdvn\)\s*-/ism','',$intro);
	$intro			=	preg_replace('/\(gdvn\)/ism','',$intro);
	$intro			=	strip_tags($intro);	
	$full_text		=	$obj_cuttext->getFulltext();	
	$full_text		=	mostidy_clean(trim(str_replace("\r\n",'',$full_text)));	
	
	// get category Giaoduc
	$reg_category	=	'/(\d+)[^\.\/]+\/(\d+)[^\.\/]+\.html/ism';
	if (!preg_match($reg_category,$link,$matches)) {
		$message	=	'#335 models newsGiaoduc mosModelNewsGiaoducGetContent. Invalid get category for '.$link;
		array_push($arrErr,$message);
		return false;
	}
	
	$cat_id	=	$matches[1];
	$query	=	'SELECT * FROM `#__article2010_category_giaoduc` WHERE id_origional = '.$cat_id;	
	$db->setQuery($query);
	$db->loadObject($obj_cat);	
	//Thứ tư, 20 Tháng 7 2011 13:51 
	$reg_date	=	'/\,\s*(\d+)[^\d]+(\d+)\s*(\d+)\s*(\d+)\:(\d+)/ism';
	if (!preg_match($reg_date,$date_time,$matches_date)) {
		
	}	
	$content_date	=	$matches_date[3].'-'.$matches_date[2].'-'.$matches_date[1].' '.$matches_date[4].':'.$matches_date[5].':00';
	
	$obj_content			=	new stdClass();
	$obj_content->title		=	trim(str_replace("\r\n",'',$title));
	$obj_content->alias		=	strtolower($alias);
	$obj_content->secid		=	$obj_cat->secid;
	$obj_content->catid		=	$obj_cat->catid;
	$obj_content->cat_title	=	$obj_cat->title;
	$obj_content->intro		=	trim(str_replace("\r\n",'',$intro));
	$obj_content->fulltext	=	$full_text;
	$obj_content->catid_original	=	$cat_id;
	$obj_content->link		=	$link;	
	$obj_content->content_date		=	$content_date;
	$obj_content->PageHTML	=	$response;	
	return $obj_content;
}

function mosModelNewsGiaoducProcessOther($str_in, $SiteID,$id_original,$cat_origianal = "")
{
	global $database,$error;
	$db	=	$database;
	$reg_link_other = '/<a.*?href=["\' ]*([^"\' ]*)["\' ]*.*?>(.*?)<\/a>/ism';
	$reg_id_other	=	'/\d+[^\.\/]*\/(\d+)[^\.\/]*\.html/ism';	
	$href	=	new href();
	$root	=	'http://giaoduc.net.vn';
	
	if (!preg_match_all($reg_link_other,$str_in,$matches_link)) {
		return $str_in;
	}
//		/xa-hoi/40/7661.html
	for ($i=0; $i< count($matches_link[0]); $i++)
	{		
		$link	=	str_replace('&amp;','&',$href->process_url($matches_link[1][$i], $root)); 
		
		if (!preg_match($reg_id_other, $link,$matches)) {
			$title		=	strip_tags($matches_link[2][$i]);
			$str_in		=	str_replace($matches_link[0][$i],$title,$str_in);
			continue;
		}	
		
		$id_orgional_other	=	$matches[1];
	
		if ($id_result	=	mosModelNewsGiaoducSaveOther($SiteID, $id_original, $matches[1],$matches_link[0][$i], $link, $cat_origianal)) {
			$title		=	strip_tags($matches_link[2][$i]);
			$link_content = '<a title=\''.str_replace(array('&gt;','\''),array(' ','"'),$title).'\' href="/index.php?option=com_content&task=view&id=' . $id_result.'" >'.$title."</a>";
			$str_in		=	str_replace($matches_link[0][$i],$link_content,$str_in);
		}
	}	
	return $str_in;
}

function mosModelNewsGiaoducSaveOther ($SiteID,$id_original,$id_original_other,$str_replace,$link_other,$cat_origianal)
{
	global $database,$error;

	$db	=	& $database;
	$query = "SELECT * FROM #__article2010_new_giaoduc WHERE id_original = ".trim($id_original_other);
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


function mosModelNewsGiaoducSave($content, $SiteID = 'gd285')
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
	$row->SiteName		=	'giaoduc.net.vn';	
	$row->Domain		=	'giaoduc.net.vn';	
	$row->SourceURL		=	$content->link;
	$row->created		= 	date("Y-m-d H:i:s",strtotime($content->content_date));	
	$row->title			=	$content->title;
	$row->title_alias	=	$content->alias;
	$row->introtext		=	$content->intro;
	$row->fulltext		=	$content->fulltext;
	$row->sectionid		=	$content->secid;
	$row->catid			=	$content->catid;
	$row->CatName		=	$content->cat_title;
	$row->catid_original=	$content->catid_original;
	$row->PageHTML 		=	$content->PageHTML;

	$fmtsql = "INSERT INTO `#__article2010_new_giaoduc` SET %s ON DUPLICATE KEY UPDATE  %s  ";
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
	mosModelNewsGiaoducSaveMedia($content->arr_image,$id,$SiteID);
	mosModelNewsGiaoducSaveParam($content,$id,$SiteID);
	mosUpdateOther($content->id_content,$content->title,$id,'#__article2010_new_giaoduc');
	return true;
}

function mosModelNewsGiaoducSaveMedia($arr_media,$contenid, $SiteID = 'gd285')
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
			$message	=	'#562 models news mosModelNewsSaveMedia. Invalid store media for '.$media->Path.'. Link: '.$media->media_url.' sql error: '.$row->getError ();
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
function mosModelNewsGiaoducSaveParam($content,$contenid, $SiteID = 'gd285')
{
	global $arrErr,$database;
	$db	=	$database;
	
	$browser	=	new phpWebHacks();	
	
	$id_content	=	$content->id_content;	
//	
//	// store comment	
//	for ($i = 0; $i<count($content->comment); $i++)
//	{
//		mosCommentStore($contenid,"giaoduc.net.vn",$content->comment[$i]->name,$content->comment[$i]->datetime,$content->comment[$i]->comment);
//	}	
}