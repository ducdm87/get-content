<?php
if(!class_exists('HTMLCleaner'))
{
	require_once('HTMLCleaner.php');
}
function mostidy_clean($str_in = '',$remove_script = true, $remove_table = true){

	/*	$str_in	=	'<div>demo</div> <p>demo 1</p> <p>demo 2</p> <p>demo <div> demo 3</div> <div> demo 4</div> <p> demo 5';
	echo htmlspecialchars($str_in);
	echo '<br /><hr />';
	echo $str_in;*/	
	if ($remove_script) {
		$str_in	=	preg_replace('/<script[^>]*>.*?<\/script>/ism','<!--remove script here-->',$str_in);
	}
	$str_in	=	str_replace(array("\r\n","\t"),array(' ',' '),$str_in);
	$cleaner=new HTMLCleaner();

		$cleaner->Options['UseTidy']=true;
		$cleaner->Options['OutputXHTML']=false;
		$cleaner->Options['Optimize']=true;
		$cleaner->Options['IsWord']=false;
		if ($remove_table) {
			$cleaner->Tag_whitelist='<p><br><hr><blockquote>'.
									'<b><i><u><sub><sup><strong><em><tt><var>'.
									'<code><xmp><cite><pre><abbr><acronym><address><samp>'.
									'<fieldset><legend>'.
									'<a><img><h1><h2><h3><h4><h4><h5><h6>'.
									'<ul><ol><li><dl><dt><frame><frameset>'.
									'<form><input><select><option><optgroup><button><textarea>';	
		}else {
			$cleaner->Tag_whitelist='<table><tbody><thead><tfoot><tr><th><td><colgroup><col><p><br><hr><blockquote>'.
									'<b><i><u><sub><sup><strong><em><tt><var>'.
									'<code><xmp><cite><pre><abbr><acronym><address><samp>'.
									'<fieldset><legend>'.
									'<a><img><h1><h2><h3><h4><h4><h5><h6>'.
									'<ul><ol><li><dl><dt><frame><frameset>'.
									'<form><input><select><option><optgroup><button><textarea>';
		}
		
	//	
		$cleaner->TidyConfig['indent']=true;
		$cleaner->TidyConfig['output-xhtml']=false;
		$cleaner->TidyConfig['show-body-only']=true;
		$cleaner->TidyConfig['hide-comments']=false;
		$cleaner->TidyConfig['clean']=true;
		$cleaner->TidyConfig['drop-proprietary-attributes']=false;	
		$cleaner->TidyConfig['wrap']=0;
	$cleaner->html=$str_in;
/**
 * The encoding parameter sets the encoding for input/output documents. 
 * The possible values for encoding are: ascii, latin0, latin1, raw, utf8, iso2022, mac,
 * win1252, ibm858, utf16, utf16le, utf16be, big5, and shiftjis. 
**/
	$cleanHTML=$cleaner->cleanUp('utf8');
	$return	=	is_object($cleanHTML)?$cleanHTML->value:$cleanHTML;
	$return	=	str_replace(array("\r\n","\t"),array(' ',' '),$return);
	
	return $return;	
}
?>
