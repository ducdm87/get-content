<?php
class cacheHelper {   
    var $cache_file 	= '';   
    var $startId 		= 0;   
    var $lastGet_Id 	= -1;
    function cacheHelper($path_cache){
    	
//        $this->cache_file = dirname(__FILE__).DS.'cache.php';   
        $this->cache_file = $path_cache;
        if (!file_exists($path_cache)) {
        	$this->update_cache_file(-1,0,date('Y-m-d H:i:s'));
        }
    }
    function setStartID($startID = 0)
    {
    	$this->startId	=	$startID;
    }
    function getlastGet_Id() 
    {
    	 require($this->cache_file);
    	  $this->lastGet_Id	=	$lastGet_Id;    	
    	  return $this->lastGet_Id;
    }
    function getStartID() 
    {
    	 require($this->cache_file);
    	  $this->startId	=	$startId;    	
    	  return $this->startId;
    }
    /**
     * Get stock info from cache file, if cache was expired then call the function update stock
     *
     * @param objec $params
     * @return and object which stores stock info
     */
    
    function isGetContent($time_exp = 5)
    {
    	global $mainframe, $mosConfig_offset_user;
        require($this->cache_file);               
        // neu con thoi gian(van dang lay) thi thoi
        $now = date('Y-m-d H:i:s');
       
        if ($now < $cache_exp && $checkedout==1) return false;           
        // neu ko co ai lay hoac dang co ai lay ma da het thoi gian                
        $now = mktime(date("h"), date("i"), date("s")+$time_exp*60, date("m")  , date("d"), date("Y"));
       	$now = date('Y-m-d H:i:s',$now); 
        $cache_exp = $now; 
        
        $this->update_cache_file($lastGet_Id, 1, $cache_exp); 
        $this->lastGet_Id	=	$lastGet_Id;
        return true;
        // sau khi get ve xong. can goi require($this->cache_file); $this->update_cache_file($lastGet_Id, 0, $cache_exp)
    }       
   
    /**
     * Update cache file
     *
     * @param string $y_data
     * @param int $checkout
     * @param string $checkout_time
     * @param string $cache_exp
     * @return true if success, else return false
     */
    function update_cache_file($id, $checkedout, $cache_exp){      
        $str_file_content = "
        <?php
        // no direct access
        defined('_VALID_MOS') or die('Restricted access');
        \$startId = \"$this->startId\";
        \$lastGet_Id = \"$id\";
        \$checkedout = $checkedout;       
        \$cache_exp = '$cache_exp';
        ";      
         
        return file_put_contents($this->cache_file, $str_file_content);       
    }
}