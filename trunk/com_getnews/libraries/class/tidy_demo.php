<?php
class HTMLCleaner
{
	var $TidyConfig;
	var $Encoding = 'latin1';
	var $Version = '1.0';
	function HTMLCleaner() {	
		// Specify TIDY configuration
		$this->TidyConfig = array(
			   'indent'         				=> true, /*a bit slow*/
			   'output-xhtml'   				=> true, //Outputs the data in XHTML format
			   'word-2000'						=> false, //Removes all proprietary data when an MS Word document has been saved as HTML
			   //'clean'						=> true, /*too slow*/
			   'drop-proprietary-attributes'	=>true, //Removes all attributes that are not part of a web standard
			   'hide-comments' 					=> true, //Strips all comments
			   'preserve-entities' 				=> true,	// preserve the well-formed entitites as found in the input
			   'quote-ampersand' 				=> true,//output unadorned & characters as &amp;.
			   'show-body-only' 				=> true,
			   'wrap'           				=> 200
			   ); //Sets the number of characters allowed before a line is soft-wrapped
	}
	/*-----------------------------------------------------------------------------*/
	function TidyClean() {
		if (! class_exists('tidy')) {
				if (function_exists( 'tidy_parse_string' ) ) {
					//use procedural style for compatibility with PHP 4.3
					tidy_set_encoding($this->Encoding);			
					foreach ($this->TidyConfig as $key => $value) {
					   tidy_setopt($key,$value);
					}

					tidy_parse_string($this->html);
					tidy_clean_repair();
					$this->html = tidy_get_output();
				}
				else {
					print("<b>No tidy support. Please enable it in your php.ini.\r\nOnly basic cleaning is beeing applied\r\n</b>");
				}

		}
		else {
				//PHP 5 only !!!
				$tidy = new tidy;
				$tidy->parseString($this->html, $this->TidyConfig, $this->Encoding);
				$tidy->cleanRepair();
				$this->html = $tidy;
		}
	}
/*-----------------------------------------------------------------------------*/	
	function cleanUp($encoding = 'latin1') {  
		if (! empty($encoding)) {
			$this->Encoding = $encoding;
		}
		//++++
		// Tidy
		if ($this->Options['UseTidy']) {		
				$this->TidyClean();
		}
		return $this->html;
			
	} //end cleanup
/*-----------------------------------------------------------------------------*/
}
