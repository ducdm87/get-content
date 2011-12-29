<?php
/*
 * @filename 	: newsvov.php
 * @version  	: 1.0
 * @package	 	: vietbao.vn/get news/
 * @subpackage	: component
 * @license		: GNU/GPL 3, see LICENSE.php
 * @author 		: DucDM, HaiNH
 * @authorEmail	: ducdm87@binhhoang.com, hainh@binhhoang.com
 * @copyright	: Copyright (C) 2011 Vi?t b�o�. All rights reserved. 
 */

/**
 * Get new content
 *
 */

function mosControllerNewsvovGetNews()
{	
	$number	=	mosGetNumberRow('#__article2010_new_vov');
	echo $number.' rows';
	if ( $number > 30000) {	
		echo 'database is very big.(article2010_new_vov)';
		echo 'Please backup table and try agein.';
		die();
	}	
	global $arrErr;	
	require_once(dirname(__FILE__).DS.'..'.DS.'configs'.DS.'vov.php');
	
	if (isset($_REQUEST['id_content'])) {
		$lastGet_vovId	=	0;
	}else {
		require_once(dirname(__FILE__).DS.'..'.DS.'cache'.DS.'helpers.php');
		$cache_file	=	dirname(__FILE__).DS.'..'.DS.'cache'.DS.'data'.DS.'vov_cache.php';
		$cache_helper	=	new cacheHelper($cache_file);
		if (!$cache_helper->isGetContent($time_exp)) {
			return ;
		}
		$lastGet_vovId	=	$cache_helper->lastGet_Id;
	}
	// for article other
	
	$defalutExecution = ini_get('max_execution_time');
		
	//trying to set limit to 5 minutes, because I will run the cronjob every 5 minutes
	@set_time_limit(60 * $time_exp);

	$obj_result	=	mosModelNewsvovGetNews( $lastGet_vovId,$numbercontent,false);	
	
	$id_result	=	$obj_result->id_result;
	
	@set_time_limit($defalutExecution);
	$now = date('Y-m-d H:i:s');
	if (!isset($_REQUEST['id_content'])) 
		$cache_helper->update_cache_file($lastGet_vovId,0,$now);
	if ($get_old and $id_result> $id_started ) {
		// refresh
		$href	=	new href();
		$param	=	array();	
		$param['option']	=	$_REQUEST['option'];
		$param['task']		=	'getnews';
		$param['host']		=	'vovnews';
		$param['id_content']	=	$id_result;
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
		echo '<br /><hr /> List error:'. count(($arrErr));
		foreach ($arrErr as $k=> $err)
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
function mosControllerNewsvovGetVOV()
{
	global $arrErr, $arrNotice, $mosConfig_absolute_path,$mosConfig_live_site;
	
	require_once(dirname(__FILE__).DS.'..'.DS.'configs'.DS.'vov.php');
	
	$id_content	=	$_REQUEST['conten_id'];
	$begin		=	$_REQUEST['begin_get_content'].'<br />';
	$end		=	'<br />'.$_REQUEST['end_get_content'];	
	
	if (!$id_content) {
		echo $begin;
			echo 'ERROR_GET_CONTENT_VOV|Not get content id';
		echo $end;
		die();
	}
		
	require_once(dirname(__FILE__).DS.'..'.DS.'tables'.DS.'vov_article2010_new2.php');
	require_once(dirname(__FILE__).DS.'..'.DS.'tables'.DS.'vov_smedia2010_new.php');
	require_once(dirname(__FILE__).DS.'..'.DS.'libraries'.DS.'helpers'.DS.'get_image.php');
	require_once(dirname(__FILE__).DS.'..'.DS.'libraries'.DS.'helpers'.DS.'ImageResizeFactory.php');	
	
	$defalutExecution = ini_get('max_execution_time');
		
	//trying to set limit to 5 minutes, because I will run the cronjob every 5 minutes
	@set_time_limit(60 * 5);

	mosModelNewsvovGetVOV($id_content, $section_id, $catid, $path_image, $link_image, $SiteID);	
	
	@set_time_limit($defalutExecution);
	
	echo $begin;
	if (count($arrErr)) {		
		echo 'ERROR_GET_CONTENT_VOV|';
			foreach ($arrErr as $err)
			{
				echo '<br />'.$err.'<hr />';
			}
		
	}else if (count($arrNotice)) {
		echo 'NOTICE_GET_CONTENT_VOV|';
		foreach ($arrNotice as $not)
			{
				echo '<br />'.$not.'<hr />';
			}
	} else {
			echo 'SUCESSFULL';
		
	}	
	echo $end;	
	die();
}