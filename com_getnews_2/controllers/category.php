<?php

//////////////////////////////////////////////////////////////////////////
///////		FOR BAODATVIET			//////////
//////////////////////////////////////////////////////////////////////////
function mosControllerCategoryGetBaodatviet()
{	
	if (!$data	=	mosModelCategoryGetBDV()) {
		echo '#123. Controller category. mosControllerCategoryGetBaodatviet. ERROR: get news';
	}	
	
	if (!mosModelCategorySaveBDV($data)) 
	{
		echo '#167. Controller category. mosControllerCategoryGetBaodatviet. ERROR: save news';		
	}
	echo 'get category for baodatviet sucessfully';
}

//////////////////////////////////////////////////////////////////////////
///////		FOR GIAODUC.NET.VN			//////////
//////////////////////////////////////////////////////////////////////////
function mosControllerCategoryGetGiaoduc()
{	
	if (!$data	=	mosModelCategoryGetGD()) {
		echo '#123. Controller category. mosControllerCategoryGetGiaoduc. ERROR: get news';
	}	
	
	if (!mosModelCategorySaveGD($data)) 
	{
		echo '#167. Controller category. mosControllerCategoryGetGiaoduc. ERROR: save news';		
	}
	echo 'get category for GIAODUC.NET.VN sucessfully';
}
//////////////////////////////////////////////////////////////////////////
///////		FOR NGUOIDUATIN			//////////
//////////////////////////////////////////////////////////////////////////
function mosControllerCategoryGetnguoiduatin()
{	
	if (!$data	=	mosModelCategoryGetNDT()) {
		echo '#123. Controller category. mosControllerCategoryGetnguoiduatin. ERROR: get news';
	}	
	
	if (!mosModelCategorySaveNDT($data)) 
	{
		echo '#167. Controller category. mosControllerCategoryGetnguoiduatin. ERROR: save news';		
	}
	echo 'get category for NGUOIDUATIN.VN sucessfully';
	die();
}
//////////////////////////////////////////////////////////////////////////
///////		FOR VIETNAMPLUS			//////////
//////////////////////////////////////////////////////////////////////////
function mosControllerCategoryGetVietnamplus()
{	
	if (!$data	=	mosModelCategoryGetVNP()) {
		echo '#123. Controller category. mosControllerCategoryGetVietnamplus. ERROR: get news';
	}	
	
	if (!mosModelCategorySaveVNP($data)) 
	{
		echo '#167. Controller category. mosControllerCategoryGetVietnamplus. ERROR: save news';		
	}
	echo 'get category for VIETNAMPLUS.VN sucessfully';
	die();
}
//////////////////////////////////////////////////////////////////////////
///////		FOR VIR			//////////
//////////////////////////////////////////////////////////////////////////
function mosControllerCategoryGetvir()
{	
	if (!$data	=	mosModelCategoryGetVIR()) {
		echo '#123. Controller category. mosControllerCategoryGetGiaoduc. ERROR: get news';
	}	
	if (!mosModelCategorySaveVIR($data)) 
	{
		echo '#167. Controller category. mosControllerCategoryGetGiaoduc. ERROR: save news';		
	}
	echo 'get category for GIAODUC.NET.VN sucessfully';
}
//////////////////////////////////////////////////////////////////////////
///////		FOR VIR			//////////
//////////////////////////////////////////////////////////////////////////
function mosControllerCategoryGetVneconomy()
{	
	if (!$data	=	mosModelCategoryGetVneconomy()) {
		echo '#123. Controller category. mosControllerCategoryGetVneconomy. ERROR: get news';
	}	
	if (!mosModelCategorySaveVneconomy($data)) 
	{
		echo '#167. Controller category. mosControllerCategoryGetVneconomy. ERROR: save news';		
	}
	echo 'get category for GIAODUC.NET.VN sucessfully';
	die();
}