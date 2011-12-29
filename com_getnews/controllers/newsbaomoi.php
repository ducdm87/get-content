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
function mosControllerNewsbaomoiGetNews()
{

	$arrSid	=	array('null','5562527','5417000','5267000','5117000','4967000');
	$arrEid	=	array('null','5417000','5267000','5117000','4967000','4817000');
	$arrTbl	=	array('null','','2','3','4','5');
	$arrIncrement	=	array('null','200780387','200925387','201075387','201225387','201375387');
//	4817000
	$arrSid	=	array('null','4817000','4717000','4617000','4517000','4417000');
	$arrEid	=	array('null','4717000','4617000','4517000','4417000','4317000');
	$arrTbl	=	array('null','','2','3','4','5');
	$arrIncrement	=	array('null','201476931','201576931','201676931','201776931','201876931');
//	201476930
//	4817002
	
	$number	=	mosGetNumberRow("#__article2010_new_baomoi".$arrTbl[$_REQUEST['sid']]);
	echo date('Y-m-d H:i:s').' '.$number.' rows';
	if ( $number > 50000) {
		echo '<br />database is very big.(article2010_new_baomoi)';
		echo 'Please backup table and try again.';
		die();
	}
	
	var_dump($_REQUEST['id_content']);
	var_dump($arrEid[$_REQUEST['sid']]);
	$a	=	$arrEid[$_REQUEST['sid']] + 20;
	var_dump($a);

	if ($_REQUEST['id_content'] <= $arrEid[$_REQUEST['sid']])
	{
		echo '<br />';
		echo 'change script ID';
		die();
	}
	
	if ( intval($_REQUEST['id_content']) < $a) {
		echo '1ac';
		$_REQUEST['id_content']	=	20 + $arrEid[$_REQUEST['sid']];
	}
	
	
	global $arrErr;	
	require_once(dirname(__FILE__).DS.'..'.DS.'configs'.DS.'baomoi.php');
	require_once(dirname(__FILE__).DS.'..'.DS.'cache'.DS.'helpers.php');
	$cache_file	=	dirname(__FILE__).DS.'..'.DS.'cache'.DS.'data'.DS.'baomoi_cache.php';
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
		$_REQUEST['end_id']	=	$lastGet_vovId;
	}
	$defalutExecution = ini_get('max_execution_time');
		
	//trying to set limit to 5 minutes, because I will run the cronjob every 5 minutes
	@set_time_limit(60*$time_exp);

	$obj_result	=	mosModelNewsbaomoiGetNews( $lastGet_vovId,$numbercontent,false, $arrTbl[$_REQUEST['sid']]);
	$id_result	=	$obj_result->id_result;
	
	@set_time_limit($defalutExecution);
	$now = date('Y-m-d H:i:s');
	if (!isset($_REQUEST['id_content']))
		$cache_helper->update_cache_file($lastGet_vovId,0,$now);
	
	if ($get_old) {
		// refresh
		$href	=	new href();
		$param	=	array();	
		$param['option']	=	$_REQUEST['option'];
		$param['task']		=	'getnews';
		$param['host']		=	'baomoi';
		$param['id_content']	=	$id_result;
		$param['end_id']	=	$_REQUEST['end_id'];
		$param['sid']	=	$_REQUEST['sid'];
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
function mosControllerNewsbaomoiGetBM($path_image = 'images/bm11/')
{
	
	$arrSid	=	array('5414000','5263000','5114000','4964000','4814000');
	$arrTbl	=	array('null','','2','3','4','5');
	
	global $arrErr,$mosConfig_absolute_path,$mosConfig_live_site;
	require_once(dirname(__FILE__).DS.'..'.DS.'configs'.DS.'baomoi.php');	
	
	$id_content	=	$_REQUEST['conten_id'];
	$begin		=	$_REQUEST['begin_get_content'].'<br />';
	$end		=	'<br />'.$_REQUEST['end_get_content'];	
	
	if (!$id_content) {
		echo $begin;
			echo 'ERROR_GET_CONTENT_BAOMOI|Not get content id';
		echo $end;
		die();
	}		
	require_once(dirname(__FILE__).DS.'..'.DS.'tables'.DS.'vov_article2010_new2.php');
	require_once(dirname(__FILE__).DS.'..'.DS.'tables'.DS.'vov_smedia2010_new.php');
	require_once(dirname(__FILE__).DS.'..'.DS.'libraries'.DS.'helpers'.DS.'get_image.php');
	require_once(dirname(__FILE__).DS.'..'.DS.'libraries'.DS.'helpers'.DS.'ImageResizeFactory.php');	
	
	mosModelNewsbaomoiGetBM($id_content, $section_id, $catid, $path_image, $link_image, $SiteID, $arrTbl[$_REQUEST['sid']]);
	
	if (count($arrErr)) {
		echo $begin;
		echo 'ERROR_GET_CONTENT_BAOMOI|';
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