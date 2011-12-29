<?php


function mosModelNewsBongdaPlusGetNews($get_existing = true)
{	
	global $arrErr,$database, $mosConfig_live_site;
	$db = $database;
	
	$arr_obj 	=	mosModelBongdaPlusGetCat();	
	if (count($arr_obj) <2 ) {
		echo 'sucess';
		dump_data($arr_obj);
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
	
	if($getold == 0 and $page >2)
		$page	=	1;
		
	$data_content = mosModelBongdaPlusGetListContent($obj_cat->domain, $obj_cat->id_origional, $page);

	$arr_ID		=	$data_content->arrID;
	$arr_link	=	$data_content->arr_link;
	$arr_title	=	$data_content->arr_title;
	
	$arr_result	=	array();

	$arr_result	=	array();
	
	if (count($arr_ID) <1 and $getold) {
		$bool	=	1;		
	}
	else {
		$bool	=	0;
		if ($get_existing==false) {
			$db = $database;
			$_id	=	implode(',',$arr_ID);
			$query = "SELECT id_original 
					FROM #__article2010_new_bongdaplus
					WHERE id_original in($_id)";
			$db->setQuery($query);		
			$arr_result	=	$db->loadResultArray();
			
			if (count($arr_ID) == count($arr_result) && $data_content->isNext) 
			{
				$bool = 1;			
			}
		}
	}
	
	$number_run	=	1;
	
	while ($bool and $number_run <10 and $getold) {
		$page ++ ;
		$data_content = mosModelBongdaPlusGetListContent($obj_cat->domain, $obj_cat->id_origional, $page);
		$arr_ID		=	$data_content->arrID;
		$arr_link	=	$data_content->arr_link;	
		if (count($arr_ID) <1 and $data_content->isNext) {
			$bool	=	1;			
		}
		else if ($get_existing==false) {
			$db = $database;
			$_id	=	implode(',',$arr_ID);
			$query = "SELECT id_original 
					FROM #__article2010_new_bongdaplus
					WHERE id_original in($_id)";
			$db->setQuery($query);			
			$arr_result	=	$db->loadResultArray();
			if (count($arr_ID) == count($arr_result) && $data_content->isNext) {
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
		
		$arr_ban	=	mosBanidGet('bongdaplus.vn',array('id_origional'),"id_origional  in($_id)");	
		if (count($arr_result)) {
			if (count($arr_ban)) {
				$arr_result	=	array_merge($arr_result,$arr_ban);
			}
		}else {
			$arr_result	=	$arr_ban;
		}
	}	

	$now = date('Y-m-d H:i:s');	
	$fp = fopen( dirname(__FILE__).DS.'..'.DS.'log'.DS.'bongdaplus.txt', 'a');
	fputs($fp, "__________________________________________________\r\n");
	fputs($fp, "__________________________________________________\r\n");
	fputs($fp, "--------------------------------------------------\r\n");
	
	fputs($fp, " ID: ". $obj_cat->id." \r\n");
	fputs($fp, " Title: ". $obj_cat->title." \r\n");
	fputs($fp, " number id: ". count($arr_ID)." \r\n");
	$isNext	=	$data_content->isNext?'yes':'no';
		
	fputs($fp, " IS Next: ". $isNext." \r\n");
	fputs($fp, " Page: ". $page." \r\n");
	fputs($fp, " Link: ". $obj_cat->domain." \r\n");	
	
	fputs($fp, "------Run time: $now ------\r\n");
	fputs($fp, "--------------------------------------------------\r\n");
	fclose($fp);
	
	$number_getContent	=	0;
	$i = 0;
	$option	=	$_REQUEST['option'];
	$browser	=	new phpWebHacks();
	
	for ($i = 0; $i < count($arr_ID) ; $i++)
	{
		if ($get_existing==false  && count($arr_result) > 0 && in_array($arr_ID[$i],$arr_result))
		{
			array_push($arrErr,'#444 '.$arr_ID[$i].' is existing');
			continue;
		}

		$id_content	=	$arr_ID[$i];
		$begin		=	md5('BEGIN_GET_CONTENT_OTO-HUI');
		$end		=	md5('END_GET_CONTENT_OTO-HUI');
		
		$url		=	$mosConfig_live_site."/index.php?option=$option";
		
		$arr_post	=	array();
		$arr_post['begin_get_content']	=	$begin;
		$arr_post['end_get_content']	=	$end;
		$arr_post['task']				=	'getbongdaplus';
		$arr_post['content_id']			=	$arr_ID[$i];
		$arr_post['content_link']		=	$arr_link[$i];		
		$arr_post['content_title']		=	$arr_title[$i];
		$arr_post['secid']		=	$obj_cat->secid;
		$arr_post['catid']		=	$obj_cat->catid;
		$arr_post['catid_origional']	=	$obj_cat->id_origional;
		$arr_post['cat_title']	=	$obj_cat->title;
		
//		echo $url;
//		echo '<br /><hr />';
//		$a	=	array();
//		
//		foreach ($arr_post as $k=>$v) {
//			$a[]	=	"$k=$v";
//		}
//		echo implode('&',$a);
//		die();
		
		$info	=	$browser->post($url,$arr_post);
		      
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
	
	$now = date('Y-m-d H:i:s');	
	$fp = fopen( dirname(__FILE__).DS.'..'.DS.'log'.DS.'bongdaplus.txt', 'a');	
	fputs($fp, "------Run time: $now ------\r\n");
	fputs($fp, "------number_getContent: $number_getContent ------\r\n");
	fclose($fp);
	
	if ($data_content->isNext == false) {
		$query	=	'UPDATE `#__article2010_category_bongdaplus` 
					SET `last_run` = '.$db->quote(date ( 'Y-m-d H:i:s' )).', 
						`lastGet_param` = '.$db->quote("getold=0;page=1;").'
					WHERE `id` ='. $obj_cat->id;
		$arr_obj[0]->isNext	=	false;
		}else {
			// con nh trang
			$query	=	'UPDATE `#__article2010_category_bongdaplus` 
					SET `last_run` = '.$db->quote(date ( 'Y-m-d H:i:s' )).', 
						`lastGet_param` = '.$db->quote("getold=$getold;page=$page;").'
					WHERE `id` ='. $obj_cat->id;
			$arr_obj[0]->isNext	=	true;
	}	
	$db->setQuery($query);
	$db->query();

	$arr_obj[0]->page	=	$page;
	$arr_obj[0]->next	=	$data_content->isNext?'YES':'NO';
	
	$arr_obj[0]->number_getcontent	=	$number_getContent;	
	return $arr_obj;
}

// Lấy category từ cơ sở dữ liệu
function mosModelBongdaPlusGetCat()
{
	global $database;
	$db	=	& $database;
	$arr_obj	=	array();
	if (isset($_REQUEST['catid_origional'])) {
		$id_origional	=	$_REQUEST['catid_origional'];
		$query = "SELECT *
			FROM `#__article2010_category_bongdaplus`
			WHERE id_origional = $id_origional
			ORDER BY `last_run`";	
		$db->setQuery($query);
		$db->loadObject($obj);
		
		$arr_obj[]	=	$obj;
		$arr_obj[]	=	$obj;		
	}else {
		$query = "SELECT *
			FROM `#__article2010_category_bongdaplus`
			WHERE publish = 1 AND (`lastGet_param` like '%getold=1;%' OR  `lastGet_param` = '')
			ORDER BY `last_run`
			LIMIT 0,2";	
		$db->setQuery($query);
		$arr_obj	=	$db->loadObjectList();
	}	
	return $arr_obj;
}


function mosModelBongdaPlusGetListContent($link, $id_origional = 1, $page =1)
{
	global $arrErr,$database, $mosConfig_live_site;
	$root	=	'http://bongdaplus.vn';
	$href	=	new href();	
	$link	=	str_replace('.bbd','/trang'.$page.'.bbd',$link);	
	
	$browser	=	new phpWebHacks();
	$response	=	$browser->get($link);
	$html		=	loadHtmlString($response);
	$arr_link_article	=	array();
	
	// get from spotlight
	if ($spotlight		=	$html->find('div[id="spotlight"]',0)) {
		if ($item	=	$spotlight->find('h1[class="title"]',0)) {
			$obj_article	=	new stdClass();
			if ($item->first_child()) {
				$obj_article->title	=	strip_tags($item->first_child()->innertext);
				$obj_article->link	=	$href->process_url(trim($item->first_child()->href),$root);
				$arr_link_article[]	=	$obj_article;
				
				if ($spotlight->find('ul[class="story-listing"]',0) and $items	=	$spotlight->find('ul[class="story-listing"]',0)->find('li')) {
					for ($i=0; $i<count($items); $i++)
					{
						$obj_article	=	new stdClass();
						$obj_article->title	=	strip_tags($items[$i]->first_child()->innertext);
						$obj_article->link	=	$href->process_url(trim($items[$i]->first_child()->href),$root);
						$arr_link_article[]	=	$obj_article;			
					}	
				}
			}
			
		}

	}
	
	// get from topnews
	if ($topnews	=	$html->find('div[class="topnews"]',0)) {
		if($items	=	$topnews->find('p[class="title"]'))
		{
			for ($i=0; $i<count($items); $i++)
			{
				$obj_article	=	new stdClass();
				$obj_article->title	=	strip_tags($items[$i]->first_child()->innertext);
				$obj_article->link	=	$href->process_url(trim($items[$i]->first_child()->href),$root);
				$arr_link_article[]	=	$obj_article;
			}
		}
	}
	
	// get from column-listing
	if ($column_listing	=	$html->find('div[class="column-listing"]',0)) {
		if ($items	=	$column_listing->find('p[class="title"]')) {
			for ($i=0; $i<count($items); $i++)
			{
				$obj_article	=	new stdClass();
				if ($items[$i]->first_child()) {
					$obj_article->title	=	strip_tags($items[$i]->first_child()->innertext);
					$obj_article->link	=	$href->process_url(trim($items[$i]->first_child()->href),$root);
					$arr_link_article[]	=	$obj_article;
				}
			}
		}
	}
	
	$obj_return	=	new stdClass();
	
	$arr_title	=	array();
	$arr_link	=	array();
	$arr_id	=	array();
	$reg_id	=	'/\/(\d+)\.bbd/ism';
	for($i=0; $i < count($arr_link_article); $i++)
	{
		$item	=	$arr_link_article[$i];
		$arr_title[]	=	$item->title;		
		$arr_link[]	=	$item->link;
		if (!preg_match($reg_id,$item->link,$matches_id)) {
			continue;
		}
		$arr_id[]	=	$matches_id[1];
	}
	$obj_return->arr_title	=	$arr_title;
	$obj_return->arr_link	=	$arr_link;
	$obj_return->arrID		=	$arr_id;
	// get next page
	$page++; 
	$reg_next_page	=	'/<a[^>]*class="item"[^>]*href="[^"]*\/trang'.$page.'\.bbd"[^>]*>/ism';
	$obj_return->isNext	=	false;
	
	if (preg_match($reg_next_page,$response)) {
		$obj_return->isNext	=	true;		
	}	
	echo $link;
	echo '&nbsp;&nbsp;|&nbsp;;&nbsp;';
	$next	=	$obj_return->isNext?'yes':'no';
	echo $next;
	echo '&nbsp;&nbsp;|&nbsp;;&nbsp;';
	echo count($arr_id);
	echo '<hr />';
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
function mosModelBongdaPlusGetBDP($id_content, $catid_origional, $cat_title, $link_content, $title_contetn, $section_id = '1', $catid =1 , $path_image = 'images', $link_image,$SiteID = 'nld125')
{
	global $arrErr; 	
	
	if (!$content	=	mosModelBongdaPlusGetContent($link_content,$id_content,$title_contetn,$SiteID))
	{
		$message_ban	=	$arrErr[count($arrErr)-1];		
		$message	=	'#389 models newsEnvov mosModelBongdaPlusGetBDP.<b> Not get content</b>.'.$id_content;
		array_push($arrErr,$message);
		mosBanidStore('nld.com.vn','',$id_content,1,$message_ban."\r\n".$message);
		return false;
	}
	
	$content->intro		=	mosModelBongdaPlusProcessOther($content->intro,$SiteID, $id_content);
	$content->fulltext	=	mosModelBongdaPlusProcessOther($content->fulltext,$SiteID, $id_content);
	
	$root	=	'http://bongdaplus.vn';
	$arr_Images	=	array();
	mosGetImages($content,$root, $arr_Images,$path_image, $link_image);
	
	$content->arr_image		=	$arr_Images;
	$content->id_content	=	$id_content;
	$content->catid_origional	=	$catid_origional;
	$content->cat_title		=	$cat_title;
	if (!mosModelBongdaPlusSave($content,$section_id , $catid, $SiteID)) {
		$message	=	'#391 models newsBongdaPlus mosModelBongdaPlusGetBDP. <b>Not save content</b>.'.$id_content. ' ' .$link_content;
		array_push($arrErr,$message);
		return false;
	}
}

function mosModelBongdaPlusGetContent($link, $id_content, $title_contetn, $SiteID = 'bdp180')
{
	global $arrErr;
	
	$browser	=	new phpWebHacks();
	$source_content	=	$browser->get($link);

	$html	=	loadHtmlString($source_content);
	$href	=	new href();
	$title_alias = $href->convertalias($title_contetn);
// get intro	
	if (!$intro	=	strip_tags($html->find('div[class="summary"]',0)->innertext)) {
		
	}

	if(!$full_text	=	$html->find('div[class="story-body article"]',0))
	{
		$message	=	'#391 models newsBongdaPlus mosModelBongdaPlusGetContent. <b>Not Get fulltext content</b>.'.$id_content. ' ' .$link_content;
		array_push($arrErr,$message);
		return false;
	}
	
	$wrap_related	=	$full_text->find('div[class="wrap-related"]',0);
	@ $full_text->find('div[class="wrap-related"]',0)->outertext	=	"";
	@ $full_text->find('p[class="subtitle"]',0)->outertext	=	"";
	@ $full_text->find('h1[class="title"]',0)->outertext	=	"";
	@ $full_text->find('div[class="summary"]',0)->outertext	=	"";
	@ $full_text->find('p[class="author"]',0)->outertext	=	"";
	@ $full_text->find('div[class="ads"]',0)->outertext	=	"";
	@ $full_text	=	$full_text->innertext;
	
	$root	=	'http://bongdaplus.vn';
		
	if ($wrap_related->find('ul[class="story-listing"]',0)) {
		$list_a		=	$wrap_related->find('ul[class="story-listing"]',0)->find('a');
		$intro		=	$intro.' <ul class="story-listing">';
		for ($i=0; $i<count($list_a); $i++)
		{
			$title_a	=	$list_a[$i]->innertext;
			$link_a		=	$list_a[$i]->href;		
			$intro		=	$intro.'<li> <a href="'. $href->process_url($link_a,$root) .'" title="'.$title_a.'" >'.$title_a.'</a></li>';
		}
		$intro	=	$intro	.	'</ul>';		
	}
	if ($images	=	$wrap_related->find('p[class="photo"]',0)) {
		$images	=	$images->find('img');
		for ($i=0; $i<count($images); $i++)
		{
			$image	=	$images[$i];
			$intro	=	$intro . '<img title="'. $image->title .'" alt="'. $image->alt .'" src="'. $href->process_url($image->src,$root) .'" style="float:right" />';	
		}		
	}
	
// get date Thứ ba 07/06/2011 14:45'	
	$date	=	$html->find('p[class="updated"]',0)->innertext;
	$reg_date	=	'/\s*(\d+)\/(\d+)\/(\d+)\s*(\d+)\:(\d+)/ism';
	if (!preg_match($reg_date,$date,$matches_date)) {
		return false;
	}
	$content_date	=	$matches_date[3].'-'.$matches_date[2].'-'.$matches_date[1].' '.$matches_date[4].':'.$matches_date[5].':00';

// Get comment
	$arr_comment	=	array();
	if ($divCommentList	=	$html->find('div[id="divCommentList"]',0)) {
		$comment		=	$divCommentList->find('div[class="detail"]');
		for ($i = 0; $i<count($comment); $i++)
		{
			$user	=	$comment[$i]->find('p[class="user"]',0);
			$obj_comment	=	new stdClass();
			$date			=	$user->find('span[class="date"]',0)->innertext;
			if (!preg_match($reg_date,$date,$matches_date)) {
				continue;
			}
			$comment_date			=	$matches_date[3].'-'.$matches_date[2].'-'.$matches_date[1].' '.$matches_date[4].':'.$matches_date[5].':00';
			$obj_comment->datetime	=	$comment_date;
			$obj_comment->name		=	$user->first_child()->innertext;
			$obj_comment->comment	=	$comment[$i]->find('p[class="message"]',0)->innertext;	
			$arr_comment[]			=	$obj_comment;
		}		
	}
	
	$obj_content			=	new stdClass();
	$obj_content->catid_original = trim($id_content);
	$obj_content->title		=	trim(str_replace("\r\n",' ',$title_contetn));
	$obj_content->intro		=	mostidy_clean(trim(str_replace("\r\n",' ',$intro)));
	$obj_content->fulltext	=	mostidy_clean(trim(str_replace("\r\n",' ',$full_text)));	
	$obj_content->link		=	$link;
	$obj_content->alias		=	$title_alias;
	$obj_content->content_date		=	$content_date;	
	$obj_content->comment	=	$arr_comment;
	$obj_content->PageHTML	=	$source_content;

	return $obj_content;
}

// lấy thông tin link khác
function mosModelBongdaPlusProcessOther($str_in, $SiteID,$id_original)
{
	global $database,$error;
	$db	=	$database;

	$reg_link_other = '/<a[^>]*href=["\' ]*([^"\' ]*bongdaplus\.vn[^"\' ]*)["\' ]*[^>]*>(.*?)<\/a>/ism';
	$reg_id_other = '/\/(\d+)\.bbd/ism';
	$href	=	new href();
	$root	=	'http://bongdaplus.vn';
	
	if (!preg_match_all($reg_link_other,$str_in,$matches_link)) {var_dump($matches_link);
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
	
		if ($id_result	=	mosModelBongdaPlusSaveOther($SiteID, $id_original, $id_orgional_other,$matches_link[0][$i], $link)) {
			$link_content = '<a title="'.str_replace('&gt;','',$title).'" href="/index.php?option=com_content&task=view&id=' . $id_result.'" >'.$title."</a>";
			$str_in		=	str_replace($matches_link[0][$i],$link_content,$str_in);
		}
	}
	return $str_in;
}

function mosModelBongdaPlusSaveOther ($SiteID,$id_original,$id_original_other,$str_replace,$link_other)
{
	global $database,$error;
	
	$db	=	& $database;
	$query = "SELECT id FROM #__article2010_new_bongdaplus WHERE id_original = ".trim($id_original_other);
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


// Lưu nội dung bài viết
function mosModelBongdaPlusSave($content, $section_id = 1, $catid = 1, $SiteID = 'nld125')
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
	$row->SiteName		=	'bongdaplus.vn';	
	$row->Domain		=	'bongdaplus.vn';	
	$row->SourceURL		=	$content->link;
	$row->created		= 	date("Y-m-d H:i:s",strtotime($content->content_date));	
	$row->title			=	$content->title;
	$row->title_alias	=	$content->alias;
	$row->introtext		=	$content->intro;
	$row->fulltext		=	$content->fulltext;
	$row->sectionid		=	$section_id;
	$row->catid			=	$catid;
	$row->CatName		=	$content->cat_title;
	$row->catid_original=	$content->catid_origional;
	$row->PageHTML 		=	$content->PageHTML;

	$fmtsql = "INSERT INTO `#__article2010_new_bongdaplus` SET %s ON DUPLICATE KEY UPDATE  %s  ";
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
	mosModelBongdaPlusSaveMedia($content->arr_image,$id,$SiteID);
	mosModelBongdaPlusSaveParam($content,$id,$SiteID);
	mosUpdateOther($content->id_content,$content->title,$id,'#__article2010_new_bongdaplus');
	return true;
}

function mosModelBongdaPlusSaveMedia($arr_media,$contenid, $SiteID = 'bdp180')
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
			$message	=	'#562 models newsEnvov mosModelBongdaPlusSaveMedia.<b> Invalid store media</b> for '.$media->Path.'. Link: '.$media->media_url.' sql error: '.$row->getError ();
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
function mosModelBongdaPlusSaveParam($content,$contenid, $SiteID = 'bdp180')
{
	global $arrErr,$database;
	
	// store comment	
	for ($i = 0; $i<count($content->comment); $i++)
	{
		// $aid, $domain = "", $name,$datetime,$comment, $param = ''
		mosCommentStore($contenid,'bongdaplus.vn',$content->comment[$i]->name,$content->comment[$i]->datetime,$content->comment[$i]->comment, $param = '');
	}	
}