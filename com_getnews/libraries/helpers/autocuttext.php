<?php
/**
 * @version		$Id: autocuttext.php 189 2008-09-15 10:58:00 yopensource@gmail.com $
 * @package		vb
 * @subpackage	Auto Cut Text
 * @author 		ducdm87 
 * @license		GNU/GPL, see LICENSE.php 
 */

class AutoCutText {
	/**
	 * String data input
	 *
	 * @var string HTML
	 */
	var $_data 			= null;
	
	/**
	 * Number introtext will be cut
	 *
	 * @var int
	 */
	var $_intronumber	=	0;
	
	/**
	 * String of introtext
	 *
	 * @var String HTML
	 */
	var $_subtext		=	null;
	
	/**
	 * String of fulltext
	 *
	 * @var String HTML
	 */
	var $_remaintext	=	null;
	
	var $_stack = array();
	var $_stack_intro = array();
	var $_stack_full = array();
	
	var $_arr_codes = array();
		
	/**
	 * Constructor
	 *
	 * @param String $data
	 * @param Int $number
	 */
	function __construct($data, $number){
		if (class_exists('tidy')) {
			//use tidy to improve html code:
			$config = array(
				'indent' => TRUE,
				'output-xhtml' => TRUE,
				'show-body-only' => TRUE,
				'clean' => TRUE,
				'wrap' => 0
				);
			$tidy = new tidy();
			$data = $tidy->repairString($data, $config, 'utf8');	
		}

		$this->_data		=	$data;
		
		//encode
		$this->_data		= $this->_encode($this->_data);

		$this->_intronumber	=	$number;
		
		$this->_to_stack();
		$this->_separate();

		
		$this->_stack_intro = $this->_fill_missing_items($this->_stack_intro);
		
		$this->_stack_full = $this->_fill_missing_items($this->_stack_full);

		$this->_subtext = $this->_to_string($this->_stack_intro);
		$this->_remaintext = $this->_to_string($this->_stack_full);
		
		//decode
		$this->_subtext = $this->_decode($this->_subtext);
		
		$this->_remaintext = $this->_decode($this->_remaintext);
		
		if (class_exists('tidy')) {
			//use tidy to improve html code:
			$config = array(
				'indent' => TRUE,
				'output-xhtml' => TRUE,
				'show-body-only' => TRUE,
				'clean' => TRUE,
				'wrap' => 0
				);
			$tidy = new tidy();
			$this->_subtext = $tidy->repairString($this->_subtext, $config, 'utf8');	
			$this->_remaintext = $tidy->repairString($this->_remaintext, $config, 'utf8');	
		}
	}
	
	/**
	 * Decode html string, restore all script and style tags
	 *
	 * @param string $str_in
	 * @return string
	 */
	private function _decode($str_in){
		for ($i = 0; $i < count($this->_arr_codes); $i++){
			$str_in = str_replace('DUC_CODE_'.$i.'_CODE_DUC', $this->_arr_codes[$i], $str_in);
		}
		
		return $str_in;
	}
	
	/**
	 * Encode html string, encode all script and style tags
	 *
	 * @param string $str_in
	 * @return string
	 */
	private function _encode($str_in){
		$next = 0;
		if(preg_match_all('/<script[^>]*>.*?<\/script>/ism', $str_in, $matches)){
			for ($i = 0; $i < count($matches[0]); $i++){
				$str_in = str_replace($matches[0][$i], 'DUC_CODE_'.$next.'_CODE_DUC', $str_in);
				array_push($this->_arr_codes, $matches[0][$i]);
				$next++;
			}
		}
		if(preg_match_all('/<style[^>]*>.*?<\/style>/ism', $str_in, $matches)){
			for ($i = 0; $i < count($matches[0]); $i++){
				$str_in = str_replace($matches[0][$i], 'DUC_CODE_'.$next.'_CODE_DUC', $str_in);
				array_push($this->_arr_codes, $matches[0][$i]);
				$next++; 
			}
		}
		
		return $str_in;
	}
	
	/**
	 * Convert stack to html string
	 *
	 * @param array $stack_in
	 */
	private function _to_string($stack_in){
		$str_out = '';
		foreach ($stack_in as $item){
			$str_out .= $item->data."\n";
		}
		
		return $str_out;
	}
	
	/**
	 * fill missing items
	 *
	 * @param array $stack_in
	 * @return array
	 */
	private function _fill_missing_items($stack_in){
		$stack_out = $stack_in;
		
		$arr_open = array();
		$arr_close = array();
		
		foreach ($stack_in as $item){
			if ($item->type === 1) {//open tag
				
				//push to arr_open stack
				array_push($arr_open, $item);
								
				continue;
			}
			if ($item->type === -1) {//close tag
				
				//remove from open tags
				$last_open = isset($arr_open[count($arr_open) - 1]) ? $arr_open[count($arr_open) - 1] : false;
				if ($last_open && strtolower($last_open->name) === strtolower($item->name)) {
					array_pop($arr_open);
				}
				else{
					//push to arr_close stack
					array_push($arr_close, $item);
				}
				continue;
			}
			
		}
		
//		echo 'open array:'; var_dump($arr_open);echo "<hr />\n";
//		echo 'close array:'; var_dump($arr_close);echo "<hr />\n";die();
		
		//process open without closed tags
		if (count($arr_open)) {
			//reverse array open (important)
			$arr_open = array_reverse($arr_open);
			foreach ($arr_open as $open_item){
				//add close item to.
				$obj_close_tag = new stdClass();
				$obj_close_tag->name = $open_item->name;
				$obj_close_tag->data = '</'.$open_item->name.'>';
				$obj_close_tag->type = -1;
				array_push($stack_out, $obj_close_tag);
			}
		}
		
		//process closed without open tags
		if (count($arr_close)) {
			$reverse_stack = array_reverse($stack_out);
			foreach ($arr_close as $close_item){
				$obj_open_tag = new stdClass();
				$obj_open_tag->name = $close_item->name;
				$obj_open_tag->data = '<'.$close_item->name.'>';
				$obj_open_tag->type = 1;
				array_push($reverse_stack, $obj_open_tag);
			}
			$stack_out = array_reverse($reverse_stack);
		}

		return $stack_out;
	}
	
	/**
	 * Separate stack to 2 stack: intro and full
	 *
	 */
	private function _separate(){
		$word_counter = 0;
		
		$arr_break = array('br', 'hr', 'p', 'div', 'table');
		
		$is_intro = true;
		$index	=	0;
		for ($i = 0; $i < count($this->_stack); $i++){
			$stack_item = $this->_stack[$i];
			
			if ($is_intro === true) {
				array_push($this->_stack_intro, $stack_item);
				
				//count word
				if ($stack_item->type === 2) {
					$word_counter += $this->_word_count($stack_item->data);				
				}
				if ($word_counter > $this->_intronumber) {						
					//check if the next stack is a P, BR, HR, DIV, TABLE						
					$next_stack_item = $this->_stack[$i + 1];
					
					if (in_array(strtolower($next_stack_item->name), $arr_break)) {
						$is_intro = false;
					}
				}
			}
			else {
				array_push($this->_stack_full, $stack_item);
			}
		}
	}
	
	/**
	 * Count words in a string
	 *
	 * @param string $str_in
	 * @return int number of words
	 */
	private function _word_count($str_in){
		$str_in = strip_tags($str_in);
		$arr_word = preg_split('/\s+/', $str_in);
		return count($arr_word);
	}
	
	/**
	 * put data to a stack
	 *
	 */
	private function _to_stack(){
		$tmp_data = trim($this->_data);
		
		$continue = 1;
		while ($continue) {
			//find the first tag (open and close)
			if(preg_match('/^(\s*<[^>]+>)(.*)$/su', $tmp_data, $match)){
				$obj_tag = $this->_get_tag_properties(trim($match[1]));
				if ($obj_tag !== false) {
					array_push($this->_stack, $obj_tag);
				}
								
				//new data
				$tmp_data = $match[2];
			}
			elseif (preg_match('/^([^<]+)(($|<).*$)/smu', $tmp_data, $match)){
				//get text
				$obj_stack_item = new stdClass();
				//text
				$obj_stack_item->type = 2;
				$obj_stack_item->name = '';
				$obj_stack_item->data = trim($match[1]);
				array_push($this->_stack, $obj_stack_item);
				
				//new data
				$tmp_data = $match[2];
			}
			else {
				$continue = 0;
			}
		}		
	}
	
	private function _get_tag_properties($str_tag){
		$str_tag = trim($str_tag);
		
		$obj_tag = new stdClass();
		$obj_tag->data = $str_tag;
		
		//tag name
		if (preg_match('/<(\/|)([\-a-zA-z0-9]+)/sm', $str_tag, $match)) {
			$obj_tag->name = strtolower($match[2]);
		}
		else {
			return false;
		}
		
		//tag type
		$arr_single_tags = array('img', 'br', 'hr');
		if (in_array($obj_tag->name, $arr_single_tags)) {
			$obj_tag->type = 0;
		}
		else {
			if (preg_match('/<\//sm', $str_tag)) {
				//close tag
				$obj_tag->type = -1;
			}elseif (preg_match('/<[a-zA-z0-9]+.*?\/>/sm', $str_tag)) {
				//single tag (open and close
				$obj_tag->type = 0;
			}else {
				//open tag
				$obj_tag->type = 1;
			}
		}
		return $obj_tag;
	}
	
	
	function getIntro(){
		return $this->_subtext;
	}
	
	function getFulltext(){
		return $this->_remaintext;
	}
}