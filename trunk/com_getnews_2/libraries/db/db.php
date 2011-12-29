<?php
/*
 * @filename 	: getnews.php
 * @version  	: 1.0
 * @package	 	: vietbao.vn/get news/
 * @subpackage	: component
 * @license		: GNU/GPL 3, see LICENSE.php
 * @author 		: Team : Đức
 * @authorEmail	: 
 *				: ducdm87@binhhoang.com
 * @copyright	: Copyright (C) 2011 Vi?t b�o�. All rights reserved. 
 */

defined('_VALID_MOS') or die('Restricted access');


function mosGetNumberRow($tbl_name)
{
	global $database,$arrErr;
	$db	=	$database;	
	$query	=	"SELECT COUNT(*) FROM $tbl_name";
	$db->setQuery($query);
	return $db->loadResult();
}
function mosDB(& $db)
{	
	$db->setQuery("SET character_set_results = 'utf8', character_set_client = 'utf8', character_set_connection = 'utf8', character_set_database = 'utf8', character_set_server = 'utf8'");		
	$db->query();	
}