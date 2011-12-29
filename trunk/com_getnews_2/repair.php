<?php
function mosProcess_image()
{
	$browser	=	new phpWebHacks();
	$link		=	'http://www.baomoi.com/alias-baomoi/72/6222761.epi';
	$response	=	$browser->get($link);
	echo $response; die();
}
function mosRepair_baomoi()
{
	global $arrErr,$database;
	$db	=	$database;
	$start	=	$_REQUEST['start'];
	$tbl_name	=	'`#__article2010_new_baomoi_05`';
	$query	=	'SELECT * FROM '.$tbl_name.' LIMIT '.$start.',100';
	$db->setQuery($query);	
	echo $db->getQuery();
	$arr_content	=	$db->loadObjectList();
	
	$param	=	array();
	$param['option']	=	$_REQUEST['option'];
	$param['task']	=	$_REQUEST['task'];
	$param['host']	=	'rp_baomoi';
	$param['start']	=	intval($_REQUEST['start']) + 100;
		
	$href	=	new href();
	$refresh	=	$href->refresh($param);	
	echo count($arr_content);
	if (count($arr_content)) {
		echo $refresh;
		echo '<hr />';
	}else {
		echo  'sucess';
		die();
	}
	
	for ($i=0; $i<count($arr_content); $i++)
	{
		$content	=	$arr_content[$i];
		$response	=	$content->PageHTML;
		
		// find cat_idorigional
		$html	=	loadHtmlString($response);
		$latest	=	'';
		if ($latest = $html->find('div[id="latestStories"]',0)) {
			if (!$latest->find('ul[class="bmListing"]')) {
				if (!$latest = $html->find('div[id="moreStories"]',0)) {
					$message	=	'#756 models newsbaomoi mosModelNewsbaomoiGetContent. Invalid get catid for '.$link;
					array_push($arrErr,$message);
					continue;
				}
			}
		}
		
		if (!$link_content	=	$latest->find('ul[class="bmListing"]',0)->first_child()->first_child()->href) {
			echo $response; die();
		}		
		$reg_category	=	'/\/(\d+)\/\d+\.epi/ism';
		if (!preg_match($reg_category,$link_content,$match_cat)) {
			$message	=	'#856 models newsbaomoi mosModelNewsbaomoiGetContent. Invalid get catid for '.$link;
			array_push($arrErr,$message);
			continue;
		}
		
		$query	=	'SELECT secid,catid,title FROM `#__article2010_category_baomoi` WHERE id_origional = '. $match_cat[1];
		$db->setQuery($query);		
		if (!$db->loadObject($obj_cat)) {
			$message	=	'#156 models newsbaomoi mosModelNewsbaomoiGetContent. Invalid get catid or not need get. for '.$link_content;
			array_push($arrErr,$message);
			continue;
		}
		$link_content	=	'http://www.baomoi.com/alias-baomoi/'.$match_cat[1].'/'.$content->id_original.'.epi';
		$query	=	'UPDATE '.$tbl_name.
					' SET `CatName` = '.$db->quote($obj_cat->title).
					' ,`SourceURL` =  '. $db->quote($link_content).
					' ,`sectionid` = '. $obj_cat->secid.
					' ,`catid` = '. $obj_cat->catid.
					' ,`catid_original` = '. $match_cat[1].
					' WHERE `id` = '.$content->id;
		$db->setQuery($query);
		$db->query();
	}	
	die();
}

function mosRepair_baomoi_en()
{
	global $arrErr,$database;
	$db	=	$database;
	$start	=	$_REQUEST['start'];
	$tbl_name	=	'`#__article2010_new_baomoi_en`';
	$query	=	'SELECT * FROM '.$tbl_name.' LIMIT '.$start.',100';
	$db->setQuery($query);	
	echo $db->getQuery();
	$arr_content	=	$db->loadObjectList();
	
	$param	=	array();
	$param['option']	=	$_REQUEST['option'];
	$param['task']	=	$_REQUEST['task'];
	$param['host']	=	'rp_baomoi';
	$param['start']	=	intval($_REQUEST['start']) + 100;
		
	$href	=	new href();
	$refresh	=	$href->refresh($param);	
	echo count($arr_content);
	if (count($arr_content)) {
		echo $refresh;
		echo '<hr />';
	}else {
		echo  'sucess';
		die();
	}
	$reg_category	=	'/\/(\d+)\/\d+\.epi/ism';
	for ($i=0; $i<count($arr_content); $i++)
	{
		$content	=	$arr_content[$i];
		$link_content	=	$content->SourceURL;
					
		$reg_category	=	'/\/(\d+)\/\d+\.epi/ism';
		if (!preg_match($reg_category,$link_content,$match_cat)) {
			$message	=	'#856 models newsbaomoi mosModelNewsbaomoiGetContent. Invalid get catid for '.$link_content;
			array_push($arrErr,$message);
			continue;
		}
		
		$query	=	'SELECT secid,catid,title FROM `#__article2010_category_baomoi_en` WHERE id_origional = '. $match_cat[1];
		$db->setQuery($query);		
		if (!$db->loadObject($obj_cat)) {
			$message	=	'#156 models newsbaomoi mosModelNewsbaomoiGetContent. Invalid get catid or not need get. for '.$link_content;
			array_push($arrErr,$message);
			continue;
		}		
		$query	=	'UPDATE '.$tbl_name.
					' SET `CatName` = '.$db->quote($obj_cat->title).					
					' ,`sectionid` = '. $obj_cat->secid.
					' ,`catid` = '. $obj_cat->catid.
					' ,`catid_original` = '. $match_cat[1].
					' WHERE `id` = '.$content->id;
		$db->setQuery($query);
		$db->query();		
	}	
	die();
}

function mosRepair_pl_hcm()
{
	global $arrErr,$database;
	$db	=	$database;
	$start	=	$_REQUEST['start'];
	$tbl_name	=	'`#__article2010_new_phapluat_hcm_04`';
	$query	=	'SELECT * FROM '.$tbl_name.' LIMIT '.$start.',100';
//	$query	=	'SELECT * FROM '.$tbl_name.' where id_original ='. $db->quote('2011041004481869p1013c1144');
	$db->setQuery($query);	
	echo $db->getQuery();
	$arr_content	=	$db->loadObjectList();
	
	
	$reg_id	=	'/\/([^\/]+)\/([^\/]*)\.htm/ism';
	$regcat_id	=	'/p(\d+)c(\d+)/ism';	
	$bool	=	true;
	$is_buffer	=	false;
	$number	=	0;
	$number_del	=	0;
	$arr_buffer	=	array();
	$arr_id_origonal	=	array();
	for ($i=0; $i<count($arr_content); $i++)
	{
		$content	=	$arr_content[$i];
		if (in_array($content->id,$arr_buffer)) {
			continue;
		}
		$link_content	=	$content->SourceURL;
		$html	=	loadHtmlString($content->PageHTML);
		$title	=	strip_tags($html->find('div[class="fon12"]',0)->innertext);
		if (!preg_match($reg_id,$link_content,$match_id)) {		
			continue;
		}
		if (!preg_match($regcat_id,$match_id[1],$match_cat)) {		
			continue;
		}
		
		if ($match_id[1] != $content->id_original or $match_cat[2] != $content->catid_original ) {
			$query	=	'SELECT * FROM '.$tbl_name.' where id_original ='. $db->quote($match_id[1]) .' AND id <>'. $content->id;
			$db->setQuery($query);			
			if ($db->loadObject($obj_content)) {
				$query	=	'DELETE from '.$tbl_name.' where id = '.$content->id;
				$db->setQuery($query);
				$db->query();
				$arr_buffer[]	=	$content->id;
				$arr_id_origonal[]	=	$content->id_original;
				$number_del++;
				continue;
			}			
			$query	=	'SELECT secid,catid,title FROM `#__article2010_category_phapluat_hcm` WHERE id_origional = '. $match_cat[2];
			$db->setQuery($query);
			if (!$db->loadObject($obj_cat)) {
				$message	=	'#156 models newsbaomoi mosModelNewsbaomoiGetContent. Invalid get catid or not need get. for '.$link_content;
				array_push($arrErr,$message);
				continue;
			}
			$content->sectionid	=	$obj_cat->secid;
			$content->catid		=	$obj_cat->catid;
			if ($bool) {
				$bool	=	false;
				$file_name	=	dirname(__FILE__).DS.'log'.DS.'hcm_01_'.$start.'.txt';
				$fp = fopen( $file_name, 'a');
				fputs($fp, "__________________________________________________ $tbl_name \r\n");
			}
			$number ++;
			fputs($fp, "[$number]\t ID: ". $content->id." \r\n");
			fputs($fp, " Title: ". $content->title." \r\n");
			fputs($fp, " SourceURL: ". $content->SourceURL." \r\n");
			fputs($fp, " id_original: ". $content->id_original." \r\n");
			fputs($fp, " \t=> id_original: ".$match_id[1]." \r\n");	
			fputs($fp, " catid_original: ". $content->catid_original." \r\n");
			fputs($fp, " \t=> catid_original: ". $match_cat[2]." \r\n");
			$now = date('Y-m-d H:i:s');
			fputs($fp, "______ $now ______ \r\n");	
			
		}
			
		$query	=	'UPDATE '.$tbl_name.
					' SET `id_original` = '.$db->quote($match_id[1]).					
					' ,`title_alias` = '.$db->quote($match_id[2]).					
					' ,`sectionid` = '.$db->quote($content->sectionid).					
					' ,`catid` = '.$db->quote($content->catid).					
					' ,`catid_original` = '.$db->quote($match_cat[2]).					
					' ,`title` = '.$db->quote($title).					
					' WHERE `id` = '.$content->id;
		$db->setQuery($query);		
//		echo $db->getQuery(); die();
		$db->query();	
	}
	var_dump($arr_buffer);
	echo '<hr />';
	var_dump($arr_id_origonal);
	$aid	=	implode(',',$arr_buffer);
	if (count($arr_buffer)) {
		$query	=	'DELETE from jos_smedia2010_new WHERE aid in('.$aid.') and SiteID = '. $db->quote('plh190');
		$db->setQuery($query);
		echo $db->getQuery();
		$db->query();
		$query	=	'DELETE from jos_smedia2010_new_01 WHERE aid in('.$aid.') and SiteID = '. $db->quote('plh190');
		$db->setQuery($query);
		echo $db->getQuery();
		$db->query();
	}	
	
	if ($bool == false) {
		fclose($fp);
	}
	
	$param	=	array();
	$param['option']	=	$_REQUEST['option'];
	$param['task']	=	$_REQUEST['task'];
	$param['host']	=	'rp_phapluat_hcm';
	$param['start']	=	intval($_REQUEST['start']) + 100-$number_del;
	echo 	$param['start'];
	$href	=	new href();
	$refresh	=	$href->refresh($param);		
	if (count($arr_content)) {
		echo $refresh;
		echo '<hr />';
	}else {
		echo  'sucess';
		die();
	}
	
	die();
}

function mosRepair_antd()
{
	global $arrErr,$database;
	$db	=	$database;	
	$tbl_name	=	'`#__article2010_new_anninhthudo`';
	$query	=	'SELECT * FROM '.$tbl_name.' WHERE status = 0 LIMIT 0,100';
	$db->setQuery($query);		
	
	$array_content	=	$db->loadObjectList();
	if (count($array_content) <1 ) {
		echo 'success';
		die();
	}
	
	$param	=	array();
	$param['option']	=	$_REQUEST['option'];
	$param['task']	=	$_REQUEST['task'];
	$_REQUEST['host']	=	'repair anninhthudo';
	$href	=	new href();
	$refresh	=	$href->refresh($param);
	echo $refresh;
	echo '<hr />';
	
	for ($i=0; $i<count($array_content); $i++)
	{
		$content	=	$array_content[$i];
		echo '['.$i.'] '.$content->SourceURL;
		echo '<br />';
		$fulltext	=	mosAnninhthudoProcessOther($content->fulltext,'antd175',$content->id_original); 
		mosUpdateOther($content->id_original,$content->title,$content->id,'#__article2010_new_anninhthudo');
		
		$query	=	'UPDATE '.$tbl_name.
					' SET '.$db->NameQuote('status').' = '.$db->quote('1').
					','.$db->NameQuote('fulltext').' = '.$db->quote($fulltext).
					' WHERE id = '.$content->id;
		$db->setQuery($query);
		$db->query();
	}
	
	die();
}


function mosAnninhthudoProcessOther($str_in, $SiteID,$id_original)
{
	global $database,$error;
	$db	=	$database;
	$reg_link_other = '/<a.*?href=["\' ]*([^"\' ]*)["\' ]*.*?>(.*?)<\/a>/ism';
	$reg_id_other = '/\/(\d+)\.antd/ism';
	$href	=	new href();
	$root	=	'http://www.anninhthudo.vn';
	
	if (!preg_match_all($reg_link_other,$str_in,$matches_link)) {		
		return $str_in;
	}	
	for ($i=0; $i< count($matches_link[0]); $i++)
	{		
		$link	=	$href->process_url($matches_link[1][$i], $root);
		$link	=	str_replace('&amp;','&',$link);
		
		$id_orgional_other	=	0;
		if (!preg_match($reg_id_other, $link,$matches)) {
			$file_name	=	dirname(__FILE__).DS.'log'.DS.'anninhthudo_link_other_1.txt';
			$fp = fopen( $file_name, 'a');
			fputs($fp, "_________________\r\n");
			fputs($fp, "\t\t id_original: $id_original\r\n");
			fputs($fp, "\t\t link: $link\r\n");
			fclose($fp);
//			ArticleID=105174&ChannelID=92
			if (!preg_match('/ArticleID=(\d+)\&ChannelID/ism',$link,$matches)) {				
				if (!preg_match('/option=com_content/ism',$link,$matches)) {
					$title		=	strip_tags($matches_link[2][$i]);
					$str_in		=	str_replace($matches_link[0][$i],$title,$str_in);
				}
				
				continue;	
			}
			
		}		
		if ($id_result	=	mosANTDSaveOther($SiteID, $id_original, $matches[1],$matches_link[0][$i], $link)) {
			$title		=	strip_tags($matches_link[2][$i]);
			$link_content = '<a title=\''.str_replace(array('&gt;','\''),array(' ','"'),$title).'\' href="/index.php?option=com_content&task=view&id=' . $id_result.'" >'.$title."</a>";
			$str_in		=	str_replace($matches_link[0][$i],$link_content,$str_in);
		}
	}
	return $str_in;
}

function mosANTDSaveOther ($SiteID,$id_original,$id_original_other,$str_replace,$link_other)
{
	global $database,$error;
	
	$db	=	& $database;
	$query = "SELECT * FROM #__article2010_new_anninhthudo WHERE id_original = ".$db->quote(trim($id_original_other));
	$db->setQuery($query);	
	$id_result	=	false;
	
	$state = 0;
	if ($db->loadObject($obj)) {		 
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