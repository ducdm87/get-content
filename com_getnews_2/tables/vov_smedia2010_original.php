<?php
/**
 * @version $Id: joomla.php 9997 2008-02-07 11:27:04Z eddieajau $
 * @package Joomla
 * @copyright Copyright (C) 2005 Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 */

// no direct access
defined ( '_VALID_MOS' ) or die ( 'Restricted access' );

class mosVovSmedia2010_original extends mosDBTable {
	
	/** @var int */
	var $id				=	null;
	/** @var int */
	var $aid 			=	null;
	/** @var string */
	var $SiteID			=	null;
	/** @var string */
	var $PageHTML 		=	null;
	/** @var string */
	var $url 			=	null;
	
	/**
	 * @param database A database connector object
	 */
	function mosVovSmedia2010_original(&$db) {
		$this->mosDBTable ( '#__smedia2010_original', 'id', $db );
	}
}