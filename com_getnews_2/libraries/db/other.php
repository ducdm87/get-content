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
require_once(dirname(__FILE__).DS.'..'.DS.'..'.DS.'tables'.DS.'article2010_other.php');

function mosOtherStore($SiteID, $id_original, $id_original_other, $str_replace, $link_other, $type, $state)
{
	global $database,$error;
	
	$db	=	& $database;

	$query = "INSERT INTO #__article2010_other
					SET SiteID=".$db->Quote($SiteID).",
					id_original=".$db->quote($id_original).",
					id_original_other=".$db->quote($id_original_other).",
					str_replace=".$db->Quote($str_replace).",
					link=".$db->Quote($link_other).",
					type=".$type.",
					state=".$state." 
				ON DUPLICATE KEY UPDATE 
					SiteID=".$db->Quote($SiteID).", 
					str_replace=".$db->Quote($str_replace).", 
					id_original_other=".$db->quote($id_original_other).", 
					type=".$type.", 
					state=".$state; 
	
	$db->setQuery ( $query );
//	echo $db->getQuery();
//	echo '<hr />';
	if (!$db->query()) {
		$error->arr_err[]	=	"Error insert or update data ".$query;
		return false;
	}
	return true;
}