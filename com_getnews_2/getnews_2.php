<?php
/*

http://localhost/getnews_content/index.php?option=com_getnews_2&task=getnews&host=phapluatxahoi

 * @filename 	: getnews.php
 * @version  	: 1.0
 * @package	 	: vietbao.vn/get news/
 * @subpackage	: component
 * @license		: GNU/GPL 3, see LICENSE.php
 * @author 		: DucDM, HaiNH
 * @authorEmail	: ducdm@binhhoang.com, hainh@binhhoang.com
 * @copyright	: Copyright (C) 2011 Vi?t b�o�. All rights reserved. 
 */

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
//die('stop get news 2');
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
//
//die('stop here');

switch ($task)
{
	case 'process_image':
		{
			require_once('repair.php');
			mosProcess_image();
		}
	case 'repair_content':
		{			 
			$defalutExecution = ini_get('max_execution_time');
			$now = date('Y-m-d H:i:s');	
			echo $now;
			@set_time_limit(60 * 200);			
			require_once('repair.php');
			mosRepair_antd();
			$now = date('Y-m-d H:i:s');	
			echo '<br />';	echo $now;
			@set_time_limit($defalutExecution);
			global $arrErr;
			if (count($arrErr)) {				
					foreach ($arrErr as $err)
					{
						echo '<br />'.$err.'<hr />';
					}
			}else {				
				echo 'SUCESSFULL';
			}
			die();
			break;
		}
	case 'getcategory':
		{
			require_once('controllers/category.php');
			require_once('models/category.php');	
			$host	=	$_REQUEST['host'];			
			switch ($host) {
				case 'baodatviet':
					mosControllerCategoryGetBaodatviet();
					break;	
				case 'giaoduc':
					mosControllerCategoryGetGiaoduc();
					break;	
				case 'vir':
					mosControllerCategoryGetvir();
					break;		
				break;	
				case 'nguoiduatin':
					mosControllerCategoryGetnguoiduatin();
					break;
				case 'vietnamplus':
					mosControllerCategoryGetVietnamplus();
					break;
				case 'vneconomy':
					mosControllerCategoryGetVneconomy();
					break;				
			}
			break;
		}
	case 'getnews':
		{		
			$host	=	$_REQUEST['host'];
			switch ($host) {
				case 'baodatviet':
				{
					require_once('controllers/newsBaodatviet.php');
					require_once('models/newsBaodatviet.php');	
					mosControllerNewsBDVGetNews();
					break;
				}
				case 'giaoduc':
				{
					require_once('controllers/newsGiaoduc.php');
					require_once('models/newsGiaoduc.php');	
					mosControllerNewsGiaoducGetNews();
					break;
				}
				case 'vir':
				{
					require_once('controllers/newsVIR.php');
					require_once('models/newsVIR.php');	
					mosControllerNewsVIRGetNews();
					break;
				}
				case 'nguoiduatin':
				{
					require_once('controllers/newsNguoiduatin.php');
					require_once('models/newsNguoiduatin.php');	
					mosControllerNewsNguoiduatinGetNews();
					break;
				}
				case 'vietnamplus':
				{
					require_once('controllers/newsVietnamplus.php');
					require_once('models/newsVietnamplus.php');	
					mosControllerNewsVietnamplusGetNews();
					break;
				}
				case 'cafef':
				{
					require_once('controllers/newsCafef.php');
					require_once('models/newsCafef.php');	
					mosControllerNewsCafefGetNews();
					break;
				}
				case 'vinacorp':
				{
					require_once('controllers/newsVinacorp.php');
					require_once('models/newsVinacorp.php');	
					mosControllerNewsVinacorpGetNews();
					break;
				}
				case 'vneconomy':
				{
					require_once('controllers/newsVneconomy.php');
					require_once('models/newsVneconomy.php');	
					mosControllerNewsVneconomyGetNews();
					break;
				}
			}
			break;
		}
	case 'getbaodatviet':
		{
			require_once('controllers/newsBaodatviet.php');
			require_once('models/newsBaodatviet.php');
			require_once('libraries/class/images.php');
			mosControllerNewsBaodatvietGetBDV();
			break;	
		}
	case 'getnewsgiaoduc':
		{
			require_once('controllers/newsGiaoduc.php');
			require_once('models/newsGiaoduc.php');
			require_once('libraries/class/images.php');
			require_once('libraries/helpers/autocuttext.php');
			mosControllerNewsGiaoducGetGD();
			break;	
		}
	case 'getnewsvir':
		{
			require_once('controllers/newsVIR.php');
			require_once('models/newsVIR.php');
			require_once('libraries/class/images.php');
			require_once('libraries/helpers/autocuttext.php');
			mosControllerNewsVIRGetVIR();
			break;	
		}
	case 'getnewsNDT':
		{
			require_once('controllers/newsNguoiduatin.php');
			require_once('models/newsNguoiduatin.php');	
			require_once('libraries/class/images.php');
			require_once('libraries/helpers/autocuttext.php');
			mosControllerNewsNguoiduatinGetNDT();
			break;	
		}
	case 'getnewsVNP':
		{
			require_once('controllers/newsVietnamplus.php');
			require_once('models/newsVietnamplus.php');	
			require_once('libraries/class/images.php');
			require_once('libraries/helpers/autocuttext.php');
			mosControllerNewsnewsVietnamplusGetVNP();
			break;	
		}
	case 'getnewsC2F':
		{
			require_once('controllers/newsCafef.php');
			require_once('models/newsCafef.php');	
			require_once('libraries/class/images.php');
			require_once('libraries/helpers/autocuttext.php');
			mosControllerNewsnewsCafefGetC2F();
			break;	
		}
	case 'getnewsVNC':
		{
			require_once('controllers/newsVinacorp.php');
			require_once('models/newsVinacorp.php');	
			require_once('libraries/class/images.php');
			require_once('libraries/helpers/autocuttext.php');
			mosControllerNewsnewsVinacorpGetVNC();
			break;	
		}	
	case 'getnewsVNE':
		{
			require_once('controllers/newsVneconomy.php');
			require_once('models/newsVneconomy.php');	
			require_once('libraries/class/images.php');
			require_once('libraries/helpers/autocuttext.php');
			mosControllerNewsnewsVneconomyGetVNE();
			break;	
		}
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