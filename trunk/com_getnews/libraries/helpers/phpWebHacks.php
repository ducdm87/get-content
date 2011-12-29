<?php
/**
 * phpWebHacks.php 1.5
 * This class is a powerful tool for HTTP scripting with PHP.
 * It simulates a web browser, only that you use it with lines of code
 * rather than mouse and keyboard.
 *
 * See the documentation at http://php-http.com/documentation
 * See the examples at http://php-http.com/examples
 *
 * Author  Nashruddin Amin - me@nashruddin.com
 * License GPL
 * Website http://php-http.com
 * 
 * Approved by Nguyen Anh Minh - minhna@gmail.com
 */

/* minhna hacked add directory separator define */
defined('DS') or define('DS', strtoupper(substr(PHP_OS, 0, 3) == 'WIN') ? '\\' : '/');

class phpWebHacks 
{ 
	private $_user_agent = 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.9) Gecko/2008052906 Firefox/3.0';
	private $_boundary 	 = '----PhPWebhACKs-RoCKs--';
	private $_useproxy 	 = false;
	private $_proxy_host = '';
	private $_proxy_port = '';
	private $_proxy_user = '';
	private $_proxy_pass = '';
	private $_usegzip 	 = false;
	private $_log 		 = false;
	private $_debugdir   = '.log';
	private $_debugnum   = 1;
	private $_delay 	 = 1;
	private $_body 		 = array();
	private $_cookies 	 = array();
	private $_addressbar = '';
	private $_multipart  = false;
	private $_timestart  = 0;
	private $_bytes 	 = 0;
	/**
	 * DUCDM hack this to search special character to remove
	 *
	 * @var array
	 */	
	private $_search_special	=	array();
	/**
	 * DUCDM hack this to replace
	 *
	 * @var unknown_type
	 */
	private $_replace_special	=	array();
	
	/* minhna hacked to added error handle */
	private $_errors	= '';
	
	/* minhna hacked to store header info */
	private $_head	= array();
	
	/* minhna hacked to store page charset info */
	private $_charset	= false;

	/**
	 * Constructor
	 */
	public function __construct($debugdir = '',$search_special = array(), $replace_special = array())
	{
		/* check if zlib is available */
		if (function_exists('gzopen')) {
			//$this->_usegzip = true;
		}

		/* start time */
		$this->_timestart = microtime(true);
		
		/* minhna hacked to force random boundary */
		$this->_boundary = '----'.rand(1000000, 9000000).'--';
		
		/* minhna hacked to init log dir */
		if ($debugdir) {
			$this->_debugdir = $debugdir;
		}
		else {
			$this->_debugdir = dirname(__FILE__).DS.'.log';
		}
		$this->setDebug(true);
		$this->_search_special	=	$search_special;
		$this->_replace_special	=	$replace_special;
	}

	/**
	 * Destructor
	 */
	public function __destruct()
	{
		/* remove temporary file for gzip encoding */
		if (file_exists('tmp.gz')) {
			unlink('tmp.gz');
		}

		/* get elapsed time and transferred bytes */
		$time  = sprintf("%02.1f", microtime(true) - $this->_timestart);
		$bytes = sprintf("%d", ceil($this->_bytes / 1024));

		/* log */
		if ($this->_log) {
			$fp = fopen("$this->_debugdir/headers.txt", 'a');
			fputs($fp, "------ Transferred " . $bytes . "kb in $time sec ------\r\n");
			fclose($fp);
		}
	} 

	/** 
	 * HEAD 
	 */
	public function head($url)
	{ 
		return $this->fetch($url, 'HEAD');
	} 

	/**
	 * GET
	 */
	public function get($url)
	{ 
		return $this->fetch($url, 'GET', 5);
	} 

	/**
	 * POST
	 */
	public function post($url, $form = array(), $files = array())
	{ 
		return $this->fetch($url, 'POST', 10, $form, $files);
	} 

	/**
	 * Make HTTP request
	 */
	protected function fetch($url, $method, $maxredir = 2, $form = array(), $files = array())
	{
		// DUCDM HACK THIS
		$url	=	str_replace($this->_search_special,$this->_replace_special,$url);
	
		/* convert to absolute if relative URL */
		$url = $this->getAbsUrl($url, $this->_addressbar);

		/* only http or https */
		//if (substr($url, 0, 4) != 'http') return '';
		/* minhna hacked to enable error handle */
		if (substr($url, 0, 4) != 'http') {
			$this->_errors .= '#1: only http and https are allowed';
			return false;
		}

		/* cache URL */
		$this->_addressbar = $url;

		/* build request */
		$reqbody = $this->getReqBody($form, $files);	
		$reqhead = $this->getReqHead($url, $method, strlen($reqbody), empty($files) ? false : true);

		/* log request */
		if ($this->_log) {
			$this->logHttpStream($url, $reqhead, $reqbody);
		}

		/* parse URL and convert to local variables:
		   $scheme, $host, $path */
		$parts = parse_url($url);
		if (!$parts) { 
			//die("Invalid URL!\n");
			/* minhna hacked to enable error handle */
			$this->_errors .= '#2: Invalid URL!';
			return false;
		} else { 
			foreach($parts as $key=>$val) $$key = $val;
		} 
		// DUCDM Hacked to set timeout
		$timeout	=	0;
		if (isset($this->timeout)) {
			$timeout	=	$this->timeout;			
		}else {
			$timeout = ini_get("default_socket_timeout");
		}		
		/* open connection */
		if ($this->_useproxy) {
			$fp = @fsockopen($this->_proxy_host, $this->_proxy_port,$errNo,$errStr,$timeout);
		} else  {
			$fp = @fsockopen(($scheme=='https' ? "ssl://$host" : $host), $scheme == 'https' ? 443 : 80,$errNo,$errStr,$timeout);
		}

		/* always check */
		if (!$fp) {
			/* minhna hacked to enable error handle */
			$this->_errors .= '#3: Cannot connect to ' . $host . '!';
			return false;
			//die("Cannot connect to $host!\n");
		}

		/* send request & read response */
		@fputs($fp, $reqhead.$reqbody);
		for($res=''; !feof($fp); $res.=@fgets($fp, 4096)) {} 
		fclose($fp);

		/* set delay between requests. behave! */
		sleep($this->_delay);

		/* transferred bytes */
		$this->_bytes += (strlen($reqhead)+ strlen($reqbody)+ strlen($res));

		/* get response header & body */
		list($reshead, $resbody) = explode("\r\n\r\n", $res, 2);

		/* convert header to associative array */
		$head = $this->parseHead($reshead);
		
		/* cookies */
		if (isset($head['Set-Cookie']) && !empty($head['Set-Cookie'])) {
			$this->saveCookies($head['Set-Cookie'], $url);
		}
		
		if (isset($_REQUEST['show_debug']) and $_REQUEST['debug']==1) {					
			dump_data($head);			
		}
		
		/* minhna hacked to store head info */
		$this->_head = $head;
		
		/* redirects: 301 */
		if (isset($head['Location']) && $maxredir > 0) {
			$method = $method == 'HEAD' ? 'HEAD' : 'GET';						
			$maxredir--;
			return $this->fetch($this->getAbsUrl($head['Location'], $url), $method, $maxredir);
		}

		/* return immediately if HEAD */
		if ($method == 'HEAD') { 
			if ($this->_log) $this->logHttpStream($url, $reshead, null);
			return $head;
		} 
					
		/* referer */
		if (isset($head['Status']['Code']) && $head['Status']['Code'] == 200) {
			$this->_referer = $url;
		}
			
		/* transfer-encoding: chunked */
		if (isset($head['Transfer-Encoding']) && $head['Transfer-Encoding'] == 'chunked') {
			$body = $this->joinChunks($resbody);
		} else {
			$body = $resbody;
		} 	

		/* content-encoding: gzip */
		if (isset($head['Content-Encoding']) && $head['Content-Encoding'] == 'gzip') {
			//minhna hacked to read gzip data.
			$new_body = $this->_read_gzip_data($body);
			if ($new_body !== false) {
				$body = $new_body;
			}
			
		} 
		/* log response */
		if ($this->_log) {
			$this->logHttpStream($url, $reshead, $body);
		}

		/* cache body */
		array_unshift($this->_body, $body);

		/* parse meta tags */
		$meta = $this->parseMetaTags($body);

		/* redirects: <meta http-equiv=refresh...> */
		if (isset($meta['http-equiv']['refresh']) && $maxredir > 0) {
			//ducdm hack
			$list	=	explode(';', $meta['http-equiv']['refresh'], 2);
			if (isset($list[0])) {
				$delay	=	$list[0];
			}
			if (isset($list[1])) {
				$loc	=	$list[1];
			}			
			//minhna hack to fetch contents from some site auto refresh after some minutes.
			//only accept redirect within 10 seconds.
			if (intval($delay) < 10) {
				$loc = substr(trim($loc), 4);
				if (!empty($loc) && $loc != $url){
					return $this->fetch($this->getAbsUrl($loc, $url), 'GET', $maxredir--);
				}
			}			
		}

		/* minhna hacked to store encoding */
		if (isset($meta['http-equiv']['content-type'])) {
			//get charset
			if (preg_match('/charset=(.+?)$/i', $meta['http-equiv']['content-type'], $match)) {
				$this->_charset = $match[1];
			}
		}

		/* get body and clear cache */
		$body = $this->_body[0];
		for($i = 1; $i < count($this->_body); $i++) {
			unset($this->_body[$i]);
		}

		return $body;
	} 

	/**
	 * Build request header
	 */
	protected function getReqHead($url, $method, $bodylen = 0, $sendfile = true)
	{
		/* parse URL elements to local variables:
		   $scheme, $host, $path, $query, $user, $pass */
		$parts = parse_url($url);
		foreach($parts as $key=>$val) $$key = $val;

		/* setup path */
		$path = empty($path)  ? '/' : $path 
			  .(empty($query) ? ''  : "?$query");
			
		/* request header */	
		if ($this->_useproxy) {
			$head = "$method $url HTTP/1.1\r\nHost: $this->_proxy_host\r\n";
		} else  {
			$head = "$method $path HTTP/1.1\r\nHost: $host\r\n";
		}
		
		/*accept*/
		$head .= "Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8\r\n";
		$head .= "Accept-Language: en-us,en;q=0.5\r\n";
		$head .= "Accept-Charset: ISO-8859-1,utf-8;q=0.7,*;q=0.7\r\n";
		$head .= "Keep-Alive: 300\r\n";

		/* cookies */
		$head .= $this->getCookies($url);
		/* content-type */
		if ($method == 'POST' && ($sendfile || $this->_multipart)) {
			$head .= "Content-Type: multipart/form-data; boundary=$this->_boundary\r\n";
		} elseif ($method == 'POST') {
			$head .= "Content-Type: application/x-www-form-urlencoded\r\n";
		}

		/* set the content length if POST */
		if ($method == 'POST') {
			$head .= "Content-Length: $bodylen\r\n";
		}

		/* basic authentication */
		if (!$this->_useproxy && !empty($user) && !empty($pass)) {
			$head .= "Authorization: Basic ". base64_encode("$user:$pass")."\r\n";
		}

		/* basic authentication for proxy */
		if ($this->_useproxy && !empty($this->_proxy_user) && !empty($this->_proxy_pass)) {
			$head .= "Authorization: Basic ". base64_encode("$this->_proxy_user:$this->_proxy_pass")."\r\n";
		}

		/* gzip */
		if ($this->_usegzip) {
			$head .= "Accept-Encoding: gzip,deflate\r\n";
		}

		/* make it like real browsers */
		if (!empty($this->_user_agent)) {
			$head .= "User-Agent: $this->_user_agent\r\n";
		}
		if (!empty($this->_referer)) {
			$head .= "Referer: $this->_referer\r\n";
		}

		/* no pipelining yet */
		$head .= "Connection: Close\r\n\r\n";

		/* request header is ready */
		return $head;
	} 

	/**
	 * Build request body
	 */
	protected function getReqBody($form = array(), $files = array())
	{ 
		/* check for parameters */
		if (empty($form) && empty($files)) 
			return '';

		$body = '';
		$tmp  = array();

		/* only form available: x-www-urlencoded */
		if (!empty($form) &&  empty($files) && !$this->_multipart) { 
			foreach($form as $key=>$val)
				//name[] = ...
				if (is_array($val)) {
					foreach ($val as $sub_val){
						$tmp[] = urlencode($key) . '=' . urlencode($sub_val);
					}
				}
				else{
					$tmp[] = $key .'='. urlencode($val);
				}
			return implode('&', $tmp);
		} 

		/* form */
		foreach($form as $key=>$val) {
			$body .= "--$this->_boundary\r\nContent-Disposition: form-data; name=\"" . $key ."\"\r\n\r\n" . $val ."\r\n";
		}

		/* files */
		foreach($files as $key=>$val) { 
			if (!file_exists($val)) continue;
			$body .= "--$this->_boundary\r\n"
				   . "Content-Disposition: form-data; name=\"" . $key . "\"; filename=\"" . basename($val) . "\"\r\n"
				   . "Content-Type: " . $this->getMimeType($val) . "\r\n\r\n"
				   . file_get_contents($val) . "\r\n";
		} 

		/* request body is ready! */
		return $body."--$this->_boundary--";
	} 

	/**
	 * convert response header to associative array
	 */
	protected function parseHead($str)
	{
//var_dump($str);echo '<br />';
		$lines = explode("\r\n", $str);

		list($ver, $code, $msg) = explode(' ', array_shift($lines), 3);
		$stat = array('Version' => $ver, 'Code' => $code, 'Message' => $msg);

		$head = array('Status' => $stat);
		foreach($lines as $line) { 
			list($key, $val) = explode(':', $line, 2);
			if ($key == 'Set-Cookie') {
				$head['Set-Cookie'][] = trim($val);
			} else {
				$head[$key] = trim($val);
			}
		} 

		return $head;
	} 

	/**
	 * Read chunked pages
	 */
	protected function joinChunks($str)
	{
		$CRLF = "\r\n";
		for($tmp = $str, $res = ''; !empty($tmp); $tmp = trim($tmp)) { 
			if (($pos = strpos($tmp, $CRLF)) === false) return $str;
			$len = hexdec(substr($tmp, 0, $pos));
			$res.= substr($tmp, $pos + strlen($CRLF), $len);
			$tmp = substr($tmp, $pos + strlen($CRLF) + $len);
		} 
		return $res;
	} 

	/**
	 * Save cookies from server
	 */
	protected function saveCookies($set_cookies, $url) 
	{ 
		foreach($set_cookies as $str) 
		{
			$parts = explode(';', $str);

			/* extract cookie parts to local variables:
			   $name, $value, $domain, $path, $expires, $secure, $httponly */
			foreach($parts as $part) { 
				@list($key, $val) = explode('=', trim($part), 2);

				$k = strtolower($key);

				if ($k == 'secure' || $k == 'httponly') {
					$$k = true;
				} elseif ($k == 'domain' || $k == 'path' || $k == 'expires') {
					$$k = $val;
				} else {
					$name  = $key;
					$value = $val;
				}
			} 

			/* cookie's domain */
			if (empty($domain)) {
				$domain = parse_url($url, PHP_URL_HOST);
			}

			/* cookie's path */	
			if (empty($path)) {
				$path = parse_url($url, PHP_URL_PATH);
				$path = preg_replace('#/[^/]*$#', '', $path);
				$path = empty($path) ? '/' : $path;
			} 

			/* cookie's expire time */
			if (!empty($expires)) {
				$expires = strtotime($expires);
			}
			else {
				//tomorrow expeires
				$expires = time() + 60*60*24;
			}
				
			/* setup cookie ID, a simple trick to add/update existing cookie
			   and cleanup local variables later */
			$id = md5("$domain;$path;$name");

			/* add/update cookie */
			$this->_cookies[$id] = array(
				'domain'   => substr_count($domain, '.') == 1 ? ".$domain" : $domain, 
				'path'     => $path, 
				'expires'  => $expires, 
				'name'     => $name, 
				'value'    => $value, 
				'secure'   => isset($secure) ? $secure : false, //minhna hacked to remove notice
				'httponly' => isset($httponly) ? $httponly : false //minhna hacked to remove notice
			);

			/* cleanup local variables */
			foreach($this->_cookies[$id] as $key=>$val) unset($$key);
		} 

		return true;
	} 

	/**
	 * Get cookies for URL
	 */
	protected function getCookies($url)
	{
		$tmp = array();
		$res = array();
		/* remove expired cookies first */
		foreach($this->_cookies as $id=>$cookie) {
			if (empty($cookie['expires']) || $cookie['expires'] >= time()) {
				$tmp[$id] = $cookie;
			}
		}

		/* cookies ready */
		$this->_cookies = $tmp;
		/* parse URL to local variables:
		   $scheme, $host, $path, $query */
		$parts = parse_url($url);
		foreach($parts as $key=>$val) $$key = $val;

		if (empty($path)) $path = '/';
		
		/* get all cookies for this domain and path */
		foreach($this->_cookies as $cookie) {
			/* minhna hacked to fix get cookie issue */
			$d = strlen($host) >= strlen($cookie['domain']) ? substr($host, -1 * strlen($cookie['domain'])) : $host;
			$p = substr($path, 0, strlen($cookie['path']));
			
			if (($d == $cookie['domain'] || ".$d" == $cookie['domain']) && $p == $cookie['path']) { 
				if ($cookie['secure'] == true  && $scheme == 'http') {
					continue;
				}
				$res[] = $cookie['name'].'='.$cookie['value'];
			}
		} 
		/* return the string for HTTP header */
		return (empty($res) ? '' : 'Cookie: '.implode('; ', $res)."\r\n");
	} 

	/**
	 * Convert relative URL to absolute URL
	 */
	protected function getAbsUrl($loc, $parent)
	{ 
		/* parameters is required */
		if (empty($loc) && empty($parent)) return;

		$loc = str_replace('&amp;', '&', $loc);

		/* return if URL is abolute */
		if (parse_url($loc, PHP_URL_SCHEME) != '') return $loc;

		/* handle anchors and query's part */
		$c = substr($loc, 0, 1);
		if ($c == '#' || $c == '&') return "$parent$loc";

		/* handle query string */
		if ($c == '?') {
			$pos = strpos($parent, '?');
			if ($pos !== false) $parent = substr($parent, 0, $pos);
			return "$parent$loc";
		}

		/* parse URL and convert to local variables:
		   $scheme, $host, $path */
		$parts = parse_url($parent);
		foreach ($parts as $key=>$val) $$key = $val;

		/* remove non-directory part from path */
		$path = preg_replace('#/[^/]*$#', '', $path);

		/* set path to '/' if empty */
		$path = preg_match('#^/#', $loc) ? '/' : $path;

		/* dirty absolute URL */
		$abs = "$host$path/$loc";

		/* replace '//', '/./', '/foo/../' with '/' */
		while($abs = preg_replace(array('#(/\.?/)#', '#/(?!\.\.)[^/]+/\.\./#'), '/', $abs, -1, $count)) 
			if (!$count) break;

		/* absolute URL */
		return "$scheme://$abs";
	} 

	/**
	 * Convert meta tags to associative array
	 */
	protected function parseMetaTags($html) 
	{ 
		/* extract to </head> */
		if (($pos = strpos(strtolower($html), '</head>')) === false) { 
			return array();
		} else {
			$head = substr($html, 0, $pos);
		} 

		/* get page's title */
		preg_match("/<title>(.+)<\/title>/siU", $head, $m);
		if (!empty($m[1])) {
			$meta = array('title' => $m[1]);
		}

		/* get all <meta...> */
		preg_match_all('/<meta\s+[^>]*name\s*=\s*[\'"][^>]+>/siU', $head, $m);
		foreach($m[0] as $row) { 
			preg_match('/name\s*=\s*[\'"](.+)[\'"]/siU', $row, $key);
			preg_match('/content\s*=\s *[\'"](.+)[\'"]/siU', $row, $val);
			if (!empty($key[1]) && !empty($val[1]))
				$meta[$key[1]] = $val[1];
		} 

		/* get <meta http-equiv=refresh...> (minhna hacked to store all http-enquiv) */
		if(preg_match_all('/<meta[^>]+http-equiv\s*=\s*[\'"]?([a-zA-Z0-9 -]+?)[\'"]?[^>]+content\s*=\s*[\'"](.+)[\'"][^>]*>/siU', $head, $matches)){
			for ($i = 0; $i < count($matches[0]); $i++){
				$equiv_key = strtolower($matches[1][$i]);
				$equiv_val = preg_replace('/&#0?39;/', '', $matches[2][$i]);
				$meta['http-equiv'][$equiv_key] = $equiv_val;
			}
		}
				
		return $meta;
	} 

	/**
	 * Convert form to associative array
	 */
	public function parseForm($name_or_id, &$action = '', $str = '')
	{ 
		if (empty($str) && empty($this->_body[0])) 
			return array();
			
		$body = empty($str) ? $this->_body[0] : $str;

		/* extract the form */
		if ($name_or_id) {
			$re = '(<form[^>]+(id|name)\s*=\s*(?(?=[\'"])[\'"]'.$name_or_id.'[\'"]|\b'.$name_or_id.'\b)[^>]*>.+<\/form>)';
		}
		else {
			/* minhna hacked to find forms without name or id by using action */
			$re = '(<form[^>]+(action)\s*=\s*[\'"]'.$action.'[^>]*>.+<\/form>)';
		}
		
		if (!preg_match("/$re/siU", $body, $form)) 
			return array();

		/* check if enctype=multipart/form-data */
		if (preg_match('/<form[^>]+enctype[^>]+multipart\/form-data[^>]*>/siU', $form[1], $a))
			$this->_multipart = true;
		else 
			$this->_multipart = false;
			
		/* get form's action */
		preg_match('/<form[^>]+action\s*=\s*(?(?=[\'"])[\'"]([^\'"]+)[\'"]|([^>\s]+))[^>]*>/si', $form[1], $a);
		$action = empty($a[1]) ? html_entity_decode($a[2]) : html_entity_decode($a[1]);

		/* select all <select..> with default values */
		preg_match_all('/<select ([^>]+)\/?>(.+?)<\/select>/si', $form[1], $a);
				
		for ($k = 0; $k < count($a[1]); $k++){
			if (!preg_match('/name\s*=\s*(?(?=[\'"])[\'"]([^"]+)[\'"]|\b(.+)\b)/siU', $a[1][$k], $match)) {
				continue;
			}
			$key = $match[1];
			if(preg_match_all('/<option ([^>]+)>/is', $a[2][$k], $match2)){
				for($n = 0; $n < count($match2[1]); $n++) {
					$item_value = '';
					if (preg_match('/value\s*=\s*(?(?=[\'"])[\'"]([^"]+)[\'"]|\b(.+)\b)/siU', $match2[1][$n], $match3)) {
						$item_value = $match3[1];
						$item_value = preg_replace('/[\'"]/', '', $item_value);
					}
					
					if ($n == 0) {
						$res[$key] = $item_value;
					}
					if (preg_match('/[^=\'"]selected/i', $match2[1][$n])) {
						$res[$key] = $item_value;
					}
				}
			}
		}
		/*$re = '<select[^>]+name\s*=\s*(?(?=[\'"])[\'"]([^>]+)[\'"]|\b([^>]+)\b)[^>]*>'
			. '.+value\s*=\s*(?(?=[\'"])[\'"]([^>]+)[\'"]|\b([^>]+)\b)[^>]+\bselected\b'
			. '.+<\/select>';
		preg_match_all("/$re/siU", $form[1], $a);
		
		foreach($a[1] as $num=>$key) {
			$val = $a[3][$num];
			if ($val == '') $val = $a[4][$num];
			if ($key == '') $key = $a[2][$num];
			$res[$key] = html_entity_decode($val);
		} */

		/* get all <input...> */
		preg_match_all('/<input([^>]+)\/?>/siU', $form[1], $a);

		/* convert to associative array */
		foreach($a[1] as $b) { 
			preg_match_all('/([a-z]+)\s*=\s*(?(?=[\'"])[\'"]([^"]+)[\'"]|\b(.+)\b)/siU', trim($b), $c);
			
			$element = array();
			
			foreach($c[1] as $num=>$key) {
				$val = $c[2][$num];
				if ($val == '') $val = $c[3][$num];
				$element[$key] = $val;
			}
			
			$type = strtolower($element['type']);
			
			/* only radio or checkbox with default values */
			if ($type == 'radio' || $type == 'checkbox') 
				if (!preg_match('/\s+\bchecked\b/', $b)) continue;
				
			/* remove buttons and file */	
			if ($type == 'file' || $type == 'submit' || $type == 'reset' || $type == 'button') 
				continue;
			
			/* remove unnamed elements */
			if (!isset($element['name']) && !isset($element['id'])) 
				continue;
			if ($element['name'] == '' && $element['id'] == '') 
				continue;
			
			/* cool */
			$key = $element['name'] == '' ? $element['id'] : $element['name'];
			$res[$key] = isset($element['value']) ? html_entity_decode($element['value']) : null;
		}
		
		/* get all <teaxarea...> */
		preg_match_all('/<textarea ([^>]+)\/?>([^<]*)<\/textarea>/siU', $form[1], $a);
		
		for ($k = 0; $k < count($a[1]); $k++){
			if (!preg_match('/name\s*=\s*(?(?=[\'"])[\'"]([^"]+)[\'"]|\b(.+)\b)/siU', $a[1][$k], $match)) {
				continue;
			}
			$key = $match[1];
			$res[$key] = $a[2][$k];
		}
		
		return $res;
	} 

	/**
	 * Get mime type for a file
	 */
	protected function getMimeType($filename)
	{ 
		/* list of mime type. add more rows to suit your need */
		$mimetypes = array(
			'jpg'  => 'image/jpeg',
			'jpe'  => 'image/jpeg',
			'jpeg' => 'image/jpeg',
			'gif'  => 'image/gif',
			'png'  => 'image/png',
			'tiff' => 'image/tiff',
			'html' => 'text/html',
			'txt'  => 'text/plain',
			'pdf'  => 'application/pdf',
			'zip'  => 'application/zip'
		);

		/* get file extension */
		preg_match('#\.([^\.]+)$#', strtolower($filename), $e);

		/* get mime type */
		foreach($mimetypes as $ext=>$mime)
			if ($e[1] == $ext) return $mime;

		/* this is the default mime type */
		return 'application/octet-stream';
	} 

	/**
	 * Log HTTP request/response
	 */
	protected function logHttpStream($url, $head, $body)
	{ 
		/* open log file */
		if (($fp = @fopen("$this->_debugdir/headers.txt", 'a+')) == false) return;

		/* get method */
		$m = substr($head, 0, 4);

		/* append the requested URL for HEAD, GET and POST */
		if ($m == 'HEAD' || $m == 'GET ' || $m == 'POST')
			$head = str_repeat('-', 90) . "\r\n$url\r\n\r\n" . trim($head);

		/* header */
		@fputs($fp, trim($head)."\r\n\r\n");

		/* request body */
		if ($m == 'POST' &&  strpos($head, 'Content-Length: ') !== false) {
			/* skip binary contents */
			$find = 'Content-Type: \s*([^\s]+)\r\n\r\n(.+)\r\n';
			$repl = "Content-Type: $1\r\n\r\n <... File contents ...>\r\n";
			$body = preg_replace('/'.$find .'/siU', $repl, $body);

			@fputs($fp, "$body\r\n\r\n");
		} 

		/* response body */
		if (substr($head, 0, 7) == 'HTTP/1.' && strpos($head, 'text/html') !== false && !empty($body)) {
			$tmp = "$this->_debugdir/" . $this->_debugnum++ . '.html';
			@file_put_contents($tmp, $body);
			@fputs($fp, "<... See page contents in $tmp ...>\r\n\r\n");
		}

		@fclose($fp);
	} 

	public function setDebug($bool)
	{
		$this->_log = $bool;

		if (!$this->_log) return;

		/* create directory */
		if (!is_dir($this->_debugdir)) { 
			mkdir($this->_debugdir);
			/* minhna hacked to change mode directory to 744 instead of 644 */
			chmod($this->_debugdir, 0744);
		}

		/* empty debug directory */
		$items = scandir($this->_debugdir);
		foreach($items as $item) { 
			if ($item == '.' || $item == '..') continue;
			@unlink("$this->_debugdir/$item");
		}
	} 

	/**
	 * Set proxy
	 */
	public function setProxy($host, $port, $user = '', $pass = '')
	{
		$this->_proxy_host = $host;
		$this->_proxy_port = $port;
		$this->_proxy_user = $user;
		$this->_proxy_pass = $pass;
		$this->_useproxy   = true;
	} 

	/**
	 * Set delay between requests
	 */
	public function setInterval($sec)
	{ 
		if (!preg_match('/^\d+$/', $sec) || $sec <= 0) {
			$this->_delay = 1;
		} else { 
			$this->_delay = $sec;
		}
	} 

	/**
	 * Assign a name for this HTTP client
	 */
	public function setUserAgent($ua)
	{
		$this->_user_agent = $ua;
	}
	
	/**
	 * minhna hacked: added this function to return errors
	 *
	 * @return array of error strings
	 */
	public function getErrors(){
		if (!$this->_errors) {
			return false;
		}
		
		return $this->_errors;
	}
	
	/**
	 * minhna hacked: add this function
	 * Get post url from site url and form action
	 *
	 * @param string $siteURL
	 * @param string $form_action
	 * @return string
	 */
	public function getPostUrl($siteURL, $form_action){
		$parts = parse_url($siteURL);
		
		if (preg_match('/^http/', $form_action)) {
			return $form_action;
		}
		
		if (preg_match('/^\//', $form_action)) {
			$postURL = $parts['scheme'].'://'.$parts['host']
				.(isset($parts['port']) ? ':'.$parts['port'] : '')
				.$form_action;
			return $postURL;
		}
		
		//remove string after final / from path
		$parts['path'] = preg_replace('/[^\/]+$/', '', $parts['path']);
		$postURL = $parts['scheme'].'://'.$parts['host']
			.(isset($parts['port']) ? ':'.$parts['port'] : '')
			.$parts['path']
			.$form_action;
					
		return $postURL;
	}
	
	/**
	 * minhna hacked to get final url;
	 *
	 * @return string url
	 */
	public function get_addressbar(){
		return $this->_addressbar;
	}
	
	/**
	 * minhna hacked to get header info;
	 *
	 * @return array
	 */
	public function get_head(){
		return $this->_head;
	}
	
	/**
	 * minhna hacked to get page charset info
	 *
	 * @return string
	 */
	public function get_charset(){
		return $this->_charset;
	}
	
	/**
	 * minhna hacked to add function read gzip data
	 *
	 * @param string $data
	 * @return string after unzip
	 */
	private function _read_gzip_data($data){
		if(function_exists("gzdecode")) {
			return gzdecode($data);
		}
		else {
			$len = strlen($data);
			if ($len < 18 || strcmp(substr($data,0,2),"\x1f\x8b")) {
				return null;  // Not GZIP format (See RFC 1952)
			}
			$method = ord(substr($data,2,1));  // Compression method
			$flags  = ord(substr($data,3,1));  // Flags
			if ($flags & 31 != $flags) {
				// Reserved bits are set -- NOT ALLOWED by RFC 1952
				return null;
			}
			// NOTE: $mtime may be negative (PHP integer limitations)
			$mtime = unpack("V", substr($data,4,4));
			$mtime = $mtime[1];
			$xfl   = substr($data,8,1);
			$os    = substr($data,8,1);
			$headerlen = 10;
			$extralen  = 0;
			$extra     = "";
			if ($flags & 4) {
				// 2-byte length prefixed EXTRA data in header
				if ($len - $headerlen - 2 < 8) {
					return false;    // Invalid format
				}
				$extralen = unpack("v",substr($data,8,2));
				$extralen = $extralen[1];
				if ($len - $headerlen - 2 - $extralen < 8) {
					return false;    // Invalid format
				}
				$extra = substr($data,10,$extralen);
				$headerlen += 2 + $extralen;
			}
			
			$filenamelen = 0;
			$filename = "";
			if ($flags & 8) {
				// C-style string file NAME data in header
				if ($len - $headerlen - 1 < 8) {
					return false;    // Invalid format
				}
				$filenamelen = strpos(substr($data,8+$extralen),chr(0));
				if ($filenamelen === false || $len - $headerlen - $filenamelen - 1 < 8) {
					return false;    // Invalid format
				}
				$filename = substr($data,$headerlen,$filenamelen);
				$headerlen += $filenamelen + 1;
			}
			
			$commentlen = 0;
			$comment = "";
			if ($flags & 16) {
				// C-style string COMMENT data in header
				if ($len - $headerlen - 1 < 8) {
					return false;    // Invalid format
				}
				$commentlen = strpos(substr($data,8+$extralen+$filenamelen),chr(0));
				if ($commentlen === false || $len - $headerlen - $commentlen - 1 < 8) {
					return false;    // Invalid header format
				}
				$comment = substr($data,$headerlen,$commentlen);
				$headerlen += $commentlen + 1;
			}
			
			$headercrc = "";
			if ($flags & 1) {
				// 2-bytes (lowest order) of CRC32 on header present
				if ($len - $headerlen - 2 < 8) {
					return false;    // Invalid format
				}
				$calccrc = crc32(substr($data,0,$headerlen)) & 0xffff;
				$headercrc = unpack("v", substr($data,$headerlen,2));
				$headercrc = $headercrc[1];
				if ($headercrc != $calccrc) {
					return false;    // Bad header CRC
				}
				$headerlen += 2;
			}
			
			// GZIP FOOTER - These be negative due to PHP's limitations
			$datacrc = unpack("V",substr($data,-8,4));
			$datacrc = $datacrc[1];
			$isize = unpack("V",substr($data,-4));
			$isize = $isize[1];
			
			// Perform the decompression:
			$bodylen = $len-$headerlen-8;
			if ($bodylen < 1) {
				// This should never happen - IMPLEMENTATION BUG!
				return null;
			}
			$body = substr($data,$headerlen,$bodylen);
			$data = "";
			if ($bodylen > 0) {
				switch ($method) {
				case 8:
					// Currently the only supported compression method:
					$data = gzinflate($body);
					break;
				default:
					// Unknown compression method
					return false;
				}
			} else {
				// I'm not sure if zero-byte body content is allowed.
				// Allow it for now...  Do nothing...
			}
			
			// Verifiy decompressed size and CRC32:
			// NOTE: This may fail with large data sizes depending on how
			//       PHP's integer limitations affect strlen() since $isize
			//       may be negative for large sizes.
			if ($isize != strlen($data) || crc32($data) != $datacrc) {
				// Bad format!  Length or CRC doesn't match!
				return false;
			}
			
			return $data;
		}
	}
	//ducdm87 hacked to set param. example: _referer
	function setParam($array_param)
	{
		if (!is_array($array_param)) {
			return false;
		}
		foreach ($array_param as $k => $param)
		{
			$this->$k	=	$param;
		}
	}
}

