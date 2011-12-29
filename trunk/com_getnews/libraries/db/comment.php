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


function mosCommentStore($aid, $domain = "", $name,$datetime,$comment, $param = '')
{
	global $database;
	$db	=	$database;
	$query	=	'INSERT into `#__article2010_comment`
					SET aid = '.$db->quote($aid).',
						domain = '.$db->quote($domain).',
						name = '.$db->quote($name).',
						created = '.$db->quote($datetime).',
						comment = '.$db->quote($comment).',
						field_unique = '.$db->quote(md5($name.$comment)).',
						param = '.$db->quote($param);
	$db->setQuery($query);
	$db->query();	
}