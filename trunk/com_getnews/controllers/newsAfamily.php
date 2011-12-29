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

function mosControllerNewsAfamilyGetNews()
{	
	$number	=	mosGetNumberRow('#__article2010_new_afamily');
	echo $number.' rows';
	if ( $number > 30000) {	
		echo '<br /> database is very big.(article2010_new_afamily)';
		echo 'Please backup table and try again.';
		die();
	}
	global $arrErr;	
	require_once(dirname(__FILE__).DS.'..'.DS.'configs'.DS.'afamily.php');
		
	// for article other
	
	$defalutExecution = ini_get('max_execution_time');
		
	//trying to set limit to 5 minutes, because I will run the cronjob every 5 minutes
	@set_time_limit(60 * $time_exp);

	$obj_cat	=	mosModelNewsAfamilyGetNews( $get_existing);	
	
	@set_time_limit($defalutExecution);
	
	$now = date('Y-m-d H:i:s');	
	if ($get_multicat) {
		// refresh
		$href	=	new href();
		$param	=	array();
		$param['option']	=	$_REQUEST['option'];
		$param['task']		=	'getnews';
		$param['host']		=	'afamily';
		if (isset($_REQUEST['cat_id'])) {
			$param['cat_id']		=	$_REQUEST['cat_id'];	
		}
		$param['live']		=	1;		
		$param['s']	=	uniqid();
		
		$refresh	=	$href->refresh($param);
		echo ($refresh);
		
		echo '<br /> catid: '.$obj_cat[0]->id;
		echo '<br /> cat title: '.$obj_cat[0]->title;		
		echo '<br /> cat page: '.$obj_cat[0]->page;
		echo '<br />GET  Next: '.$obj_cat[0]->next;
		echo '<br /> lastGet param: '.$obj_cat[0]->lastGet_param;
		echo '<br /> <b> Number content got sucessfully: '.$obj_cat[0]->number_getcontent.'</b> ';
		echo '<br /><hr /> <b>';
		echo '<br /> will get:<br /> catid: '.$obj_cat[1]->id;
		echo '<br /> cat title: '.$obj_cat[1]->title;				
		echo '<br /> Time: '.$now;
		echo '<br /> lastGet param: '.$obj_cat[1]->lastGet_param;
		echo '<br /> Time Expires(minute): '.$time_exp.' </b>';
	}	
	// show error
	if (count($arrErr)) {		
		echo '<br /><hr /> List error:'. count($arrErr);
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
function mosControllerNewsAfamilyGetAFML()
{
	global $arrErr,$mosConfig_absolute_path,$mosConfig_live_site;
	
	require_once(dirname(__FILE__).DS.'..'.DS.'configs'.DS.'afamily.php');
	
	$cattitle_origional	=	$_REQUEST['cattitle_origional'];
	$catid_origional	=	$_REQUEST['catid_origional'];
	$id_content		=	$_REQUEST['content_id'];
	$link_content	=	$_REQUEST['content_link'];
	$alias_content	=	$_REQUEST['content_alias'];
	$title_content	=	$_REQUEST['content_title'];	
	$secid	=	$_REQUEST['secid'];
	$catid	=	$_REQUEST['catid'];
	
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
	
	mosModelNewsAfamilyGetAFML($id_content, $link_content, $alias_content, $title_content, $cattitle_origional, $catid_origional, $secid, $catid, $path_image, $link_image, $SiteID);
	
	@set_time_limit(60 * 5);	
	@set_time_limit($defalutExecution);
	
	echo $begin;
	if (count($arrErr)) {		
		echo 'ERROR_GET_CONTENT_VOV|';
		foreach ($arrErr as $err)
		{
			echo '<br />'.$err.'<hr />';
		}		
	}else {	
			echo 'SUCESSFULL';		
	}
	echo $end;
	die();
}