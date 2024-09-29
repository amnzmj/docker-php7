<?php

	class Controller{
		protected $isModular = false;
		
		protected $layout;
		protected $view;
		protected $renderLayout;
		protected $renderView;
		protected $sendDefaultResponseHeader;
		protected $responseHeaders;
		
		protected $module;
		protected $controller;
		protected $action;
		
		protected $actionViewPath = null;
		protected $layoutViewPath;
		
		protected $_req = null;
		protected $config;
		
		public function __construct(){
			$this->view = new View();
			$this->layout = new View();
			$this->config = Zend_Registry::get("config");
			
			if(Zend_Registry::isRegistered("_req"))
				$this->_req = Zend_Registry::get("_req");
			
			$this->layoutViewPath = $this->config->application->defaults->layout;
			
			$this->renderLayout = true;
			$this->renderView = true;
			$this->sendDefaultResponseHeader = true;
			
			$this->init();
			
		}
		
		protected function init(){ }
		
		
		public function setAction($action){
			$this->action = $action;
			return $this;
		}
		
		public function setController($controller){
			$this->controller = $controller;
			return $this;
		}
		
		public function setModule($module){
			$this->isModular = true;
			$this->module = $module;
		}
		
		protected function setView($view = ""){
			$this->actionViewPath = $view;
			return $this;
		}
		
		protected function setLayout($layout = ""){
			$this->layoutViewPath = $layout;
			return $this;
		}
		
		protected function addResponseHeader($header){
			if( is_array($header) ){
				foreach($header as $h)
					$this->responseHeaders[] = $h;
			}
			else{
				$this->responseHeaders[] = $header;
			}
			
			$this->sendDefaultResponseHeader = false;
			return $this;
		}
		
		protected function disableLayout(){
			$this->renderLayout = false;
			return $this;
		}
		
		protected function disableView(){
			$this->renderView = false;
			return $this;
		}
		
				
		public function processNext($output = ''){
			$viewRender = '';
			$layout = '';
			$pathPrefix = '';
			if($this->isModular) $pathPrefix = "modules/" . $this->module . "/";
			
			if( $this->renderView ){
				$this->view->layout = $this->layout;
				$this->view->controller = $this->controller;
				$this->view->action = $this->action;
				
				if($this->actionViewPath === null)
					$this->actionViewPath = $this->action;
				$this->view->setViewPath($pathPrefix . "views/" . $this->controller . "/" . $this->actionViewPath . ".phtml");
				$viewRender = $this->view->render();
			}
				
			if( $this->renderLayout ){
				$this->layout->controller = $this->controller;
				$this->layout->action = $this->action;
				
				if( $this->renderView ){
					$this->layout->viewRender = $viewRender;
				}
				else{
					$this->layout->viewRender = '';
				}
				
				
				$this->layout->setViewPath($pathPrefix . "layouts/" . $this->layoutViewPath . ".phtml");
				$layout = $this->layout->render();
			}
			else{
				$layout = $viewRender;
			}
			
			if( $this->sendDefaultResponseHeader ){
				if($this->renderLayout)
					header("Content-Type: " . $this->config->application->defaults->ResponseHeader );
			}
			
			else{
				foreach($this->responseHeaders as $rh){
					header($rh);
				}
			}
			
			echo $output;
			echo $layout;
			
		}
		
		
	
	}