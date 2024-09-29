<?php

	class Application{
		protected $isModular = false;
		protected $module;
		protected $controller;
		protected $action;
		protected $_req;
		protected $config;
		
		
		public static function getConfig(){
			if(Zend_Registry::isRegistered('config'))
				return Zend_Registry::get('config');
			
			$config = new Zend_Config_Ini(APPLICATION_PATH . "/application.ini", APPLICATION_ENV);
			Zend_Registry::set('config', $config);
			return $config;
		}
		
		
		protected function setupConfig(){
			$config = new Zend_Config_Ini(APPLICATION_PATH . "/application.ini", APPLICATION_ENV);
			$this->config = $config;
			Zend_Registry::set('config', $config);
		}
		
		protected function setupRequest(){
			Gecko_Request::registerFilter( new Gecko_Request_Filter_MagicQuotes() );
			Gecko_Request::registerFilter( new Gecko_Request_Filter_RemoveXSS() );
			$this->_req = new Gecko_Request();
			Zend_Registry::set("_req", $this->_req);
		}
		
		protected function setupControllerActionModule(){
			$this->controller = (isset($this->_req['_controller']) && $this->_req['_controller'] != "")
				? $this->_req['_controller'] : $this->config->application->defaults->controller;
			$this->action = (isset($this->_req['_action']) && $this->_req['_action'] != "")
				? $this->_req['_action'] : $this->config->application->defaults->action;
				
			if($this->isModular){
				$this->module = (isset($this->_req['_module']) && $this->_req['_module'] != "")
					? $this->_req['_module'] : $this->config->application->defaults->module;
			}
			
			
			
		}
		
		protected function init(){
			$this->setupConfig();
			$this->setupRequest();
			$this->setupControllerActionModule();
			date_default_timezone_set($this->config->application->timezone);
		}
		
		
		protected function run(){
			$this->launchController();
		}
		
		
		protected function launchController(){
			if( !file_exists( APPLICATION_PATH . "/" . $this->getControllerPath() ) ){
				$this->controller = "error";
				$this->action = "index";
			}
			
			
			require($this->getControllerPath());
			$controllerClass = ucfirst( $this->controller ) . "Controller";
			$controller = new $controllerClass();
			
			$controller
				->setController($this->controller)
				->setAction($this->action);
				
			if($this->isModular){
				$controller->setModule($this->module);
			}
			
			$actionToCall = $this->action . "Action";
			ob_start();
			$controller->$actionToCall();
			$output = ob_get_clean();
			$controller->processNext($output);

		}
		
		protected function getControllerPath(){
			$controllerPath = '';
			
			if($this->isModular){
				$controllerPath = "modules/" . $this->module . "/";
			}
			
			$controllerPath .=  "controllers/" . ucfirst( $this->controller ) . "Controller.php";
			return $controllerPath;
		}
	}
	