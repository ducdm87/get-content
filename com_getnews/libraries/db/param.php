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


function mosParamStore($aid,$data,$name_type,$type)
{
	global $database;
	$db	=	$database;
	$query	=	'INSERT into `#__article2010_param`
					SET aid = '.$db->quote($aid).',
						data = '.$db->quote($data).',
						name_type = '.$db->quote($name_type).',
						type = '.$db->quote($type);
	$db->setQuery($query);
	$db->query();	   
}
function mosparamGet($type, $arr_field)
{
	global $database;
	$db	=	$database;
	$field	=	implode(',',$arr_field);
	$query	=	"SELECT $field 
					FROM `#__article2010_param` 
					WHERE type = ".$db->quote($type);
	$db->setQuery($query);
	if (count($arr_field)>1) {
		return $db->loadObjectList();
	}else {
		return $db->loadResultArray();
	}	
}