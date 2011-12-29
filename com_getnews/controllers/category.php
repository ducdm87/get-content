<?php
//////////////////////////////////////////////////////////////////////////
/////////////////////	FOR VOV			//////////////
//////////////////////////////////////////////////////////////////////////

function mosControllerCategoryGetVOV()
{
	require_once(dirname(__FILE__).DS.'..'.DS.'tables'.DS.'vov_category.php');
	
	$url	=	'http://vov.vn/';	
	
	if (!$data	=	mosModelCategoryGetVOV($url)) {
		echo '#123. Controller category. mosControllerCategoryGetCategory. ERROR: get news';
	}
	$section_id	=	'1';
	if (!mosModelCategorySaveVOV($data,$section_id)) 
	{
		echo '#167. Controller category. mosControllerCategoryGetVOV. ERROR: save news';
		die();
	}
}


function mosControllerCategoryGetENVOV()
{
	require_once(dirname(__FILE__).DS.'..'.DS.'tables'.DS.'vov_category.php');
	
	$url	=	'http://english.vovnews.vn';
	
	if (!$data	=	mosModelCategoryGetVOV($url)) {
		echo '#123. Controller category. mosControllerCategoryGetCategory. ERROR: get news';
	}
	
	$section_id	=	'1';
	if (!mosModelCategorySaveVOV($data,$section_id)) 
	{
		echo '#167. Controller category. mosControllerCategoryGetENVOV. ERROR: save news';
		die();
	}
}
//////////////////////////////////////////////////////////////////////////////////////////////////
//////////////		FOR BAO MOI			/////////////////////
//////////////////////////////////////////////////////////////////////////////////////////////////
function mosControllerCategoryGetBAOMOI()
{
	require_once(dirname(__FILE__).DS.'..'.DS.'tables'.DS.'baomoi_category.php');
	
	$url		=	'http://www.baomoi.com/';
	$link_js	=	'http://static.baomoi.vn/JScripts/static_menu.js';
	
	if (!$data	=	mosModelCategoryGetBAOMOI($url, $link_js)) {
		echo '#123. Controller category. mosControllerCategoryGetCategory. ERROR: get news';
	}	

	if (!mosModelCategorySaveBAOMOI($data)) 
	{
		echo '#167. Controller category. mosControllerCategoryGetBAOMOI. ERROR: save news';
		die();
	}
}

////////////////////////////////////////////////////////////////////////////////////////////////
/////////////		FOR VIETNAMNET	///////////////
////////////////////////////////////////////////////////////////////////////////////////////////
function mosControllerCategoryGetENVIETNAMNET()
{	
	$url		=	'http://english.vietnamnet.vn/';	
	
	if (!$data	=	mosModelCategoryGetVNNET($url)) {
		echo '#123. Controller category. mosControllerCategoryGetCategory. ERROR: get news';
	}
	
	$section_id	=	'1';
	if (!mosModelCategorySaveVNNET($data,$section_id)) 
	{
		echo '#167. Controller category. mosControllerCategoryGetENVIETNAMNET. ERROR: save news';
		die();
	}
}

/////////////////////////////////////////////////////////////////////////////////////////
//////////		FOR THANHNIEN				////////////
/////////////////////////////////////////////////////////////////////////////////////////

function mosControllerCategoryGetTHANHNIEN()
{
	require_once(dirname(__FILE__).DS.'..'.DS.'tables'.DS.'thanhnien_category.php');
	
	if (!$data	=	mosModelCategoryGetVNTHANHNIEN()) {
		echo '#123. Controller category. mosControllerCategoryGetCategory. ERROR: get news';
	}	
	
	if (!mosModelCategorySaveVNTHANHNIEN($data)) 
	{
		echo '#167. Controller category. mosControllerCategoryGetTHANHNIEN. ERROR: save news';
		die();
	}
}
////////////////////////////////////////////////////////////////////////////////////
////////	FOR KINHTEDOTHI				/////////
////////////////////////////////////////////////////////////////////////////////////
function mosControllerCategoryGetKTDT()
{
	$url		=	'http://www.ktdt.com.vn';	
	
	if (!$data	=	mosModelCategoryGetKTDT($url)) {
		echo '#123. Controller category. mosControllerCategoryGetCategory. ERROR: get news';
	}	
	$section_id	=	'1';
	if (!mosModelCategorySaveKTDT($data,$section_id)) 
	{
		echo '#167. Controller category. mosControllerCategoryGetKTDT. ERROR: save news';
		die();
	}
}


////////////////////////////////////////////////////////////////////////////
/////////		FOR NGUOI LAO DONG			//////////
////////////////////////////////////////////////////////////////////////////
function mosControllerCategoryGetNGUOILAODONG()
{
	require_once(dirname(__FILE__).DS.'..'.DS.'tables'.DS.'nguoilaodong_category.php');
	
	if (!$data	=	mosModelCategoryGetNGUOILAODONG()) {
		echo '#123. Controller category. mosControllerCategoryGetCategory. ERROR: get news';
	}	
	
	if (!mosModelCategorySaveNGUOILAODONG($data)) 
	{
		echo '#167. Controller category. mosControllerCategoryGetNGUOILAODONG. ERROR: save news';
		die();
	}
}

////////////////////////////////////////////////////////////////////////////
/////////		FOR THE THAO VAN HOA			//////////
////////////////////////////////////////////////////////////////////////////
function mosControllerCategoryGetTheThaoVanHoa()
{
	require_once(dirname(__FILE__).DS.'..'.DS.'tables'.DS.'ttvh_category.php');
	
	if (!$data	=	mosModelCategoryGetTTVH()) {
		echo '#123. Controller category. mosControllerCategoryGetCategory. ERROR: get news';
	}	
	
	if (!mosModelCategorySaveTTVH($data)) 
	{
		echo '#167. Controller category. mosModelCategorySave. ERROR: save news';
		die();
	}
}

////////////////////////////////////////////////////////////////////////////
/////////		FOR AUTONET			//////////
////////////////////////////////////////////////////////////////////////////
function mosControllerCategoryGetAutoNet()
{
	require_once(dirname(__FILE__).DS.'..'.DS.'tables'.DS.'autonet_category.php');
	
	if (!$data	=	mosModelCategoryGetATN()) {
		echo '#123. Controller category. mosControllerCategoryGetCategory. ERROR: get news';
	}	
	
	if (!mosModelCategorySaveATN($data)) 
	{
		echo '#167. Controller category. mosModelCategorySave. ERROR: save news';
		die();
	}
}
//////////////////////////////////////////////////////////////////////////
///////		FOR AUTOPRO			//////////
//////////////////////////////////////////////////////////////////////////
function mosControllerCategoryGetAutoPro()
{
	require_once(dirname(__FILE__).DS.'..'.DS.'tables'.DS.'autopro_category.php');
	
	if (!$data	=	mosModelCategoryGetATP()) {
		echo '#123. Controller category. mosControllerCategoryGetCategory. ERROR: get news';
	}	
	
	if (!mosModelCategorySaveATP($data)) 
	{
		echo '#167. Controller category. mosModelCategorySave. ERROR: save news';
		die();
	}
}
//////////////////////////////////////////////////////////////////////////
///////		FOR AUTOTV			//////////
//////////////////////////////////////////////////////////////////////////
function mosControllerCategoryGetAutoTV()
{
	echo '';
}
//////////////////////////////////////////////////////////////////////////
///////		FOR VNMEDIA			//////////
//////////////////////////////////////////////////////////////////////////
function mosControllerCategoryGetVNmedia()
{	
	if (!$data	=	mosModelCategoryGetVNM()) {
		echo '#123. Controller category. mosControllerCategoryGetVNmedia. ERROR: get news';
	}	
	
	if (!mosModelCategorySaveVNM($data)) 
	{
		echo '#167. Controller category. mosControllerCategoryGetVNmedia. ERROR: save news';		
	}
	die();
}


//////////////////////////////////////////////////////////////////////////
///////		FOR AUTOHUI			//////////
//////////////////////////////////////////////////////////////////////////
function mosControllerCategoryGetAutoHui()
{	
	if (!$data	=	mosModelCategoryGetATH()) {
		echo '#123. Controller category. mosControllerCategoryGetCategory. ERROR: get news';
	}	
	
	if (!mosModelCategorySaveATH($data)) 
	{
		echo '#167. Controller category. mosModelCategorySave. ERROR: save news';
		die();
	}
}

//////////////////////////////////////////////////////////////////////////
///////		FOR DAN VIET			//////////
//////////////////////////////////////////////////////////////////////////
function mosControllerCategoryGetDanViet()
{
	if (!$data	=	mosModelCategoryGetDV()) {
		echo '#123. Controller category. mosControllerCategoryGetCategory. ERROR: get news';
	}	
	
	if (!mosModelCategorySaveDV($data)) 
	{
		echo '#167. Controller category. mosModelCategorySave. ERROR: save news';
		die();
	}
}
//////////////////////////////////////////////////////////////////////////
///////		FOR TIN TUC TIM NHANH			//////////
//////////////////////////////////////////////////////////////////////////
function mosControllerCategoryGetTT_Timnhanh()
{
	if (!$data	=	mosModelCategoryGetTTTN()) {
		echo '#123. Controller category. mosControllerCategoryGetCategory. ERROR: get news';
	}	
	
	if (!mosModelCategorySaveTTTN($data)) 
	{
		echo '#167. Controller category. mosModelCategorySave. ERROR: save news';
		die();
	}
} 
//////////////////////////////////////////////////////////////////////////
///////		FOR TIN TUC TIEN PHONG			//////////
//////////////////////////////////////////////////////////////////////////
function mosControllerCategoryGetTienphong()
{
	if (!$data	=	mosModelCategoryGetTP()) {
		echo '#123. Controller category. mosControllerCategoryGetCategory. ERROR: get news';
	}	
	
	if (!mosModelCategorySaveTP($data)) 
	{
		echo '#167. Controller category. mosModelCategorySave. ERROR: save news';
		die();
	}
	echo 'sucessfull';
	die();
}
//////////////////////////////////////////////////////////////////////////
///////		FOR TIN TUC AN NINH THU DO			//////////
//////////////////////////////////////////////////////////////////////////
function mosControllerCategoryGetAnninhthudo()
{
	if (!$data	=	mosModelCategoryGetANTD()) {
		echo '#123. Controller category. mosControllerCategoryGetCategory. ERROR: get news';
	}	
	
	if (!mosModelCategorySaveANTD($data)) 
	{
		echo '#167. Controller category. mosModelCategorySave. ERROR: save news';
		die();
	}
	echo 'sucessfull';
	die();
}

//////////////////////////////////////////////////////////////////////////
///////		FOR TIN TUC BONGDAPLUS			//////////
//////////////////////////////////////////////////////////////////////////
function mosControllerCategoryGetBongdaplus()
{
	if (!$data	=	mosModelCategoryGetBDP()) {
		echo '#123. Controller category. mosControllerCategoryGetCategory. ERROR: get news';
	}	
	
	if (!mosModelCategorySaveBDP($data)) 
	{
		echo '#167. Controller category. mosModelCategorySave. ERROR: save news';
		die();
	}
	echo 'sucessfull';
	die();
}
//////////////////////////////////////////////////////////////////////////
///////		FOR TIN TUC TIM NHANH CHUYEN MUC			//////////
//////////////////////////////////////////////////////////////////////////
function mosControllerCategoryGetTT_Timnhanh_CM()
{
	if (!$data	=	mosModelCategoryGetTTTNCM()) {
		echo '#123. Controller category. mosControllerCategoryGetCategory. ERROR: get news';
	}	
	
	if (!mosModelCategorySaveTTTNCM($data)) 
	{
		echo '#167. Controller category. mosModelCategorySave. ERROR: save news';
		die();
	}
}

//////////////////////////////////////////////////////////////////////////
///////		FOR PHAPLUAT TP			//////////
//////////////////////////////////////////////////////////////////////////
function mosControllerCategoryGetPLHCM()
{
	if (!$data	=	mosModelCategoryGetPLHCM()) {
		echo '#123. Controller category. mosControllerCategoryGetPLHCM. ERROR: get news';
	}	
	
	if (!mosModelCategorySavePLHCM($data)) 
	{
		echo '#167. Controller category. mosControllerCategoryGetPLHCM. ERROR: save news';
		die();
	}
}
//////////////////////////////////////////////////////////////////////////
///////		FOR PHAPLUAT TP			//////////
//////////////////////////////////////////////////////////////////////////
function mosControllerCategoryGetAFAMILY()
{
	if (!$data	=	mosModelCategoryGetAFAMILY()) {
		echo '#123. Controller category. mosControllerCategoryGetAFAMILY. ERROR: get news';
	}	
	
	if (!mosModelCategorySaveAFAMILY($data)) 
	{
		echo '#167. Controller category. mosControllerCategoryGetAFAMILY. ERROR: save news';
		die();
	}
}
