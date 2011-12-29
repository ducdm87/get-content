<?php
/*
 * @filename 	: getnews.php
 * @version  	: 1.0
 * @package	 	: vietbao.vn/get news/
 * @subpackage	: component
 * @license		: GNU/GPL 3, see LICENSE.php
 * @author 		: Team : �?ức
 * @authorEmail	: 
 *				: ducdm87@binhhoang.com
 * @copyright	: Copyright (C) 2011 Vi?t b�o�. All rights reserved. 
 */
/**
 * Enter description here...
 *
 * @param unknown_type $lastGet_vovId
 * @param unknown_type $max_number
 * @param unknown_type $get_existing
 * @return unknown
 */
function mosModelNewsbaomoiGetNews(& $lastGet_vovId, $max_number = 1000, $get_existing = true,$tbl_subFix = '1' )
{	
	global $arrErr,$database, $mosConfig_live_site;
	
	if (isset($_REQUEST['id_content']) and $_REQUEST['id_content'] >0) {
		$id_content	=	$_REQUEST['id_content'];
		$number		=	$max_number;
	}else {
		$id_content	=	mosModelNewsbaomoiGetNewId();
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
					FROM #__article2010_new_baomoi$tbl_subFix 
					WHERE id_original in($_id)
						AND Domain = ".$db->quote('baomoi.com');
		$db->setQuery($query);
		$arr_result	=	$db->loadResultArray();
		
		$arr_ban	=	mosBanidGet('baomoi.com',array('id_origional'),"id_origional  in($_id)");
				
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
		$time1	=	date('Y-m-d H:i:s');
		
		if ($get_existing==false && in_array($arr_ID[$i],$arr_result))
		{
			array_push($arrErr,'#444 '.$arr_ID[$i].' is existing');
			continue;
		}
		$id_content	=	$arr_ID[$i];
		$link		=	$mosConfig_live_site."/index.php?option=$option&task=getnewsbaomoi&conten_id=".$arr_ID[$i];
		
		$web 		=	parse_url($link);
		$begin		=	md5('BEGIN_GET_CONTENT_VOV');
		$end		=	md5('END_GET_CONTENT_VOV');
		 
		$postdata	=	$web['query'];
		$postdata	.=	'&begin_get_content='.$begin;
		$postdata	.=	'&end_get_content='.$end;
		$postdata	.=	'&sid='.$_REQUEST['sid'];

//		echo $link;
//		echo '<hr />';
//		echo $postdata;		
//		die();

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
        $time2	=	date('Y-m-d H:i:s');    
//        echo '<hr />#['. $i.']['.$id_content.']'.$time1 . ' => '. $time2;
         if (preg_match('/' . $begin . '(.*?)' . $end . '/ism', $info, $match)) 
         {                   
             $info=trim($match[1]);
         }
         else {         	
         	$message	=	"[".$arr_ID[$i]."] ".'ERROR_GET_CONTENT_VOV| #123 API false'.$info;
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
	$obj_result	=	new stdClass();
	$obj_result->number_getcontent	=	$number_getContent;
	$obj_result->id_result			=	$id_result;
//	mysql_close($db->_resource);
	return $obj_result;
}
/**
 * Enter description here...
 *
 * @return unknown
 */
function mosModelNewsbaomoiGetNewId()
{
	global $arrErr,$database, $mosConfig_live_site;
	$url		=	'http://www.baomoi.com/';
	$browser	=	new phpWebHacks();
	$response	=	$browser->get($url);
	$reg		=	'/<div id="bmLatest" class="latest">\s*<h3>.*?<\/h3>\s*(<ul class="bmListing">.*?<\/ul>)\s*<div class="story advertorial">/ism';
	if (!preg_match($reg,$response,$matches)) {
		$message	=	'#341 models newsbaomoi mosModelNewsbaomoiGetNews. Invalid get news content';
		array_push($arrErr,$message);
		return false;
	}
	$list_news	=	$matches[1];
	$html	=	loadHtmlString($list_news);
	$ultags	=	$html->find('ul');
	$href		=	$ultags[0]->children[0]->children[0]->attr['href'];	
	$reg		=	'/(\d+)\.epi/ism';
	if (!preg_match($reg,$href,$matches)) {
		$message	=	'#361 models news mosModelNewsGetNews. Invalid get id of news content';
		array_push($arrErr,$message);
		return false;
	}
	$id_content	=	intval($matches[1]);
	return $id_content;
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
function mosModelNewsbaomoiGetBM($id_content, $section_id = '1', $catid =1 , $path_image = 'images', $link_image, $SiteID = 'bm12', $tbl_subFix = '1')
{
	global $arrErr,$database;	
	$link_content	=	'http://www.baomoi.com/alias-baomoi/119/'.$id_content.'.epi';
	if (!$content	=	mosModelNewsbaomoiGetContent($link_content,$id_content))
	{
		$message_ban	=	$arrErr[count($arrErr)-1];		
		$message	=	'#389 models newsbaomoi mosModelNewsbaomoiGetBM. Not get content.'.$id_content;
		array_push($arrErr,$message);
		mosBanidStore('baomoi.com','',$id_content,1,$message_ban."\r\n".$message);
		return false;
	}	
	$root	=	'http://www.baomoi.com';	
		
	$content->intro		=	mosModelNewsbaomoiProcessOther($content->intro,$SiteID, $id_content, $content->CatName, $tbl_subFix);
	$content->fulltext	=	mosModelNewsbaomoiProcessOther($content->fulltext,$SiteID, $id_content, $content->CatName, $tbl_subFix);
		
	$arr_Images	=	array();
	mosGetImages($content, $root, $arr_Images, $path_image, $link_image);
	
	$content->arr_image		=	$arr_Images;
	
	$content->id_content	=	$id_content;	
	if (!mosModelNewsbaomoiSave($content,$section_id , $catid, $SiteID, $tbl_subFix)) {
		$message	=	'#391 models news mosModelNewsGetVOV. Not save content.'.$id_content;
		array_push($arrErr,$message);
		return false;
	}
}

function mosModelNewsbaomoiGetContent($link,$id_content)
{
	global $arrErr,$database;
	$db	=	$database;
	
	$browser	=	new phpWebHacks();
	$response	=	$browser->get($link);
	$link		=	$browser->get_addressbar();	

	// get title
	$reg_title	=	'/<div class="story">\s*<h1>(.*?)<\/h1>/ism';
	if (!preg_match($reg_title,$response,$matches)) {
		$message	=	'#332 models newsbaomoi mosModelNewsbaomoiGetContent. Invalid get title for '.$link;
		array_push($arrErr,$message);
		return false;
	}
	$title	=	strip_tags($matches[1]);
	// get alias
	$href		=	new href();
	$alias		=	$href->take_file_name($title);	
	// get intro
	$reg_intro	=	'/<p[^>]*class="summary">(.*?)<\/p>/ism';
	if (!preg_match($reg_intro,$response,$matches)) {
		$message	=	'#333 models newsbaomoi mosModelNewsbaomoiGetContent. Invalid get introtext for '.$link;
		array_push($arrErr,$message);
	}
	$intro	=	strip_tags($matches[1]);
	// get image full not in block
	$reg_image	=	'/<p\s*class="thumb">\s*<a[^>]*id="avatar_\d+"[^>]*>\s*<img[^>]*src="([^"]*)"[^>]*>\s*<\/a>\s*<\/p>/ism';
	if (preg_match($reg_image,$response,$matches)) {
		$link_image		=	$matches[1];
		$tag_images			=	'<img src="'.$link_image.'" title="'.$title.'" alt="'.$title.'" style="float:right;" />';
		$intro	=	$tag_images. $intro;
	}	
	// get full
	$reg_full	=	'/<div class="storyContents">(.*?)<\/div>\s*<\/div>\s*<div class="storyAttach">/ism';
	if (!preg_match($reg_full,$response,$matches)) {
		$message	=	'#334 models newsbaomoi mosModelNewsbaomoiGetContent. Invalid get fulltext for '.$link;
		array_push($arrErr,$message);
		return false;
	}
	$full_text	=	$matches[1];	
	$full_text	=	preg_replace('/<\w+\d*[^>\/]*>\s*<\/\w+\d*>/ism','',$full_text);	
	
	// get date
	$reg_date	=	'/<span class="time">(\d+)\s*(\w+).*?<\/span>/ism';
	if (!preg_match($reg_date,$response,$matches)) {
		$message	=	'#356 models newsbaomoi mosModelNewsbaomoiGetContent. Invalid get date for '.$link;
		array_push($arrErr,$message);
		return false;
	} 
	$time_ago	=	$matches[1];
	$time_type	=	$matches[2];
	$time_type	=	substr($time_type,0,2);
	
	$content_date	=	date_time_ago_vn($time_ago,$time_type);	
	//12:21 PM, 26/04/2011	
//	$content_date	=	$date_time;
	$reg_image	=	'/(<p class="storyInlinePhoto">\s*<img[^>]*src=\'([^\']*)\'[^>]*\/>)\s*<\/p>/ism';
	if (preg_match_all($reg_image,$full_text,$matches)) {
		$arr_image	=	$matches;	
		$nuber_image=	count($arr_image[2]);
		$full_text	=	preg_replace('/<p>\s*This photo was shot on the plane\s*<\/p>/ism','',$full_text);
		$full_text	=	preg_replace($reg_image,'',$full_text);
		preg_match_all('/(<p[^>]*>.*?<\/p>)/ism',$full_text,$matches);
		$full_text	=	'';
		$i			=	0;
		$number		= 	floor(count($matches[1])/($nuber_image+1));
		$number		=	$number?$number:1;		
		 
		foreach ($arr_image[2] as $k=>$link_image)
		{
			if ($number*$k<count($matches[1])) {
				for ($i=$number*$k;$i<$number*($k+1);$i++)
				{				
					$full_text	.=	$matches[0][$i];
				}
			}
			
			$full_text	.=	'<img src="'.$link_image.'" />';
		}
		for ($i=$i;$i<count($matches[1]);$i++)
		{
			$full_text	.=	$matches[0][$i];
		}		
	}	
	//	get duplicate
	$arr_duplicate	=	array();
	if (preg_match('/hlDuplicate/ism',$response)) {
//		http://www.baomoi.com/DuplicateList.aspx?cid=6154222
		$link_duplicate	=	'http://www.baomoi.com/DuplicateList.aspx?cid='.$id_content;
		$html_duplicate	=	$browser->get($link_duplicate);
		preg_match_all('/<div class="story">(.*?)<\/div>/ism',$html_duplicate,$match_duplicate);
		for ($j = 1;$j<count($match_duplicate[1]); $j++)
		{
			if (preg_match('/<h4>\s*<a[^>]*href=".*?\/(\d+)\.epi"[^>]*>.*?<\/a>/ism',$match_duplicate[1][$j],$data_dup)) {
				$arr_duplicate[]	=	$data_dup[1];
			}
		}
	}else {
		$arr_duplicate	=	false;
	}
	
	
	//	get comment
	$arr_comment	=	array();
	if (preg_match('/<div id="emoticon">(.*?)<\/div>/ism',$response,$match_comment)) {	
		// get user
		preg_match_all('/<span\s*class="userTitle">(.*?)<\/span>/ism',$match_comment[1],$matche_user);
		$arr_user	=	$matche_user[1];
		// get time
		preg_match_all('/<span\s*class="time">(.*?)<\/span>/ism',$match_comment[1],$matche_time);
		$arr_time	=	$matche_time[1];
		// get comment
		preg_match_all('/<span\s*id="lbContent">(.*?)<\/span>/ism',$match_comment[1],$matche_comment);
		$arr_content	=	$matche_comment[1];
		for ($i = 0; $i<count($arr_user); $i++)
		{
			$obj_comment	=	new stdClass();
			$obj_comment->name	=	strip_tags($arr_user[$i]);
			$obj_comment->comment	=	strip_tags($arr_content[$i]);
			$time			=	strip_tags($arr_time[$i]);
			preg_match('/(\d+)\s*(\w+)/ism',$time,$matche_time);
			$time_ago	=	$matche_time[1];
			$time_type	=	$matche_time[2];
			$time_type	=	substr($time_type,0,2);
			$obj_comment->datetime	=	date_time_ago_vn($time_ago,$time_type,120);
			$arr_comment[]	=	$obj_comment;
		}
	}else {
		$arr_duplicate	=	false;
	}
	
	// search keyword
	$arr_keyword	=	array();
	$reg_keyword	=	'/<div class="sKeywords">(.*?)<\/div>/ism';
	if (preg_match($reg_keyword,$response,$matches)) {
		preg_match_all('/<h5>(.*?)<\/h5>\s*<ul>(.*?)<\/ul>/ism',$matches[1],$matches);		
		for ($i=0;$i<count($matches[1]);$i++)
		{
			$obj_key	=	new stdClass();
			$obj_key->title	=	$matches[1][$i];
			$str_li		=	$matches[2][$i];
			preg_match_all('/<li>\s*<strong>\s*<a[^>]*>(.*?)<\/a>\s*<\/strong>\s*<\/li>/ism',$str_li,$matches_li);
			$obj_key->key	=	implode(';',$matches_li[1]);
			$arr_keyword[]	=	$obj_key;
		}		
	}
	// find cat_idorigional
	$html	=	loadHtmlString($response);
	if (!$latest = $html->find('div[id="latestStories"]',0)) {
		$message	=	'#756 models newsbaomoi mosModelNewsbaomoiGetContent. Invalid get catid for '.$link;
		array_push($arrErr,$message);
		return false;
	}	
	if (!$latest->find('ul[class="bmListing"]',0)) {
		$message	=	'#856 models newsbaomoi mosModelNewsbaomoiGetContent. Invalid get catid for '.$link;
		array_push($arrErr,$message);
		return false;
	}
	
	$link_content	=	$latest->find('ul[class="bmListing"]',0)->find('a',0)->href;
	$reg_category	=	'/\/(\d+)\/\d+\.epi/ism';
	if (!preg_match($reg_category,$link_content,$match_cat)) {
		$message	=	'#856 models newsbaomoi mosModelNewsbaomoiGetContent. Invalid get catid for '.$link;
		array_push($arrErr,$message);
		return false;
	}

	$query	=	'SELECT secid,catid,title FROM `#__article2010_category_baomoi` WHERE id_origional = '. $match_cat[1];
	$db->setQuery($query);
	
	if (!$db->loadObject($obj_cat)) {
		$message	=	'#156 models newsbaomoi mosModelNewsbaomoiGetContent. Invalid get catid or not need get. for '.$link;
		array_push($arrErr,$message);
		return false;
	}
	if ($obj_cat->secid == 0) {
		$message	=	'#456 models newsbaomoi mosModelNewsbaomoiGetContent. Invalid get catid or not need get. for '.$link;
		array_push($arrErr,$message);
		return false;
	}
	$link_content	=	'http://www.baomoi.com/alias-baomoi/'.$match_cat[1].'/'.$id_content.'.epi';
	
	$obj_content			=	new stdClass();
	$obj_content->title		=	trim(str_replace("\r\n",'',$title));
	$obj_content->intro		=	mostidy_clean(trim(str_replace("\r\n",'',$intro)));
	$obj_content->fulltext	=	mostidy_clean(trim(str_replace("\r\n",'',$full_text)));
	$obj_content->sectionid =	$obj_cat->secid;
	$obj_content->catid		=	$obj_cat->catid;
	$obj_content->CatName	=	$obj_cat->title;
	$obj_content->catid_original =	$match_cat[1];	
	$obj_content->link		=	$link_content;
	$obj_content->alias		=	$alias;
	$obj_content->content_date		=	$content_date;
	$obj_content->key_word	=	$arr_keyword;
	$obj_content->PageHTML	=	$response;
	$obj_content->duplicate	=	$arr_duplicate;
	$obj_content->comment	=	$arr_comment;
	
	return $obj_content;
}

function mosModelNewsbaomoiProcessOther($str_in, $SiteID,$id_original,$catname = "", $tbl_subFix = '1')
{
	global $database,$error;
	$db	=	$database;
	$reg_link_other = '/<a.*?href=["\' ]*([^"\' ]*)["\' ]*.*?>(.*?)<\/a>/ism';
	$reg_id_other = '/\/(\d+)\.epi/ism';
	$href	=	new href();
	$root	=	'http://baomoi.com';
	
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
		
		if ($id_result	=	mosModelNewsbaomoiSaveOther($SiteID, $id_original, $matches[1],$matches_link[0][$i], $link, $catname, $tbl_subFix)) {
			$title		=	strip_tags($matches_link[2][$i]);
			$link_content = '<a title=\''.str_replace(array('&gt;','\''),array(' ','"'),$title).'\' href="/index.php?option=com_content&task=view&id=' . $id_result.'" >'.$title."</a>";
			$str_in		=	str_replace($matches_link[0][$i],$link_content,$str_in);
		}
	}
	return $str_in;
}

function mosModelNewsbaomoiSaveOther ($SiteID,$id_original,$id_original_other,$str_replace,$link_other,$cat_name, $tbl_subFix = '1')
{
	global $database,$error;

	$db	=	& $database;
	$query = "SELECT * FROM #__article2010_new_baomoi$tbl_subFix WHERE id_original = ".trim($id_original_other);
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

function mosModelNewsbaomoiSave($content, $section_id = 1, $catid = 1, $SiteID = 'bm12', $tbl_subFix = '1')
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
	$row->SiteName		=	'baomoi.com';
	$row->Domain		=	'baomoi.com';
	$row->SourceURL		=	$content->link;
	$row->created		= 	date("Y-m-d H:i:s",strtotime($content->content_date));
	$row->title			=	$content->title;
	$row->title_alias	=	$content->alias;
	$row->introtext		=	$content->intro;
	$row->fulltext		=	$content->fulltext;
	$row->sectionid		=	$content->sectionid;
	$row->catid			=	$content->catid;
	$row->CatName		=	$content->CatName;
	$row->catid_original=	$content->catid_original;
	$row->PageHTML 		=	$content->PageHTML;

	$fmtsql = "INSERT INTO `#__article2010_new_baomoi$tbl_subFix` SET %s ON DUPLICATE KEY UPDATE  %s  ";
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
	mosModelNewsbaomoiSaveMedia($content->arr_image,$id,$SiteID);
	mosModelNewsbaomoiSaveParam($content,$id,$SiteID);
	mosUpdateOther($content->id_content,$content->title,$id,'#__article2010_new_baomoi');
	return true;
}

function mosModelNewsbaomoiSaveMedia($arr_media,$contenid, $SiteID = 'bm12')
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
function mosModelNewsbaomoiSaveParam($content,$contenid, $SiteID = 'bm12')
{
	global $arrErr,$database;
	$db	=	$database;
	
	$browser	=	new phpWebHacks();	
	
	$id_content	=	$content->id_content;	
	$arr_keyword=	$content->key_word;
	
	// get origional hyperlink
	
	$link	=	'http://www.baomoi.com/Home/category/host-baomoi/alias-baomoi/'.$id_content.'.epi';
	$response	=	$browser->get($link);	

	$html	=	loadHtmlString($response);
	
	$base_link	=	'';
	if ($html->find('base',0)) {
		$base_link	=	$html->find('base',0)->href;
	}
	$PageHTML	=	'';
	if ($html->find('div[id="mainContainer"]',0)) {
		$PageHTML	=	$html->find('div[id="mainContainer"]',0)->innertext;
	}
	
	$query	=	'INSERT into `#__article2010_original`
					SET aid = '.$db->quote($contenid).',
						SiteID = '.$db->quote($SiteID).',
						PageHTML = '.$db->quote($PageHTML).',
						url = '.$db->quote($base_link);
	$db->setQuery($query);
	$db->query();	
	
	// store key word
	for ($i = 0; $i <count($arr_keyword); $i++)
	{
		mosParamStore($contenid,$arr_keyword[$i]->key,$arr_keyword[$i]->title,1);
	}	
	// store duplicate	
	
	if ($content->duplicate) {
		for ($i = 0; $i<count($content->duplicate); $i++)
		{		
			mosBanidStore('baomoi.com',$contenid,$content->duplicate[$i],2,'content duplicate');		
		}	
	}	
	// store comment	
	for ($i = 0; $i<count($content->comment); $i++)
	{
		mosCommentStore($contenid,"baomoi.com",$content->comment[$i]->name,$content->comment[$i]->datetime,$content->comment[$i]->comment);
	}	
}