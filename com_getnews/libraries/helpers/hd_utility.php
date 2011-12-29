<?php
/**
 * @package		yos_showcase
 * @subpackage	Components
 * @link		http://yopensource.com
 * @author		yopensource
 * @copyright 	yopensource (yopensource@gmail.com)
 * @license		Commercial
 */
// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

/**
 * YOS Utility class
 * Store many useful functions
 *
 */
class YOS_utility extends JObject 
{
	/**
	 * Mysql check connection
	 * if connection is idle (MySQL server has gone away) then create a new connection
	 *
	 */
	public function mysql_check_connection(){
		$db	 =& JFactory::getDBO();
		
		$is_connected = mysql_ping($db->get('_resource'));
		if(!$is_connected){
			$conf =& JFactory::getConfig();
			$host 		= $conf->getValue('config.host');
			$user 		= $conf->getValue('config.user');
			$password 	= $conf->getValue('config.password');
			$database	= $conf->getValue('config.db');
			$prefix 	= $conf->getValue('config.dbprefix');
			$driver 	= $conf->getValue('config.dbtype');
			$debug 		= $conf->getValue('config.debug');
	
			$options	= array ( 'driver' => $driver, 'host' => $host, 'user' => $user, 'password' => $password, 'database' => $database, 'prefix' => $prefix );
			
			$driver		= array_key_exists('driver', $options) 		? $options['driver']	: 'mysql';
			
			$driver = preg_replace('/[^A-Z0-9_\.-]/i', '', $driver);
			$path	= JPATH_LIBRARIES.DS.'joomla'.DS.'database'.DS.'database'.DS.$driver.'.php';

			if (file_exists($path)) {
				require_once($path);
			} else {
				$this->setError('[ARTICLE] Cannot load SQL driver: '.$path);
				return false;
			}

			$adapter	= 'JDatabase'.$driver;
			$instance	= new $adapter($options);
			if ( $error = $instance->getErrorMsg() )
			{
				$this->setError('[ARTICLE] Cannot create '.$adapter.' object: '.$error);
				return false;
			}
			
			$db = $instance;
		}
		
		return mysql_ping($db->get('_resource'));
	}
	
	/**
	 * Test keywords in a string
	 *
	 * @param string $str_in
	 * @param string $str_keywords separated by ,
	 * @return boolean
	 */
	public function test_keyword($str_in, $str_keywords){
		$str_in = strip_tags($str_in);
		
		$return = false;
		
		$arr_keywords = explode(',', $str_keywords);
		for ($i = 0; $i < count($arr_keywords); $i++){
			if (!trim($arr_keywords[$i])) {
				continue;
			}			
			if (preg_match('/'.preg_quote(trim($arr_keywords[$i])).'/i', $str_in)){
				$return = true;
				break;
			}
		}
		
		return $return;
	}
	
	function charset_process($str_in, $from_encoding = 'UTF-8'){
		$from_encoding = strtoupper($from_encoding);
		switch ($from_encoding){
			case 'UTF-8':
				$str_in = $this->utf8_entity_decode($str_in);
				break;
			case 'ISO-8859-1':
				$str_in = utf8_encode($str_in);
				break;
			default:
				if ($from_encoding) {
					$str_in = @mb_convert_encoding($str_in, 'UTF-8', $from_encoding);
				}
				break;
		}
		
		return $str_in;
	}
	
	function utf8_entity_decode($str_in){
		$str_in	= preg_replace_callback('/&#\d{2,5};/u', array(&$this,"_utf8_entity_decode"), $str_in );
		return $str_in;
	}
	
	function _utf8_entity_decode($entity)
	{
		$convmap = array(0x0, 0x10000, 0, 0xfffff);
		return mb_decode_numericentity($entity[0], $convmap, 'UTF-8');
	}
	
	function print_debug($level, $message){
		for ($i = 1; $i < $level; $i++){
			echo '&nbsp;&nbsp;&nbsp;';
		}
		echo date('H:i:s').$message."<br />\n";
	}
	
	/**
	 * Convert an array to string
	 *
	 * @param array $arr_input
	 * @param string $str_separate
	 * @return string
	 */
	function yos_array_to_string($arr_input, $str_separate){
		$str_output = "";
		for ($i = 0; $i < count($arr_input); $i++){
			if ($i == 0) {
				$str_output = $arr_input[$i];
			}else {
				$str_output .= $str_separate . $arr_input[$i];
			}
		}
		return $str_output;
	}
	
	/**
	 * Convert an string to an array
	 *
	 * @param string $str_input
	 * @param string $str_separate
	 * @return array
	 */
	function yos_string_to_array($str_input, $str_separate){
		$arr_output = split($str_separate, $str_input);
		return $arr_output;
	}
	
	/**
	 * Is sub array (sequential)
	 * A smarter detect, uses for getUpdateElement function
	 *
	 * @param array $newArray
	 * @param array $oldArray
	 * @return bool
	 */
	function isSubArray($newArray, $oldArray) {
		$d = -1;
		for ($i = 0; $i < count($newArray) && $i < count($oldArray); $i++){
			$ok = false;
			for ($j = 0; $j < count($oldArray); $j++){
				if(($newArray[$i] == $oldArray[$j]) && ($j > $d)){
					$d = $j;
					$ok = true;
					break;
				}
			}
			if (!$ok) {
				return false;
			}
		}
		
		return true;
	}
	
	/**
	 * Get Update Elements
	 * Get updated elements by compare 2 arrays
	 *
	 * @param array $newArray
	 * @param array $oldArray
	 * @return array
	 */
	function getUpdateElement($newArray, $oldArray){
		$k = 0;
		$allNew = true;
		for ($i = 0; $i < count($newArray); $i++){
			$subArray = array_slice($newArray, $i, count($newArray) - $i);
			if (YOS_utility::isSubArray($subArray, $oldArray)) {
				$k = $i;
				$allNew = false;
				break;
			}
		}
		
		$returnArray = array_slice($newArray, 0, $k);
		
		if ($allNew) {
			return $newArray;
		}
		
		return $returnArray;
	}
	
	/**
	 * split an array to many array
	 * each array length less than $size
	 *
	 * @param array $arr_input
	 * @param int $size
	 * @return array
	 */
	function splitArray($arr_input, $size){
		$arr_output = array();
		
		$i = 0;
		do {
			$arr_temp = array_slice($arr_input, $i, $size);
			array_push($arr_output, $arr_temp);
			$i += $size;
		} while ($i < count($arr_input));
		
		return $arr_output;
	}
	
	/**
	 * Get a sub words from a string
	 *
	 * @param string $str_in string input
	 * @param int $numberWord Number of words
	 * @param int $start point to start get words
	 * @return string
	 */
	function wordSub($str_in, $numberWord, $start = 0) {
		$arr_text = preg_split('/\s/',trim($str_in));
		$arr_new = array_slice($arr_text, $start, $numberWord);
		$text = implode(' ', $arr_new);
		return $text;
	}
	
	
	/**
	 * Make a string HTML input safe
	 *
	 * @param String HTML $data
	 * @return String HTML
	 */
	function makesafehtml($data){
		$subtext	=	$data;
		$subtext	=	preg_replace('/<table.*?>.*?<\/table>/si','', $subtext );
		$subtext	=	preg_replace('/<select.*?>.*?<\/select>/si','',$subtext);
		$subtext	=	preg_replace('/<script.*?>.*?<\/script>/si','', $subtext);
		$subtext	=	preg_replace('/<\!--.*?-->/si','', $subtext);
		$subtext	=	preg_replace('/<br>/si','<br />', $subtext);
		
		if(preg_match_all('/<(img|hr|br|input)(.*?)>/si',$subtext, $matches)){
			for ($i = 0; $i < count($matches[2]); $i++){
				$strIn = $matches[2][$i];
				if ((strlen($strIn) == 0) || ($strIn[strlen($strIn) - 1] != '/')) {
					$newStrIn = $strIn . '/';
					$subtext = str_replace('<'.$matches[1][$i].$strIn.'>', '<'.$matches[1][$i].$newStrIn.'>', $subtext);
				}
			}
		}
		
		preg_match_all('/<(?!\/).*?>/', $subtext, $matches);
		$arr_open = $matches[0];
		preg_match_all('/<\/.*?>/', $subtext, $matches);
		$arr_close = $matches[0];
		for ($i =0; $i<count($arr_open); $i++){
			if (preg_match('/<.*?\/>/i', $arr_open[$i])) {
				$arr_open = $this->delAnArrayElement($i, $arr_open);
				$i--;
			}
		}		
		
		$carr_open	=	$arr_open;
		$carr_close	=	$arr_close;
		
		for ($i	=	0; $i< count($arr_open); $i++){			
			for ($j = 0; $j<count($arr_close); $j++){
				preg_match('/<(\S+)?\s*.*?>/i', $arr_open[$i], $match);
				preg_match('/<\/(\S+)?>/', $arr_close[$j], $match1);
				if ($match[1]==$match1[1]) {
					$arr_open	=	$this->delAnArrayElement($i, $arr_open);
					$arr_close	=	$this->delAnArrayElement($j, $arr_close);
					$i--;
					break;
				}
			}			
		}
		
	
		for ($i	=	count($carr_close)-1; $i>=0; $i--){			
			for ($j = count($carr_open)-1; $j>=0; $j--){
				preg_match('/<(\S+)?\s*.*?>/i', $carr_open[$j], $match);
				preg_match('/<\/(\S+)?>/', $carr_close[$i], $match1);
				if ($match[1]==$match1[1]) {					
					$carr_open	=	$this->delAnArrayElement($j, $carr_open);
					$carr_close	=	$this->delAnArrayElement($i, $carr_close);					
					break;
				}
			}			
		}		
		
		
		$tagclose	=	'';
		$tagopen	=	'';
		
		for ($i = 0 ; $i<count($arr_open); $i++){
			preg_match('/<(\S+)?\s*.*?>/i', $arr_open[$i], $match);
			$tagclose	=	'</'.$match[1].'>'.$tagclose;
		}
		
		for ($i = 0 ; $i<count($carr_close); $i++){
			preg_match('/<\/(.+?)>/i', $carr_close[$i], $match);
			$tagopen	=	'<'.$match[1].'>'. $tagopen;
		}
		
		$data	.=	$tagclose;
		$data	=	$tagopen.$data;
		return $data;
		
	}
	
	function delAnArrayElement($i,$array){
		$newarray	=	array();
		for ($j=0; $j<count($array); $j++){
			if ($j!=$i) {
				array_push($newarray, $array[$j]);
			}
		}
		return $newarray;
	}
	
	function getVersion(){
		$xml = & JFactory::getXMLParser('Simple');
		if ($xml->loadFile(JPATH_COMPONENT_ADMINISTRATOR.DS.'helpers'.DS.'version.xml'))
		{
			if (!$version = & $xml->document->version) {
				return false;
			}
			if (!$url = & $xml->document->url) {
				return false;
			}
			if (!$productcode = & $xml->document->productcode) {
				return false;
			}
		} else {
			return false;
		}
		
		return array('version' => $version[0]->data(), 'url' => $url[0]->data(), 'productcode'=> $productcode[0]->data() );
		
	}
}
