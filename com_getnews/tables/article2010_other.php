
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

class mosArticle2010_other extends mosDBTable {
	/** @var int Primary key */
	var $id 			=	null;
	/** @var int */
	var $SiteID	=	null;	
	/** @var int */
	var $id_original		=	null;
	/** @var int */
	var $id_original_other		=	null;		
	/**
	 * code html in article of id_origional
	 *
	 * @var enum('1','2','3')
	 */
	var $str_replace	=	"";	
	/** @var string */
	var $link			=	"";	
	/**
	 * 1: article-text/html, 2: image-image/jpeg, 3: file
	 *
	 * @var int
	 */
	var $type			=	"";
	/**
	 * 1: need get; 0: not need get
	 *
	 * @var int
	 */
	var $state 		=	0	;
	/** @var string */
	var $param 			=	0	;
	/**
	 * @param database A database connector object
	 */
	function mosArticle2010_other(&$db) {
		$this->mosDBTable ( '#__article2010_other', 'id', $db );
	}	
}