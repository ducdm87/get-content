<?php
/*
 * @filename 	: newsbaomoi.php
 * @version  	: 1.0
 * @package	 	: vietbao.vn/get news/
 * @subpackage	: component
 * @license		: GNU/GPL 3, see LICENSE.php
 * @author 		: Đức
 * @authorEmail	: ducdm87@binhhoang.com
 * @copyright	: Copyright (C) 2011 Vi?t b�o�. All rights reserved. 
 */

/**
 * Get new content
 *
 */
function mosControllerNewsGiaoducGetNews()
{	
	$number	=	mosGetNumberRow('#__article2010_new_giaoduc');
	echo $number.' rows';
	if ( $number > 30000) {
		echo '<br />database is very big.(article2010_new_giaoduc)';
		echo 'Please backup table and try again.';
		die();
	}	
	global $arrErr;		
	require_once(dirname(__FILE__).DS.'..'.DS.'configs'.DS.'giaoduc.php');
	require_once(dirname(__FILE__).DS.'..'.DS.'cache'.DS.'helpers.php');
	$cache_file	=	dirname(__FILE__).DS.'..'.DS.'cache'.DS.'data'.DS.'giaoduc_cache.php';
	$cache_helper	=	new cacheHelper($cache_file);
	
	if (isset($_REQUEST['id_content'])) {
		$lastGet_vovId	=	0;
		if (!isset($_REQUEST['end_id'])) {
			$_REQUEST['end_id']	=	0;
		}	
		if ($_REQUEST['id_content']<= $_REQUEST['end_id']) {
			$_REQUEST['id_content']	=	0;
			$_REQUEST['end_id']	=	$cache_helper->getlastGet_Id();			
		}		
	}else {		
		if (!$cache_helper->isGetContent($time_exp)) {
			return ;
		}
		$lastGet_vovId	=	$cache_helper->lastGet_Id;
		$_REQUEST['id_content']	=	0;
		$_REQUEST['end_id']	=	$lastGet_vovId;
	}
	
	$defalutExecution = ini_get('max_execution_time');	
	$start	=	date('Y-m-d H:i:s');	
	//trying to set limit to 5 minutes, because I will run the cronjob every 5 minutes
	@set_time_limit(60 * $time_exp);

	$obj_result	=	mosModelNewsGiaoducGetNews( $lastGet_vovId,$numbercontent,false);	
	$id_result	=	$obj_result->id_result;
	$end 	=	date('Y-m-d H:i:s');
	
	echo '<hr />'.$start.' => '.$end;
	@set_time_limit($defalutExecution);
	$now = date('Y-m-d H:i:s');
	if (!isset($_REQUEST['id_content']) || $_REQUEST['id_content'] == 0) 
		$cache_helper->update_cache_file($lastGet_vovId,0,$now);
	
	if ($get_old) {
		// refresh
		$href	=	new href();
		$param	=	array();	
		$param['option']	=	$_REQUEST['option'];
		$param['task']		=	'getnews';
		$param['host']		=	'giaoduc';
		$param['id_content']	=	$id_result;
		$param['end_id']	=	$_REQUEST['end_id'];
		$param['s']	=	uniqid();
		
		$refresh	=	$href->refresh($param);
		
		echo ($refresh);	
		echo '<br /> Begin: '.$id_result;
		echo '<br /> <b> Number of article got sucessfully: '.$obj_result->number_getcontent.'</b>';
		echo '<br /> Time: '.$now;
		echo '<br /> Time Expires(minute): '.$time_exp;
	}
	// show error
	if (count($arrErr)) {
		echo '<br /><hr /> List error: '.count($arrErr);
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
function mosControllerNewsGiaoducGetGD($path_image = 'images/gd285/')
{
	global $arrErr,$mosConfig_absolute_path,$mosConfig_live_site;
	require_once(dirname(__FILE__).DS.'..'.DS.'configs'.DS.'giaoduc.php');	
	
	$id_content	=	$_REQUEST['conten_id'];
	$begin		=	$_REQUEST['begin_get_content'].'<br />';
	$end		=	'<br />'.$_REQUEST['end_get_content'];	
	
	if (!$id_content) {
		echo $begin;
			echo 'ERROR_GET_CONTENT_GIAODUC|Not get content id';
		echo $end;
		die();
	}
	require_once(dirname(__FILE__).DS.'..'.DS.'tables'.DS.'vov_article2010_new2.php');
	require_once(dirname(__FILE__).DS.'..'.DS.'tables'.DS.'vov_smedia2010_new.php');
	require_once(dirname(__FILE__).DS.'..'.DS.'libraries'.DS.'helpers'.DS.'get_image.php');
	require_once(dirname(__FILE__).DS.'..'.DS.'libraries'.DS.'helpers'.DS.'ImageResizeFactory.php');	

	mosModelNewsGiaoducGetGD($id_content, $path_image, $link_image, $SiteID);
	
	if (count($arrErr)) {
		echo $begin;
		echo 'ERROR_GET_CONTENT_GIAODUC|';
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