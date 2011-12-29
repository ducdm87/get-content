<?php
/*
 * @filename 	: getnews.php
 * @version  	: 1.0
 * @package	 	: vietbao.vn/get news/
 * @subpackage	: component
 * @license		: GNU/GPL 3, see LICENSE.php
 * @author 		: DucDM, HaiNH
 * @authorEmail	: ducdm87@binhhoang.com, hainh@binhhoang.com
 * @copyright	: Copyright (C) 2011 Vi?t b�o�. All rights reserved. 
 */
//die('stop here');
defined('_VALID_MOS') or die('Restricted access');
define( 'DS', DIRECTORY_SEPARATOR );
if (!class_exists('loadHtml')) 
{
	require('libraries/helpers/parse.php');
}
if (!class_exists('QRequest')) {
	require_once('libraries/helpers/request.php')	;
	
}
if (!class_exists('phpWebHacks')) {
	require_once('libraries/helpers/phpWebHacks.php')	;
	
}
if (!class_exists('buildTree')) {
	require_once('libraries/helpers/buildTree.php')	;
	
}
if (!class_exists('href')) {
	require_once('libraries/helpers/href.php')	;
	
}
//die('stop get news 1');
$task	=	$_REQUEST['task'];

global $database,$arrErr, $arrNotice;
$arrErr	=	array();
$arrNotice	=	array();
require_once('libraries/db/banid.php');
require_once('libraries/db/param.php');
require_once('libraries/db/comment.php');
require_once('libraries/db/other.php');
require_once('libraries/db/db.php');
mosDB($database);
require_once('libraries/db/videos.php');
require_once('libraries/class/update_other.php');
require_once('libraries/class/tidy_clean.php');
require_once('libraries/class/array_helper.php');

switch ($task)
{
	case 'repair_image':
		{
			$defalutExecution = ini_get('max_execution_time');
			$now = date('Y-m-d H:i:s');	
			echo $now;
			@set_time_limit(60 * 15);
			require_once(dirname(__FILE__).DS.'libraries'.DS.'helpers'.DS.'get_image.php');
			require_once('repair.php');
			mosRepai_image();
			$now = date('Y-m-d H:i:s');	
			echo '<br />';	echo $now;
			@set_time_limit($defalutExecution);
			die();
			break;
		}
		case 'repair_content':
		{			 
			$defalutExecution = ini_get('max_execution_time');
			$now = date('Y-m-d H:i:s');	
			echo $now;
			@set_time_limit(60 * 15);			
			require_once('repair.php');
			mosRepair_original();
			$now = date('Y-m-d H:i:s');	
			echo '<br />';	echo $now;
			@set_time_limit($defalutExecution);
			die();
			break;
		}
	case 'getcategory':
		{
			require_once('controllers/category.php');
			require_once('models/category.php');	
			$host	=	$_REQUEST['host'];			
			switch ($host) {
				case 'vovnews':
					mosControllerCategoryGetVOV();
					break;
				case 'en.vovnews':	
					mosControllerCategoryGetENVOV();
					break;	
				case 'baomoi':	
					mosControllerCategoryGetBAOMOI();
					break;	
				case 'en.vietnamnet':
					mosControllerCategoryGetENVIETNAMNET();
					break;	
				case 'ktdt':
					mosControllerCategoryGetKTDT();
					break;
				case 'thanhnien':
					mosControllerCategoryGetTHANHNIEN();
					break;
				case 'nguoilaodong':
					mosControllerCategoryGetNGUOILAODONG();
					break;
				case 'thethaovanhoa':
					mosControllerCategoryGetTheThaoVanHoa();
					break;
				case 'autonet':
					mosControllerCategoryGetAutoNet();
					break;
				case 'autopro':
					mosControllerCategoryGetAutoPro();
					break;
				case 'autotv':
					mosControllerCategoryGetAutoTV();
					break;
				case 'vnmedia':
					mosControllerCategoryGetVNmedia();
					break;
				case 'autohui':
					mosControllerCategoryGetAutoHui();
					break;
				case 'danviet':
					mosControllerCategoryGetDanViet();
					break;
				case 'tt_timnhanh':
					mosControllerCategoryGetTT_Timnhanh();
					break;
				case 'tienphong':
					mosControllerCategoryGetTienphong();
					break;
				case 'anninhthudo':					
					mosControllerCategoryGetAnninhthudo();
					break;
				case 'bongdaplus':
			 		mosControllerCategoryGetBongdaplus();
					break;
				case 'tt_timnhanh_chuyenmuc':
					mosControllerCategoryGetTT_Timnhanh_CM();
					break;
				case 'phapluattp':
					mosControllerCategoryGetPLHCM();
					break;
				case 'afamily':
					mosControllerCategoryGetAFAMILY();
					break;
			}
			break;
		}
	case 'getnews':
		{		
			$host	=	$_REQUEST['host'];
			switch ($host) {
				case 'vovnews':	
					require_once('controllers/newsvov.php');
					require_once('models/newsvov.php');	
					mosControllerNewsvovGetNews();
					break;
				case 'en.vovnews':
					require_once('controllers/newsEnvov.php');
					require_once('models/newsEnvov.php');
					mosControllerNewsEnvovGetNews();
					break;
				case 'en.baomoi':
					require_once('controllers/newsEnbaomoi.php');
					require_once('models/newsEnbaomoi.php');
					mosControllerNewsEnbaomoiGetNews();	
					break;	
				case 'baomoi':
					require_once('controllers/newsbaomoi.php');
					require_once('models/newsbaomoi.php');
					mosControllerNewsbaomoiGetNews();
					break;
				case 'ktdt':
					require_once('controllers/newsktdt.php');
					require_once('models/newsktdt.php');
					mosControllerNewsktdtGetNews();	
					break;
				case 'en.vietnamnet':
					require_once('controllers/newsEnvietnamnet.php');
					require_once('models/newsEnvietnamnet.php');
					mosControllerNewsEnvietnamnetGetNews();	
					break;
				case 'en.thanhnien':
					require_once('controllers/newsEnthanhnien.php');
					require_once('models/newsEnthanhnien.php');
					mosControllerNewsEnthanhNienGetNews();
					break;	
				case 'nguoilaodong':
					require_once('controllers/newsNguoiLaoDong.php');
					require_once('models/newsNguoiLaoDong.php');
					mosControllerNguoiLaoDongGetNews();	
					break;
				case 'thethaovanhoa':
					require_once('controllers/newsTheThaoVanHoa.php');
					require_once('models/newsTheThaoVanHoa.php');
					require_once('libraries/helpers/autocuttext.php');
					mosControllerTheThaoVanHoaGetNews();
					break;
				case 'getvideos':
					require_once('controllers/get_video.php');
					require_once('models/get_video.php');
					mosControllerTheThaoVanHoaGetVideo();
					break;
				case 'autonet':
					require_once('controllers/newsAutoNet.php');
					require_once('models/newsAutoNet.php');		
					require_once('libraries/helpers/autocuttext.php');
					mosControllerNewsautoNetGetNews();	
					break;
				case 'autopro':
					require_once('controllers/newsAutoPro.php');
					require_once('models/newsAutoPro.php');	
					require_once('libraries/helpers/autocuttext.php');
					mosControllerAutoProGetNews();	
					break;	
				case 'autohui':
					require_once('controllers/newsAutoHui.php');
					require_once('models/newsAutoHui.php');		
					require_once('libraries/helpers/autocuttext.php');
					mosControllerAutoHuiGetNews();	
					break;
				case 'autotv':
					require_once('controllers/newsAutoTV.php');
					require_once('models/newsAutoTV.php');
					require_once('libraries/helpers/autocuttext.php');
					mosControllerAutoTVGetNews();
					break;
				case 'vnmedia':
					require_once('controllers/newsVNmedia.php');
					require_once('models/newsVNmedia.php');
					require_once('libraries/helpers/autocuttext.php');
					mosControllerNewsVNMGetNews();
					break;
				case 'danviet':
					require_once('controllers/newsDanViet.php');
					require_once('models/newsDanViet.php');		
					require_once('libraries/helpers/autocuttext.php');			
					mosControllerDanVietGetNews();	
				break;	
				case 'tt_timnhanh':
					require_once('controllers/newsTTTimNhanh.php');
					require_once('models/newsTTTimNhanh.php');		
					require_once('libraries/helpers/autocuttext.php');			
					mosControllerTTTimNhanhGetNews();	
				break;	
				case 'tt_timnhanh_chuyenmuc':
					require_once('controllers/newsTTTimNhanh_chuyenmuc.php');
					require_once('models/newsTTTimNhanh_chuyenmuc.php');		
					require_once('libraries/helpers/autocuttext.php');		
					require_once('libraries/class/images.php');
					require_once('libraries/class/date.php');		
					mosControllerTTTimNhanh_CMGetNews();	
				break;	
				case 'bongdaplus':
					require_once('controllers/newsBongdaPlus.php');
					require_once('models/newsBongdaPlus.php');				
					mosControllerBongdaPlusGetNews();
				case 'anninhthudo':
					require_once('controllers/newsAnninhthudo.php');
					require_once('models/newsAnninhthudo.php');	
					require_once('libraries/class/date.php');		
					mosControllerAnninhthudoGetNews();	
				break;
				case 'phapluat_hcm':
					require_once('controllers/newsPhapluatHCM.php');
					require_once('models/newsPhapluatHCM.php');	
					require_once('libraries/class/date.php');		
					mosControllerNewsPhapluatHCMGetNews();
				break;
				case 'afamily':
					require_once('controllers/newsAfamily.php');
					require_once('models/newsAfamily.php');	
					require_once('libraries/class/date.php');		
					mosControllerNewsAfamilyGetNews();	
				break;
			}
			break;
		}
	case 'getnewsvov':
			require_once('controllers/newsvov.php');
			require_once('models/newsvov.php');
			require_once('libraries/class/images.php');
			mosControllerNewsvovGetVOV();
			break;
	case 'getnewsvov.en':
			require_once('controllers/newsEnvov.php');
			require_once('models/newsEnvov.php');
			require_once('libraries/class/images.php');
			mosControllerNewsEnvovGetVOV();
			break;
	case 'getnewsbaomoi.en':
			require_once('controllers/newsEnbaomoi.php');
			require_once('models/newsEnbaomoi.php');
			require_once('libraries/class/images.php');
			require_once('libraries/class/date.php');
			mosControllerNewsENbaomoiGetBM();
			break;
	case 'getnewsbaomoi':
			require_once('controllers/newsbaomoi.php');
			require_once('models/newsbaomoi.php');
			require_once('libraries/class/images.php');
			require_once('libraries/class/date.php');
			mosControllerNewsbaomoiGetBM();
			break;	
	case 'getnewsktdt':
			require_once('controllers/newsktdt.php');
			require_once('models/newsktdt.php');
			require_once('libraries/class/images.php');
			require_once('libraries/class/date.php');
			mosControllerNewsktdtGetKTDT();
			break;	
	case 'getnewsvnnet.en':
			require_once('controllers/newsEnvietnamnet.php');
			require_once('models/newsEnvietnamnet.php');
			require_once('libraries/class/images.php');
			require_once('libraries/class/date.php');
			mosControllerNewsVietnamnetGetVNN();
			break;
	case 'getnewsthanhnien.en':
			require_once('controllers/newsEnthanhnien.php');
			require_once('models/newsEnthanhnien.php');
			require_once('libraries/helpers/autocuttext.php');
			require_once('libraries/class/images.php');
			require_once('libraries/class/date.php');
			mosControllerNewsEnthanhNienGetVNN();
			break;
	case 'nguoilaodong.vn':
			require_once('controllers/newsNguoiLaoDong.php');
			require_once('models/newsNguoiLaoDong.php');
			require_once('libraries/class/images.php');
			require_once('libraries/class/date.php');
			mosControllerNguoiLaoDongGetNLD();		
			break;
	case 'thethaovanhoa.vn':
			require_once('controllers/newsTheThaoVanHoa.php');
			require_once('models/newsTheThaoVanHoa.php');	
			require_once('libraries/helpers/autocuttext.php');
			require_once('libraries/class/images.php');
			require_once('libraries/class/date.php');
			mosControllerTheThaoVanHoaGetTTVH();
			break;	
	case 'getvideos':
			require_once('controllers/get_video.php');
			require_once('models/get_video.php');
			mosControllerGetVideoContent();
			break;	
	case 'getnewsautonet':
			require_once('controllers/newsAutoNet.php');
			require_once('models/newsAutoNet.php');	
			require_once('libraries/helpers/autocuttext.php');
			require_once('libraries/class/images.php');
			require_once('libraries/class/date.php');
			mosControllerNewsautoNetGetAtn();		
			break;	
	case 'autopro.vn':
			require_once('controllers/newsAutoPro.php');
			require_once('models/newsAutoPro.php');	
			require_once('libraries/helpers/autocuttext.php');
			require_once('libraries/class/images.php');
			require_once('libraries/class/date.php');
			mosControllerAutoProGetATP();
			break;	
	case 'getnewsvnmedia':
			require_once('controllers/newsVNmedia.php');
			require_once('models/newsVNmedia.php');
			require_once('libraries/helpers/autocuttext.php');
			require_once('libraries/class/images.php');
			require_once('libraries/class/date.php');
			mosControllerNewsVNmediaGetVNM();
			break;
	case 'getautohui':
			require_once('controllers/newsAutoHui.php');
			require_once('models/newsAutoHui.php');	
			require_once('libraries/helpers/autocuttext.php');
			require_once('libraries/class/images.php');
			require_once('libraries/class/date.php');
			mosControllerAutoHuiGetATH();
		break;	
	case 'autotv.vn':
			require_once('controllers/newsAutoTV.php');
			require_once('models/newsAutoTV.php');	
			require_once('libraries/helpers/autocuttext.php');
			require_once('libraries/class/images.php');
			require_once('libraries/class/date.php');
			mosControllerAutoTVGetATTV();		

		break;	
	case 'danviet.vn':
			require_once('controllers/newsDanViet.php');
			require_once('models/newsDanViet.php');	
			require_once('libraries/helpers/autocuttext.php');
			require_once('libraries/class/images.php');
			require_once('libraries/class/date.php');
			mosControllerDanVietGetDV();
		break;	
	case 'tt_timnhanh.vn':
			require_once('controllers/newsTTTimNhanh.php');
			require_once('models/newsTTTimNhanh.php');	
			require_once('libraries/helpers/autocuttext.php');
			require_once('libraries/class/images.php');
			require_once('libraries/class/date.php');
			mosControllerDanVietGetDV();		
		break;	
	case 'tt_timnhanh_chuyenmuc.vn':
			require_once('controllers/newsTTTimNhanh_chuyenmuc.php');
			require_once('models/newsTTTimNhanh_chuyenmuc.php');
			require_once('libraries/helpers/autocuttext.php');
			require_once('libraries/class/images.php');
			require_once('libraries/class/date.php');
			mosControllerTTTimNhanh_CMGetTTTN();
		break;
	case 'getbongdaplus':
			require_once('controllers/newsBongdaPlus.php');
			require_once('models/newsBongdaPlus.php');
			require_once('libraries/helpers/autocuttext.php');
			require_once('libraries/class/images.php');
			require_once('libraries/class/date.php');
			mosControllerBongdaPlusGetBDP();
		break;
	case 'getnewAnninhthudo':
			require_once('controllers/newsAnninhthudo.php');
			require_once('models/newsAnninhthudo.php');
			require_once('libraries/helpers/autocuttext.php');
			require_once('libraries/class/images.php');
			require_once('libraries/class/date.php');
			mosControllerAnninhthudoGetANTD();
		break;
	case 'getnewsphapluat_hcm':
			require_once('controllers/newsPhapluatHCM.php');
			require_once('models/newsPhapluatHCM.php');
			require_once('libraries/helpers/autocuttext.php');
			require_once('libraries/class/images.php');
			require_once('libraries/class/date.php');
			mosControllerNewsPhapluatHCMGetPLHCM();
		break;	
	case 'getnewsafamily':
			require_once('controllers/newsAfamily.php');
			require_once('models/newsAfamily.php');
			require_once('libraries/helpers/autocuttext.php');
			require_once('libraries/class/images.php');
			require_once('libraries/class/date.php');
			mosControllerNewsAfamilyGetAFML();
		break;
	default:
			break;
}



function dump_data($data)
{
	if (is_array($data)){
		echo '<hr />';
		echo 'array'; 
		echo '<br />';
		echo count($data);
		echo '<br />';
		foreach ($data as $k=>$_data) {
			echo 'key: ['.$k.']:';
			if (is_array($_data) or is_object($_data)) {				
				dump_data($_data);
			}else {
				echo ' value: ';
				var_dump($_data);
			}
			echo '<br />';
		}
	}	
	
	if (is_object($data))
	{		
		echo '<hr />';
		echo 'object';
		echo '<br />';
		foreach (get_object_vars( $data ) as $k => $v) {
			echo 'key: ['.$k.']:';
			if (is_array($v) or is_object($v)) {				
				dump_data($v);
			}else {
				echo ' value: ';
				var_dump($v);
			}
			echo '<br />';
		}		
	}
}
