
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

class mosVovMedia extends mosDBTable {
	/** @var int Primary key */
	var $id 			=	null;
	/** @var int */
	var $vov_content	=	null;	
	/** @var int */
	var $jl_content		=	null;		
	/**
	 * 1: thumb, 2: full, 3: video
	 *
	 * @var enum('1','2','3')
	 */
	var $type 			=	"";	
	/** @var string */
	var $path			=	"";	
	/** @var string */
	var $link 			=	"";
	/** @var string */
	var $vov_link 		=	0	;
	/** @var string */
	var $name 			=	0	;
	/**
	 * @param database A database connector object
	 */
	function mosVovMedia(&$db) {
		$this->mosDBTable ( '#__vov_media', 'id', $db );
	}	
}