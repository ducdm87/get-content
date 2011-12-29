<?php
if (!class_exists('loadHtml')) 
{
	//require('libraries/helpers/parse.php');
}
class buildTreeVOV
{
	var $data	=	'';
	var $tree	=	null;
	function buildTreeVOV($reg = null )
	{		
		$this->tree	=	array();		
	}
	function getTree($string, $root)
	{
		$this->data	=	$string;
		$html	=	loadHtmlString($string);
		$this->dump(-1,$html, $root);
		
		return $this->tree;		
	}
	function dump($index_parent,$html, $root)
	{
		$href	=	new href();
		if ($index_parent==-1) {
			$list_li	=	$html->find('li[class="parent"]');	
		}else {
			$list_li	=	$html->find('li');	
		}
		for ($i=0; $i<count($list_li); $i++)
		{
			$ul_sub	=	$list_li[$i]->find('ul[class="sub"]',0);
			if (count($ul_sub)) {
				$obj	=	new stdClass();
				$obj->parent	=	$index_parent;
				$obj->title		=	trim(str_replace('\r\n','',$list_li[$i]->children[0]->children[0]->innertext));
				$obj->link		=	$href->process_url($list_li[$i]->children[0]->children[0]->href,$root);
				array_push($this->tree,$obj);
				$index			=	count($this->tree)-1;
				$this->dump($index,$ul_sub, $root);
			}else {
				$obj	=	new stdClass();
				$obj->parent	=	$index_parent;
				if ($index_parent==-1) {
					$obj->title		=	trim(str_replace('\r\n','',$list_li[$i]->children[0]->children[0]->innertext));
					$obj->link		=	$href->process_url($list_li[$i]->children[0]->children[0]->href,$root);
					
				}else {
					$obj->title		=	trim(str_replace('\r\n','',$list_li[$i]->children[0]->innertext));
					$obj->link		=	$href->process_url($list_li[$i]->children[0]->href,$root);
				}				
				array_push($this->tree,$obj);
			}
		}
	}
}


class buildTreeBAOMOI
{
	var $data	=	'';
	var $tree	=	null;
	function buildTreeBAOMOI($reg = null )
	{		
		$this->tree	=	array();		
	}
	function getTree($string, $root)
	{
		$this->data	=	$string;
		$html	=	loadHtmlString($string);
		$this->dump(-1,$html, $root);		
		unset($this->tree[0]);		
		return $this->tree;		
	}
	function dump($index_parent,$html, $root)
	{
		$href	=	new href();
		if ($index_parent==-1) {
			$list_li	=	$html->find('ul[class="sf-menu"]',0)->children;				
		}else {
			$list_li	=	$html->find('li');	
		}
		for ($i=0; $i<count($list_li); $i++)
		{
			$ul_sub	=	$list_li[$i]->find('ul',0);
			if (count($ul_sub)) {
				$obj	=	new stdClass();
				$obj->parent	=	$index_parent;
				$obj->title		=	trim(str_replace('\r\n','',$list_li[$i]->children[0]->children[0]->innertext));
				$obj->link		=	$href->process_url($list_li[$i]->children[0]->children[0]->href,$root);
				array_push($this->tree,$obj);
				$index			=	count($this->tree)-1;				
				$this->dump($index,$ul_sub, $root);
			}else {
				$obj	=	new stdClass();
				$obj->parent	=	$index_parent;
				
				if ($index_parent==-1) {
					$obj->title		=	trim(str_replace('\r\n','',$list_li[$i]->children[0]->children[0]->innertext));
					$obj->link		=	$href->process_url($list_li[$i]->children[0]->children[0]->href,$root);
					
				}else {
					$obj->title		=	trim(str_replace('\r\n','',$list_li[$i]->children[0]->innertext));
					$obj->link		=	$href->process_url($list_li[$i]->children[0]->href,$root);
				}				
				array_push($this->tree,$obj);				
			}
		}
	}	
}