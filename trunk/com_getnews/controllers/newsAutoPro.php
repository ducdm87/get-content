<?php
/*
 * @filename 	: newsvov.php
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
function mosControllerAutoProGetNews()
{	
	global $arrErr;	
	require_once(dirname(__FILE__).DS.'..'.DS.'configs'.DS.'AutoPro.php');
	$number	=	mosGetNumberRow('#__article2010_new_autopro');
	echo $number.' rows';
	if (isset($_REQUEST['live'])) {
		$lastGet_vovId	=	0;
	}else {
		require_once(dirname(__FILE__).DS.'..'.DS.'cache'.DS.'helpers.php');
		$cache_file	=	dirname(__FILE__).DS.'..'.DS.'cache'.DS.'data'.DS.'AutoPro.php';
		$cache_helper	=	new cacheHelper($cache_file);
		if (!$cache_helper->isGetContent($time_exp)) {
			return ;
		}		
		$lastGet_vovId	=	0;
		$now = date('Y-m-d H:i:s');	
		$cache_helper->update_cache_file(0,0,$now);
	}
	
	$defalutExecution = ini_get('max_execution_time');
		
	//trying to set limit to 5 minutes, because I will run the cronjob every 5 minutes
	@set_time_limit(60 * $time_exp);

	$obj_cat	=	mosModelAutoProGetNews($get_existing);	
	
	@set_time_limit($defalutExecution);
	$now = date('Y-m-d H:i:s');
	if (!isset($_REQUEST['live'])) 
		$cache_helper->update_cache_file($lastGet_vovId,0,$now);
	if ($get_multicat) {
		// refresh
		$href	=	new href();
		$param	=	array();	
		$param['option']	=	$_REQUEST['option'];
		$param['task']		=	'getnews';
		$param['host']		=	'autopro';
		$param['live']		=	1;
		$param['s']	=	uniqid();
		if (isset($_REQUEST['id']))
		{
			$param['id']	=	$_REQUEST['id'];
		}
		$refresh	=	$href->refresh($param);
		
		echo ($refresh);
		echo '<br /> catid origional: '.$obj_cat[0]->id_origional;
		echo '<br /> cat title: '.$obj_cat[0]->title;
		echo '<br /> Number content got sucessfully: '.$obj_cat[0]->number_getcontent;
		echo '<br /><hr /> <b>';
		echo '<br /> will get:<br /> catid origional: '.$obj_cat[1]->id_origional;
		echo '<br /> cat title: '.$obj_cat[1]->title;
		echo '<br /> cat alias: '.$obj_cat[1]->alias_origional;
		echo '<br /> Time: '.$now;
		echo '<br /> lastGet param: '.$obj_cat[1]->lastGet_param;
		echo '<br /> Time Expires(minute): '.$time_exp.' </b>';

	}	
	// show error
	if (count($arrErr)) {		
		echo '<br /><hr /> List error';
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
function mosControllerAutoProGetATP()
{
	global $arrErr,$mosConfig_absolute_path,$mosConfig_live_site;
	
	require_once(dirname(__FILE__).DS.'..'.DS.'configs'.DS.'AutoPro.php');
	
	$id_content		=	$_REQUEST['conten_id'];
	$alias_content	=	$_REQUEST['conten_alias'];
	$alias_cat		=	$_REQUEST['cat_alias'];
	$begin			=	$_REQUEST['begin_get_content'].'<br />';
	$end			=	'<br />'.$_REQUEST['end_get_content'];	
	
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
	
	$data = mosModelAutoProGetATP($id_content, $alias_content, $alias_cat, $section_id, $catid, $path_image, $link_image, $SiteID);
	
	if (count($arrErr)) {
		echo $begin;
		echo 'ERROR_GET_CONTENT_VOV|';
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