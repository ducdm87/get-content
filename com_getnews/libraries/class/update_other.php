<?php

function mosUpdateOther($id_original, $title, $contenid, $tbl_name = '#__article2010_new_afamily')
{
	global $database, $arrErr;
	$db	=	$database;
	
	if (!$db->query()) {
		echo $db->getErrorMsg();
	}	
	$query	=	'SELECT * 
					FROM `#__article2010_other` '.
					' WHERE id_original_other = '.$db->quote($id_original).
					' 	AND state = 1';
	$db->setQuery($query);

	$arr_obj	=	$db->loadObjectList();
	
	
	$link_content = '<a title=\''.str_replace(array('&gt;','\''),array(' ','"'),$title).'\' href="/index.php?option=com_content&task=view&id=' . $contenid.'" >'.$title."</a>";
	for ($i=0; $i<count($arr_obj); $i++)
	{
		$obj	=	$arr_obj[$i];
		// get id, introtext,fulltext original
		$query	=	"SELECT `id`,`introtext`,`fulltext` FROM $tbl_name WHERE `id_original` = ". $db->quote($obj->id_original);
		$db->setQuery($query);
		if (!$db->loadObject($obj_root)) {
			return false;
		}
		// replace
		$obj_root->introtext	=	str_replace($obj->str_replace, $link_content, $obj_root->introtext);
				
		$obj_root->fulltext		=	str_replace($obj->str_replace, $link_content, $obj_root->fulltext);		
		// update for original
		$query	=	"UPDATE $tbl_name ".
					" SET `introtext` = ". $db->quote($obj_root->introtext).
					"	, `fulltext` = ". $db->quote($obj_root->fulltext) .
					" WHERE id= ". $db->quote($obj_root->id);
		$db->setQuery($query);
		if ($db->query()) {
			// update for other
			$query	=	"UPDATE `#__article2010_other` SET state = 0 WHERE id = $obj->id";
			$db->setQuery($query);
	
			$db->query();	
		}		
	}

}