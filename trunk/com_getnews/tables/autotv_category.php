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

class mosAutoTVCategory extends mosDBTable {
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
		$this->mosDBTable ( '#__article2010_category_autotv', 'id', $db );
	}	
}


/*INSERT INTO `jos_article2010_category_autotv` (`id`, `id_origional`, `title`, `alias_origional`, `parent`, `domain`, `last_run`, `lastGet_param`, `publish`) VALUES
(1, 0, 'TIN TỨC', 'theloai.php?idTL=1', NULL, 'autotv.vn', '0000-00-00 00:00:00', NULL, 1),
(2, 0, 'TRONG NƯỚC', 'theloai.php?idTL=1&idLT=1', 1, 'autotv.vn', '0000-00-00 00:00:00', NULL, 1),
(3, 0, 'QUỐC TẾ', 'theloai.php?idTL=1&idLT=2', 1, 'autotv.vn', '0000-00-00 00:00:00', NULL, 1),
(4, 0, 'TIN VIDEO', 'theloai.php?idTL=1&idLT=8', 1, 'autotv.vn', '0000-00-00 00:00:00', NULL, 1),
(5, 0, 'KIẾN THỨC', 'theloai.php?idTL=2', NULL, 'autotv.vn', '0000-00-00 00:00:00', NULL, 1),
(6, 0, 'CÔNG NGHỆ', 'theloai.php?idTL=3', NULL, 'autotv.vn', '0000-00-00 00:00:00', NULL, 1),
(7, 0, 'VĂN HOÁ', 'theloai.php?idTL=4', NULL, 'autotv.vn', '0000-00-00 00:00:00', NULL, 1),
(8, 0, 'CÁC GIẢI ĐUA', 'theloai.php?idTL=5', NULL, 'autotv.vn', '0000-00-00 00:00:00', NULL, 1),
(9, 0, 'F1', 'theloai.php?idTL=5&idLT=3', 8, 'autotv.vn', '0000-00-00 00:00:00', NULL, 1),
(10, 0, 'NASCAR', 'theloai.php?idTL=5&idLT=4', 8, 'autotv.vn', '0000-00-00 00:00:00', NULL, 1),
(11, 0, 'DAKAR RALLY', 'theloai.php?idTL=5&idLT=5', 8, 'autotv.vn', '0000-00-00 00:00:00', NULL, 1),
(12, 0, 'XE VÀ NGƯỜI ĐẸP', 'theloai.php?idTL=6', NULL, 'autotv.vn', '0000-00-00 00:00:00', NULL, 1),
(13, 0, 'LÁI VÀ TRẢI NGHIỆM', 'theloai.php?idTL=7', NULL, 'autotv.vn', '0000-00-00 00:00:00', NULL, 1);*/