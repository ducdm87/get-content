<?php
/*
 * @filename 	: date.php
 * @version  	: 1.0
 * @package	 	: vietbao.vn/get news/
 * @subpackage	: component
 * @license		: GNU/GPL 3, see LICENSE.php
 * @author 		: Team : Đức
 * @authorEmail	: 
 *				: ducdm87@binhhoang.com
 * @copyright	: Copyright (C) 2011 Vi?t b�o�. All rights reserved. 
 */


function date_time_ago_vn($time_ago,$time_type, $increment = 0)
{
	switch ($time_type)
	{
		case 'th':
				$date_time = mktime(date("H"), date("i"), date("s")+$increment, date("m")-$time_ago  , date("d"), date("Y"));
				break;
		case 'tu':
				$time_ago	=	7*$time_ago;
				$date_time	=	mktime(date("H"), date("i"), date("s")+$increment, date("m")  , date("d")-$time_ago, date("Y"));
				break;
		case 'gi':
				$date_time	=	mktime(date("H")-$time_ago, date("i")+$increment, date("s"), date("m")  , date("d"), date("Y"));
				break;
		case 'ph':
				$date_time	=	mktime(date("H"), date("i")-$time_ago, date("s")+$increment, date("m")  , date("d"), date("Y"));
				break;
		case 'ng':
				$date_time	=	mktime(date("H"), date("i"), date("s")+$increment, date("m")  , date("d")-$time_ago, date("Y"));
				break;
	}
	return $date_time	=	date('Y-m-d H:i:s',$date_time); 
}


function date_time_ago_en($time_ago,$time_type, $increment = 0)
{
	switch ($time_type)
	{
		case 'month':			
				$date_time = mktime(date("H"), date("i"), date("s")+$increment, date("m")-$time_ago  , date("d"), date("Y"));
				break;
		case 'week':
				$time_ago	=	7*$time_ago;
				$date_time	=	mktime(date("H"), date("i"), date("s")+$increment, date("m")  , date("d")-$time_ago, date("Y"));
				break;
		case 'hour':
				$date_time	=	mktime(date("H")-$time_ago, date("i"), date("s")+$increment, date("m")  , date("d"), date("Y"));
				break;
		case 'minute':
				$date_time	=	mktime(date("H"), date("i")-$time_ago, date("s")+$increment, date("m")  , date("d"), date("Y"));
				break;
		case 'day':
				$date_time	=	mktime(date("H"), date("i"), date("s")+$increment, date("m")  , date("d")-$time_ago, date("Y"));
				break;
	}
	return $date_time	=	date('Y-m-d H:i:s',$date_time); 	
}