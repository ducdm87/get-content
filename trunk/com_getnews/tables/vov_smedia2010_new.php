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

class mosVovSmedia2010_new extends mosDBTable {
	
	/** @var date time */
	var $firstRunDate 	=	null;
	/** @var date time */
	var $latestRunDate 	=	null;
	/** @var int */
	var $id				=	null;	
	/** @var id site copy 1 */
	var $aid 			=	null;	
	/** @var id site copy 2 */
	var $SiteID			=	null;	
	/** @var id site copy 2 */
	var $media_url 		=	null;	
	/** @var id site copy 2 */
	var $SourceURL 		=	null;
	/** @var int */
	var $Title 			=	null;
	/** @var name site copy */
	var $Category 		=	null;
	/** @var domain site copy */
	var $Description 	=	null;
	/** @var string */
	var $Size			=	null;	
	/** @var string */
	var $FileName 		=	null;	
	/** @var date time */
	var $Path			=	null;	
	/** @var string */
	var $FileType		=	null;
	/** @var int */
	var $MediaType 		=	null;
	/** @var int */
	var $MediaImage 	=	null;
	
	/**
	 * @param database A database connector object
	 */
	function mosVovSmedia2010_new(&$db) {
		$this->mosDBTable ( '#__smedia2010_new', 'id', $db );
	}	
}