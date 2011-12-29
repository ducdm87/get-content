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

class mosThanhnienCategory extends mosDBTable {
	/** @var int Primary key */
	var $id 			=	null;
	/** @var int */
	var $id_origional			=	null;
	/** @var int */
	var $title	=	null;	
	/** @var string */
	var $alias_origional 			=	"";	
	/** @var string */
	var $isparent			=	"";	
	/** @var string */
	var $domain 	=	"";
	/** @var string */
	var $last_run 	=	"";
	/** @var string */
	var $lastGet_param 	=	"";
	/** @var string */
	var $publish 	=	"";
	/**
	 * @param database A database connector object
	 */
	function mosThanhnienCategory(&$db) {
		$this->mosDBTable ( '#__article2010_category_nguoilaodong', 'id', $db );
	}	
}