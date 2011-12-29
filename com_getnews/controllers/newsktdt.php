<?php
/**
 * Get new content
 *
 */

function mosControllerNewsktdtGetNews()
{
	$number	=	mosGetNumberRow('#__article2010_new_ktdt');
	echo $number.' rows';
	if ($number > 30000) {	
		echo '<br /> database is very big.(article2010_new_ktdt)';
		echo 'Please backup table and try agein.';
		die();
	}
	global $arrErr;	
	require_once(dirname(__FILE__).DS.'..'.DS.'configs'.DS.'ktdt.php');
	
	if (isset($_REQUEST['live'])) {
		//
	}else {
		require_once(dirname(__FILE__).DS.'..'.DS.'cache'.DS.'helpers.php');
		$cache_file	=	dirname(__FILE__).DS.'..'.DS.'cache'.DS.'data'.DS.'ktdt.php';
		$cache_helper	=	new cacheHelper($cache_file);
		if (!$cache_helper->isGetContent($time_exp)) {
			return ;
		}
		$now = date('Y-m-d H:i:s');	
		$cache_helper->update_cache_file(0,0,$now);	
	}
	
	//trying to set limit to 5 minutes, because I will run the cronjob every 5 minutes
	@set_time_limit(60*$time_exp);
	
	$obj_cat	=	mosModelKtdtGetData($numbercontent, $get_existing);
	
	@set_time_limit($defalutExecution);
	$now = date('Y-m-d H:i:s');
	if (!isset($_REQUEST['live']))
		$cache_helper->update_cache_file(0,0,$now);
	if ($get_multicat and $obj_cat) {
		// refresh
		$href	=	new href();
		$param	=	array();	
		$param['option']	=	$_REQUEST['option'];
		$param['task']		=	'getnews';
		$param['host']		=	'ktdt';
		$param['live']		=	'1';
		if (isset($_REQUEST['catid_origional']))
		{
			$param['catid_origional']		=	$_REQUEST['catid_origional'];
		}
		$param['s']	=	uniqid();
		
		$refresh	=	$href->refresh($param);		
		
		echo ($refresh);
				
		echo '<br /> catid origional: '.$obj_cat[0]->id_origional;
		echo '<br /> title: '.$obj_cat[0]->title;	
		echo '<br /> Time: '.$now;
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
		echo '<br /><hr /> List error';
		foreach ($arrErr as $k=>$err)
		{
			echo '<br />['.$k.']'.$err.'<hr />';
		}
	}
	die();	
}

function mosControllerNewsktdtGetKTDT()
{
	global $arrErr,$mosConfig_absolute_path,$mosConfig_live_site;
	require_once(dirname(__FILE__).DS.'..'.DS.'configs'.DS.'ktdt.php');	
	
	$id_content			=	$_REQUEST['conten_id'];
	$catid_origional	=	$_REQUEST['catid_origional'];
	$section_id	=	$_REQUEST['secid'];
	$catid		=	$_REQUEST['catid'];
	
	$cattitle_origional	=	$_REQUEST['cattitle_origional'];
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
	
	mosModelNewsGetKTDT($id_content, $catid_origional, $cattitle_origional, $section_id, $catid, $path_image, $link_image, $SiteID);
	
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
