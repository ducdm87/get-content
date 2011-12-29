<?php 

function mosControllerTheThaoVanHoaGetVideo()
{
	global $arrErr;	
	require_once(dirname(__FILE__).DS.'..'.DS.'configs'.DS.'getvideos.php');
	if ($_REQUEST['id_content']>46) {
		$now = date('Y-m-d H:i:s');
		echo 'SUCESSFULL:'. $now; 
		die();
	}
	if (isset($_REQUEST['id_content'])) {
		$lastGet_vovId	=	0;
	}else {
		require_once(dirname(__FILE__).DS.'..'.DS.'cache'.DS.'helpers.php');
		$cache_file	=	dirname(__FILE__).DS.'..'.DS.'cache'.DS.'data'.DS.'getvideos.php';
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

	$obj_result	=	mosModlesTheThaoVanHoaGetVideoNews();	
	
	$id_result	=	$obj_result->id_result;
	
	@set_time_limit($defalutExecution);
	$now = date('Y-m-d H:i:s');
	if (!isset($_REQUEST['id_content'])) 
		$cache_helper->update_cache_file($lastGet_vovId,0,$now);
	
	// refresh
	$href	=	new href();
	$param	=	array();	
	$param['option']	=	$_REQUEST['option'];
	$param['task']		=	'getnews';
	$param['host']		=	'getvideos';
	$param['id_content']	=	$id_result;
	$param['s']	=	uniqid();
	
	$refresh	=	$href->refresh($param);
	echo ($refresh);	
	echo '<br /> Begin: '.$id_result;
	echo '<br /> <b> Number of article got sucessfully: '.$obj_result->number_getcontent.'</b>';
	echo '<br /> Time: '.$now;
	echo '<br /> Min: 105012101';
	echo '<br /> Max: 105038832';
	echo '<br /> Time Expires(minute): '.$time_exp;
		
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
function mosControllerGetVideoContent()
{
	global $arrErr,$mosConfig_absolute_path,$mosConfig_live_site;
	
	require_once(dirname(__FILE__).DS.'..'.DS.'configs'.DS.'getvideos.php');
	
	$id_content	=	$_REQUEST['conten_id'];
	$begin		=	$_REQUEST['begin_get_content'].'<br />';
	$end		=	'<br />'.$_REQUEST['end_get_content'];	
	
	if (!$id_content) {
		echo $begin;
			echo 'ERROR_GET_CONTENT_VOV|Not get content id';
		echo $end;
		die();
	}
	$data = mosModlesTheThaoVanHoaGetUrlVideo($id_content);

	mosModlesTheThaoVanHoaGetVideo($data->url_video, $data->url_referral, $path_save = 'videos/ttvh/', $data->title);
	
	mosModlesTheThaoVanHoaUpdateVideo($id_content);
	
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

?>