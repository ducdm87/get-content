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

class mosVovCategory extends mosDBTable {
	/** @var int Primary key */
	var $id 			=	null;
	/** @var int */
	var $parent			=	null;
	/** @var int */
	var $jl_category	=	null;	
	/** @var string */
	var $title 			=	"";	
	/** @var string */
	var $link			=	"";	
	/** @var string */
	var $getcontent 	=	"";
	/**
	 * @param database A database connector object
	 */
	function mosVovCategory(&$db) {
		$this->mosDBTable ( '#__article2010_category_vov', 'id', $db );
	}	
}