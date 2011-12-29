<?php
/*
* @filename : getnews.php
* @version : 1.0
* @package : vietbao.vn/get news/
* @subpackage : component
* @license : GNU/GPL 3, see LICENSE.php
* @author : Team : Đức
* @authorEmail :
* : ducdm87@binhhoang.com
* @copyright : Copyright (C) 2011 Vi?t b�o�. All rights reserved.
*/

defined('_VALID_MOS') or die('Restricted access');


function mosVideosStore($aid,$id_original,$title, $domain,$url_referral,$url_video,$state,$public, $param = '',$table, $str_replace)
{
	global $database;
	$db = $database;
	$query = 'INSERT into `#__article2010_videos`
				SET aid = '.$db->quote($aid).
					' ,id_original = '.$db->quote($id_original).
					' ,title = '.$db->quote($title).
					' ,domain = '.$db->quote($domain).
					' ,url_referral = '.$db->quote($url_referral).
					' ,url_video = '.$db->quote($url_video).
					' ,str_replace = '.$db->quote($str_replace).
					' ,table_name = '.$db->quote($table).
					' ,state = '.$db->quote($state).
					' ,public = '.$db->quote($public).
					' ,param = '.$db->quote($param).'
				ON DUPLICATE KEY UPDATE
					title = '.$db->quote($title).
					' ,domain = '.$db->quote($domain).
					' ,url_referral = '.$db->quote($url_referral).
					' ,str_replace = '.$db->quote($str_replace).
					' ,table_name = '.$db->quote($table).
					' ,public = '.$db->quote($public).
					' ,param = '.$db->quote($param);	
	$db->setQuery($query);
	$db->query();
}
