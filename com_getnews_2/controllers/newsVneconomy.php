<?php
/*
 * @filename 	: newsbaomoi.php
 * @version  	: 1.0
 * @package	 	: vietbao.vn/get news/
 * @subpackage	: component
 * @license		: GNU/GPL 3, see LICENSE.php
 * @author 		: �?ức
 * @authorEmail	: ducdm87@binhhoang.com
 * @copyright	: Copyright (C) 2011 Vi?t b�o�. All rights reserved. 
 */

/**
 * Get new content
 *
 */
function mosControllerNewsVneconomyGetNews()
{
	$number	=	mosGetNumberRow('#__article2010_new_vneconomy');
	echo $number.' rows';
	if ( $number > 30000) {
		echo '<br />database is very big.(article2010_new_vneconomy)';
		echo 'Please backup table and try again.';
		die();
	}
	global $arrErr;	
	require_once(dirname(__FILE__).DS.'..'.DS.'configs'.DS.'vneconomy.php');
	
	$now = date('Y-m-d H:i:s');
	echo '<br />'.$now;
	
	$defalutExecution = ini_get('max_execution_time');		
	//trying to set limit to 5 minutes, because I will run the cronjob every 5 minutes
	@set_time_limit(60*$time_exp);
	$obj_cat	=	mosModelNewsVneconomyGetNews($get_existing);	
	@set_time_limit($defalutExecution);	
		
	$now = date('Y-m-d H:i:s');
	echo ' => '.$now;
	
	if ($get_multicat and $obj_cat) {
		// refresh
		$href	=	new href();
		$param	=	array();	
		$param['option']	=	$_REQUEST['option'];
		$param['task']		=	'getnews';
		$param['host']		=	'vneconomy';
		$param['live']		=	'1';
		if (isset($_REQUEST['catid']))
		{
			$param['catid']		=	$_REQUEST['catid'];
		}
		$param['s']	=	uniqid();
		
		$refresh	=	$href->refresh($param);		
		
		echo ($refresh);				
		echo '<br /> catid: '.$obj_cat[0]->id;
		echo '<br /> title: '.$obj_cat[0]->title;	
		echo '<br /> Time: '.$now;
		echo '<br /> Page: '.$obj_cat[0]->page;
		echo '<br /> Get Next: '.$obj_cat[0]->next;
		echo '<br /> Get Old: '.$obj_cat[0]->old;
		echo '<br /> <b> Number of article got sucessfully: '.$obj_cat[0]->number_getcontent.'</b>';
		echo '<br /> Time Expires(minute): '.$time_exp;
		echo '<br /><hr /><b>';
		echo 'Next run';
		echo '<br /> catid origional: '.$obj_cat[1]->id_origional;
		echo '<br /> title: '.$obj_cat[1]->title;	
		echo '<br /> Time: '.$now;
		echo '<br /> Time Expires(minute): '.$time_exp.'</b>';
	}
		
	// show error
	if (count($arrErr)) {		
		echo '<br /><hr /> List error: '. count($arrErr);
		foreach ($arrErr as $k=>$err)
		{
			echo '<br />['.$k.']'.$err.'<hr />';
		}
	}
	die();	
}
/**
 * Get one content
 *
 * @param string $path_image. store image
 */
function mosControllerNewsnewsVneconomyGetVNE($path_image = 'images/vne360/')
{
	global $arrErr,$mosConfig_absolute_path,$mosConfig_live_site;
	require_once(dirname(__FILE__).DS.'..'.DS.'configs'.DS.'vneconomy.php');	
	
	$id_content	=	$_REQUEST['content_id'];
	$secid		=	$_REQUEST['secid'];
	$catid		=	$_REQUEST['catid'];
	$catid_origional	=	$_REQUEST['catid_origional'];
	$content_link	=	$_REQUEST['content_link'];
	$content_alias	=	$_REQUEST['content_alias'];
	$content_title	=	$_REQUEST['content_title'];
	$page			=	$_REQUEST['page'];
	
	$begin		=	$_REQUEST['begin_get_content'].'<br />';
	$end		=	'<br />'.$_REQUEST['end_get_content'];	

	if (!$id_content) {
		echo $begin;
			echo 'ERROR_GET_CONTENT_C2F|Not get content id';
		echo $end;
		die();
	}		
	require_once(dirname(__FILE__).DS.'..'.DS.'tables'.DS.'vov_article2010_new2.php');
	require_once(dirname(__FILE__).DS.'..'.DS.'tables'.DS.'vov_smedia2010_new.php');
	require_once(dirname(__FILE__).DS.'..'.DS.'libraries'.DS.'helpers'.DS.'get_image.php');
	require_once(dirname(__FILE__).DS.'..'.DS.'libraries'.DS.'helpers'.DS.'ImageResizeFactory.php');		
	
	mosModelNewsVneconomyGetVNE($id_content, $content_link, $content_title, $content_alias, $page, $secid, $catid, $catid_origional, $path_image, $link_image, $SiteID);
	
	if (count($arrErr)) {
		echo $begin;
		echo 'ERROR_GET_CONTENT_C2F|';
			foreach ($arrErr as $err)
			{
				echo '<br />'.$err.'<hr />';
			}
		echo $end;
	}else {
		echo $begin;
			echo 'SUCESSFULL';
		echo $end;
	}
	die();
}