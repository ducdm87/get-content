<?php

/******************
 * @author : Hồng Mai [at] vietbao.vn
 * @copyright 02/2011
 ******************/

class QRequest {
	public $connect;
	
	function QRequest() {
		if (! function_exists ( "curl_init" )) {
			echo "Error: PHP CURL library not loaded, script exit.<br/>";
			exit ();
		}
		$this->connect = curl_init ();
		curl_setopt ( $this->connect, CURLOPT_USERAGENT, $_SERVER ['HTTP_USER_AGENT'] );
		curl_setopt ( $this->connect, CURLOPT_COOKIEJAR, 'cookie.txt' );
		curl_setopt ( $this->connect, CURLOPT_COOKIEFILE, 'cookie.txt' );
		curl_setopt ( $this->connect, CURLOPT_RETURNTRANSFER, true );
	}
	/* Thiết đặt thuộc tính cho CURL 
	+ $option : Thuộc tính	- Ví dụ : CURLOPT_RETURNTRANSFER
	+ $value : Giá trị 		- Ví dụ : true */
	function setOption($option, $value) {
		curl_setopt ( $this->connect, $option, $value );
	}
	/* Thiết đặt mảng thuộc tính cho CURL 
	+ $options : Mảng thuộc tính - Ví dụ : $options = array('Stock'=>1,'Board'=>2);
	************************************/
	function setOptions($options) {
		if (is_array ( $options )) {
			curl_setopt_array ( $this->connect, $options );
		} else {
			echo "Error: setOptions($options) -> $options is not array.<br/>";
		}
	}
	function makeRequest($method, $url, $vars) {
		// if the $vars are in an array then turn them into a usable string
		if (is_array ( $vars )) :
			$vars = implode ( '&', $vars );
		
        endif;
		
		// setup the url to post / get from / to
		curl_setopt ( $this->connect, CURLOPT_URL, $url );
		// the actual post bit
		if (strtolower ( $method ) == 'post') :
			curl_setopt ( $this->connect, CURLOPT_POST, true );
			curl_setopt ( $this->connect, CURLOPT_POSTFIELDS, $vars );
		
        endif;
		// return data
		return curl_exec ( $this->connect );
	}
	function getError() {
		return curl_error ( $this->connect );
	}
	function getConntect() {
		return $this->connect;
	}
	function close() {
		@curl_close ( $this->connect );
	}
	function __destruct() {
		@curl_close ( $this->connect );
	}
}

/*<TESTING>*/
if (isset ( $_GET ['go'] )) {
	$request = new QRequest ( );
	$request->setOption ( CURLOPT_HTTPHEADER, array ("Content-Type: application/json; charset=utf-8" ) );
	$request->setOption ( CURLOPT_REFERER, 'http://directboard6.vndirect.com.vn/HoseStockBoard.aspx' );
	print '<pre>';
	print_r ( $request->makeRequest ( 'post', 'http://directboard6.vndirect.com.vn/HoseStockQuotes.asmx/GetStockBoardBasicObject', '{"clientId":-1,"vId":0,"language":"vi-VN"}' ) );
	print '</pre>';
}
/*</TESTING>*/

?>