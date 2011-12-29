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
function mosControllerTTTimNhanh_CMGetNews()
{	
	global $arrErr;	
	require_once(dirname(__FILE__).DS.'..'.DS.'configs'.DS.'TTTimNhanh_CM.php');
	
	$defalutExecution = ini_get('max_execution_time');
		
	//trying to set limit to 5 minutes, because I will run the cronjob every 5 minutes
	@set_time_limit(60 * $time_exp);

	$obj_cat	=	mosModelTTTimNhanh_CMGetNews($get_existing);	
	
	@set_time_limit($defalutExecution);
	$now = date('Y-m-d H:i:s');

	if ($get_multicat) {
		// refresh
		$href	=	new href();
		$param	=	array();	
		$param['option']	=	$_REQUEST['option'];
		$param['task']		=	'getnews';
		$param['host']		=	'tt_timnhanh_chuyenmuc';
		$param['live']		=	1;
		$param['s']	=	uniqid();
		
		$refresh	=	$href->refresh($param);
		
		echo ($refresh);
		echo '<br /> catid origional: '.$obj_cat[0]->id_origional;
		echo '<br /> cat title: '.$obj_cat[0]->title;
		echo '<br /> Number content got sucessfully: '.$obj_cat[0]->number_getcontent;
		echo '<br /><hr /> <b>';
		echo '<br /> will get:<br /> ';
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
//function mosControllerTTTimNhanh_CMGetTTTN()
//{
//	global $arrErr,$mosConfig_absolute_path,$mosConfig_live_site;
//	
//	require_once(dirname(__FILE__).DS.'..'.DS.'configs'.DS.'TTTimNhanh_CM.php');
//	
//	$id_content		=	$_REQUEST['conten_id'];
//	$alias_content	=	$_REQUEST['conten_alias'];
//	$cat_alias		=	$_REQUEST['cat_alias'];
//	$begin			=	$_REQUEST['begin_get_content'].'<br />';
//	$end			=	'<br />'.$_REQUEST['end_get_content'];	
//	
//	if (!$id_content) {
//		echo $begin;
//			echo 'ERROR_GET_CONTENT_VOV|Not get content id';
//		echo $end;
//		die();
//	}
//		
//	require_once(dirname(__FILE__).DS.'..'.DS.'tables'.DS.'TTTimNhanh_chuyenmuc.php');
//	require_once(dirname(__FILE__).DS.'..'.DS.'tables'.DS.'vov_smedia2010_new.php');
//	require_once(dirname(__FILE__).DS.'..'.DS.'libraries'.DS.'helpers'.DS.'get_image.php');
//	require_once(dirname(__FILE__).DS.'..'.DS.'libraries'.DS.'helpers'.DS.'ImageResizeFactory.php');	
//	// http://tintuc.timnhanh.com/xa-hoi/lao-dong/20110504/35AB6F6D/Ngay-dai-tang-noi-thuong-nguon-song-Lam.htm
//	$data = mosModelTTTimNhanh_CMGetTTTN($parent_alias, $cat_alias, $id_content, $alias_content, $date_content, $section_id, $catid, $path_image, $link_image, $SiteID);
//	
//	if (count($arrErr)) {
//		echo $begin;
//		echo 'ERROR_GET_CONTENT_VOV|';
//			foreach ($arrErr as $err)
//			{
//				echo '<br />'.$err.'<hr />';
//			}
//		echo $end;
//	}else {
//		echo $begin;
//			echo 'SUCESSFULL';
//		echo $end;
//	}
//	die();
//}