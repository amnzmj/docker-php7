<?php
	class View{
	
		protected $params = array();
		protected $rootPath = "./";
		protected $viewPath = "";
		public function __construct($path = '', $followAppPath = true){
			if(	defined('APPLICATION_PATH') && $followAppPath ){
				$this->rootPath = APPLICATION_PATH;
				}
			if($path != "")
				$this->viewPath = $path;
		}
		

		
		public function __get($param){
			if(! $this->exists($param) )
				throw new Exception("Parameter " . $param . " not found in view parameters");
			return $this->params[$param];
			//var_dump($param);
		}
		
		public function __set($param, $value){
			$this->params[$param] = $value;
		}
		
		public function setViewPath($vp){
			$this->viewPath = $vp;
		}
		
		public function exists($param){
			return isset($this->params[$param]);
		}
		
		public function __toString(){
			return $this->render();
		}
		
		
		public function render(){
			ob_start();
			if( !include( $this->rootPath . "/" . $this->viewPath ) ){
				$void = ob_get_clean();
				throw new Exception("View not found in '" . $this->rootPath . "/" . $this->viewPath . "'");
			}
			return ob_get_clean();
		}
		
	}