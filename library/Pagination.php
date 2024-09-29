<?php
require_once("DB.php");


class Pagination{
	private $currentPage;
	private $totalPages;
	private $perPage;
	private $sql;
	
	public function __construct($sql, $perPage = 10, $current = 1){
		$this->sql = $sql;
		$this->perPage = $perPage;
		$this->currentPage = $current - 1;
		$this->totalPages = $this->fetchTotalPages($sql);
	}
	
	private function fetchTotalPages(){
		$db = DB::getInstance();	
		$totalRows = 0;

		$query = $db->fetchArray("SELECT COUNT(*) AS `__count` FROM ( " . $this->sql ." ) AS `count`;");
		if($query){
			foreach($query as $c){
				$totalRows += $c['__count'];
			}
		}
		return ( ceil( $totalRows / $this->perPage ) > 0) ? ceil( $totalRows / $this->perPage ) : 1;
	}
	
	public function getResults(){
		$db = DB::getInstance();
		return $db->fetchArray( $this->sql . " LIMIT " . ( $this->perPage * $this->currentPage ) . ", " . $this->perPage );
	}
	
	
	public function getTotalPages(){
		return $this->totalPages;
	}
	
	public function getPages(){
		$i = 0;
		$pages = array();
		while($i < $this->totalPages){
			$pages[] = $i+1;
			$i++;
		}
		return $pages;
	}
	
	public function getCurrentPage(){
		return $this->currentPage + 1;
	}
	public function hasNextPage(){
		if( ( $this->getCurrentPage() == $this->totalPages ) ) return false;
		return true;
	}
	public function getNextPage(){
		if( ( $this->getCurrentPage() == $this->totalPages ) ) return $this->totalPages;
		return $this->getCurrentPage() + 1;
	}

	public function hasPrevPage(){
		if( ( $this->getCurrentPage() == 1 ) ) return false;
		return true;
	}
	public function getPrevPage(){
		if( ( $this->getCurrentPage() == 1 ) ) return 1;
		return $this->getCurrentPage() - 1;
	}
	
	public function getURI($params = array()){
		$serverURI = $this->getURLParams();
		$uri = "";
		foreach($params as $k=>$v){
			$serverURI[$k] = $v;
		}
		foreach($serverURI as $k=>$v){
			$uri .= $k . "=" . $v . "&";
		}
		$uri = substr($uri, 0, strlen($uri) - 1);
		
		return $this->getURLBase() . "?" . $uri;
	}
	
	public function getURLBase(){
		return (strpos($_SERVER['REQUEST_URI'], "?")) ? substr( $_SERVER['REQUEST_URI'], 0, strpos($_SERVER['REQUEST_URI'], "?") ) : $_SERVER['REQUEST_URI'];
	}
	
	public function getURLParams(){
		$serverURI = array();
		if( strpos($_SERVER['REQUEST_URI'], "?") ){
			$serverURIexp = explode( "&", substr( $_SERVER['REQUEST_URI'], strpos($_SERVER['REQUEST_URI'], "?") + 1 ) ) ;
			foreach( $serverURIexp as $param){
				$param = explode("=", $param);
				$serverURI[$param[0]] = $param[1];
			};
		}
		return $serverURI;
	}
	
	
	
	
	public function getPagination($template = "/views/helpers/pagination.phtml"){
		ob_start();
		//$paginator = $this;
		include(APPLICATION_PATH . $template);
		$output = ob_get_clean();
		return $output;
	}
}
?>