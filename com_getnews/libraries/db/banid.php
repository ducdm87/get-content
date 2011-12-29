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


function mosBanidStore($host,$aid = 0,$id_origional,$type = 1,$message = null)
{
	global $database;
	$db	=	$database;
	$query	=	'INSERT into `#__article2010_banid`
					SET host = '.$db->quote($host).',
						id_origional = '.$db->quote($id_origional).',
						aid = '.$db->quote($aid).',
						type = '.$type.',
						message = '.$db->quote($message);
	$db->setQuery($query);
	$db->query();	   
}
/// $where = ' id_origional = in(0) '
function mosBanidGet($host, $arr_field, $where = null)
{
	global $database;
	$db	=	$database;
	$field	=	implode(',',$arr_field);
	$w		=	"WHERE host = ".$db->quote($host);
	if ($where) {
		$w.=	' AND '.$where;
	}
	$query	=	"SELECT $field FROM `#__article2010_banid` ". 
					$w;
	$db->setQuery($query);
	
	if (count($arr_field)>1) {
		return $db->loadObjectList();
	}else {
		return $db->loadResultArray();
	}	
}
/// $where = ' id_origional = in(0) '
function mosGetDBOBJ($tbl_name, $arr_field, $where = null)
{
	global $database;
	$db	=	$database;
	$field	=	implode(',',$arr_field);	
	$query	=	"SELECT $field FROM $tbl_name WHERE ". $where;
	$db->setQuery($query);	
	if (count($arr_field)>1) {
		return $db->loadObjectList();
	}else {
		return $db->loadResultArray();
	}	
}

function mosStoreOBJ($tbl_name,$obj_field)
{
	global $database;
	$db	=	$database;
	$fmtsql = "INSERT INTO $tbl_name SET %s ";
	$insert = array();
	foreach (get_object_vars( $obj_field ) as $k => $v) {	
		if (is_array($v) or is_object($v) or $v === NULL) {
			continue;
		}
		$insert[] = $db->NameQuote( $k ).' = '.$db->Quote( $v );
	}
	$db->setQuery( sprintf( $fmtsql, implode( ",", $insert ) ) );
	echo $db->getQuery(); 
	echo '<hr />';
//	die();
	$db->query();
}