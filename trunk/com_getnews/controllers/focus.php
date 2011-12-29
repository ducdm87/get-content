<?php

function mosControlerGetFocus()
{
	$arr_category = GetCategory();
	
	for ($i=0;$i<count($arr_category);$i++){
		$arr_article	=	GetFocus($arr_category[$i]);
		for ($j=0;$j<count($arr_article);$j++){
			SaveFocus ($arr_article[$j]);
		}
	}
	
}