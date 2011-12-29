<?php


function mosModelTTTimNhanh_CMGetNews($get_existing = true)
{	
	
	global $arrErr,$database, $mosConfig_live_site;
	$db = $database;
	$arr_obj 	=	mosModelTTTimNhanh_CMGetCat();	
	
	$obj_cat	=	$arr_obj[0];

	$param	=	$obj_cat->param;	
	preg_match('/date=([^;]*);id=([^;]*)/ism',$param,$matches_param);
	
	$date = ''; $id_getcat = '';
	if(isset($matches_param[1]))
		$date	=	$matches_param[1];
	
	if(isset($matches_param[2]) and $matches_param[2])
		$id_getcat	=	$matches_param[2];
		
	$data_content = mosModelTTTimNhanh_CMGetListContent($obj_cat->link,$obj_cat->cat_parent);
	
}

// Láº¥y category tá»« cÆ¡ sá»Ÿ dá»¯ liá»‡u
function mosModelTTTimNhanh_CMGetCat()
{
	global $database;
	$db	=	& $database;
	if (isset($_REQUEST['cat_alias'])) {
		$id_origional	=	$_REQUEST['cat_alias'];
		$query = "SELECT *
			FROM `#__article2010_category_tt_timnhanh_chuyenmuc`
			WHERE publish = 1 and alias_origional = ".$db->quote($id_origional)."
			ORDER BY `id`
			LIMIT 0,2";	
		$db->setQuery($query);
		$obj	=	$db->loadObjectList();
		$obj[1]	=	$obj;
	}else {
		$query = "SELECT *
			FROM `#__article2010_category_tt_timnhanh_chuyenmuc`
			WHERE publish = 1
			ORDER BY `id`
			LIMIT 0,2";	
		$db->setQuery($query);
		$obj	=	$db->loadObjectList();
	}
	return $obj;
}


function mosModelTTTimNhanh_CMGetListContent($link,$cat_alias)
{
	
	global $arrErr,$database, $mosConfig_live_site;
	
	$browser	=	new phpWebHacks();
	
	$next = 1;
		
	while($next != 0)
	{		
		if (!$source_content	=	$browser->get($link)) {
			echo 'No connect. Please press f5 to refresh';
			die();
		}
		$html	=	loadHtmlString($source_content);
		
		$obj_group_news = $html->find('div[id="b_social_main"]',0);
		
		// <p class="titleBig"><a href="http://tintuc.timnhanh.com/xa-hoi/lao-dong/20110601/35AB8245/Nhung-nghe-ky-quac-thoi-hien-dai.htm" title="Những nghề kỳ quặc thời hiện đại ">Những nghề kỳ quặc thời hiện đại </a></p>
		$arr_item_article = $obj_group_news->find('ul[class="groupList"]',0);
		$arr_list_article = $arr_item_article->find('li');
		
		for ($j=0;$j<count($arr_item_article);$j++)
		{
			$obj_content_item = $arr_list_article[$j];		
			
			$reg_link_to_article = '/<p class="title(Big|S)"><a href=".*?com(\/[^"]+|)\/([^"]+)\/([^"]+)\/([^"]+)\/([^"]+)\.htm"[^>]*>(.*?)<\/a>/ism';
			
			if (!preg_match($reg_link_to_article,$obj_content_item->innertext,$matches)) {				
				$message	=	'#341 models news mosModelTTTimNhanhGetListContent. Invalid get news content. Cat_parent id:'.$cat_parent;
				array_push($arrErr,$message);
			}
			
			// get intro
			$intro = $obj_content_item->find('p[class="infoS"]',0)->innertext;
			$date = $obj_content_item->find('p[class="modifyS"]',0)->innertext;
		
			preg_match('/\s*(\d+\/\d+\/\d+)/ism',$date,$matches_date);			
			
			$article_item = new stdClass();
			$article_item->id_original		=	$matches[4];
			$article_item->cat_alias		=	$cat_alias;
			$article_item->title			=	$matches[6];
			$article_item->alias			=	$matches[5];
			$article_item->link				=	$matches[1];
			$article_item->intro			=	$intro;
			$article_item->date				=	$matches_date[1];
		
			if (!is_dir($path_image)) {
				mkdir($path_image);
			}
			
			$path_image	.=	DS.date("Y",strtotime($date));
			if (!is_dir($path_image)) {
				mkdir($path_image);		
			}	
			$path_image	.=	DS.date("m",strtotime($date));
			if (!is_dir($path_image)) {
				mkdir($path_image);
			}
			$link_image	.=	'/'.date("Y",strtotime($date)).'/'.date("m",strtotime($date)).'/';
			$href	=	new href();
			$image_prefix	=	$href->take_file_name($article_item->title).'-'.date("Y",strtotime($date)).'-'.date("m",strtotime($date));	

			$obj_get_image	=	new vov_Get_Image($url_image,$path_image);	
			if ($response = $obj_get_image->get_image($image_name, $param_getimage)) {
				var_dump($response);
			}
			die();		
			$root = 'http://tintuc.timnhanh.com';
		
			$article_item->image	=	$arr_Images;
			mosModelTTTimNhanh_CMSaveContent($article_item);
		}
		
		// <div class="viewMore"><a href="http://tintuc.timnhanh.com/xemthem/xa-hoi/lao-dong/20110426/426382.htm">Xem thêm</a></div>	
		$obj_pages = $obj_group_news->find('div[class="viewMore"]',0);
		$content_pages = $obj_pages->innertext;
		// get next page <a href="http://tintuc.timnhanh.com/xemthem/xa-hoi/lao-dong/20110426/426382.htm">Xem thêm</a>
		$reg_next_page	=	'/<a href="(http:\/\/tintuc\.timnhanh\.com\/xemthem(\/[^\.]+|)\/[^\.]+\/[^\.]+\/[^\.]+\.htm)">Xem thêm<\/a>/ism';	
	
		if (preg_match_all($reg_next_page,$content_pages,$matches_cat_next)) {
			$next = 1;
			$link = $matches_cat_next[1];
		}
		else $next = 0;
		die();
	}
	
	
	
}

// LÆ°u ná»™i dung bÃ i viáº¿t
function mosModelTTTimNhanh_CMSaveContent($data)
{	
	global $database, $my, $mainframe, $mosConfig_offset, $arrErr;
	$db	=	$database;
	$id	=	$content->id_original;	
	var_dump($data); die();
	// insert into
	$date	=	date('Y-m-d H:i:s');
	
	$nullDate = $database->getNullDate ();
	$row = new mosTTTimNhanhChuyenmuc( $db );

	
	$row->id_original	=	$data->id_original;		
	$row->cat_alias	=	$data->cat_alias;		
	$row->title		=	$data->title;			
	$row->alias		=	$data->alias;			
	$row->link			=	$data->link	;			
	
	$fmtsql = "INSERT INTO `#__article2010_new_tt_timnhanh_chuyenmuc` SET %s ON DUPLICATE KEY UPDATE  %s  ";
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
	return true;
}

