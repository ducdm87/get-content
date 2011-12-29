
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

class mosArticle2010_new_autohui extends mosDBTable {
	
	/** @var date time */
	var $firstRunDate 	=	"";
	/** @var date time */
	var $latestRunDate 	=	null;
	/** @var int */
	var $id				=	null;	
	/** @var id site copy 1 */
	var $id_hec 		=	null;	
	/** @var id site copy 2 */
	var $id_original	=	null;	
	/** @var id site copy 2 */
	var $id_original2 	=	null;
	/** @var int */
	var $SiteID 		=	null;
	/** @var name site copy */
	var $SiteName 		=	null;
	/** @var domain site copy */
	var $Domain 		=	null;
	/** @var string */
	var $SourceURL		=	null;	
	/** @var string */
	var $NavigatorPath 	=	null;	
	/** @var date time */
	var $created		=	null;	
	/** @var string */
	var $authors		=	null;
	/** @var int */
	var $author_url 	=	null;
	
	/** @var date time */
	var $author_id	 	=	null;
	/** @var date time */
	var $publish_up 	=	null;
	/** @var int */
	var $title			=	null;	
	/** @var id site copy 1 */
	var $title_alias 	=	null;	
	/** @var id site copy 2 */
	var $SubTitle		=	null;	
	/** @var id site copy 2 */
	var $introtext	 	=	null;
	/** @var int */
	var $fulltext 		=	null;
	/** @var name site copy */
	var $sectionid 		=	null;
	/** @var domain site copy */
	var $sectionid_original		=	null;
	/** @var string */
	var $SectionName	=	null;	
	/** @var string */
	var $SectionNameNew =	null;	
	/** @var date time */
	var $SectionPath	=	null;	
	/** @var string */
	var $catid			=	null;
	/** @var int */
	var $CatName 		=	null;
	
	/** @var date time */
	var $catid_original	=	null;
	/** @var date time */
	var $CatNameNew 	=	null;
	/** @var int */
	var $CatPath		=	null;	
	/** @var id site copy 1 */
	var $image_front 	=	null;	
	/** @var id site copy 2 */
	var $images		=	null;	
	/** @var id site copy 2 */
	var $metakey	 	=	null;
	/** @var int */
	var $Keywords2 		=	null;
	/** @var name site copy */
	var $metadesc 		=	null;
	/** @var domain site copy */
	var $hits			=	null;
	/** @var string */
	var $sefu			=	null;	
	/** @var string */
	var $topic_id 		=	null;	
	/** @var date time */
	var $topic_id_new	=	null;	
	/** @var string */
	var $topic_name		=	null;
	/** @var int */
	var $topic_list 	=	null;
	
	var $Comment		=	null;
	/** @var string */
	var $PageHTML		=	null;	
	/** @var string */
	var $reference 		=	null;	
	/** @var date time */
	var $note			=	null;	
	/** @var string */
	var $status			=	null;
	
	/**
	 * @param database A database connector object
	 */
	function mosArticle2010_new_autohui(&$db) {
		$this->mosDBTable ( '#__article2010_new_autohui', 'id', $db );
	}	
}