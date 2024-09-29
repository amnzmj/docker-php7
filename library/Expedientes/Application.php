<?php

	class Expedientes_Application extends Application{
	
		public function __construct(){
			$this->isModular = true;			
			$this->init();
		}
		
		public function run(){
			
			
			$identity = array("type" => "guest");
			$auth = Zend_Auth::getInstance();
			$acl = Expedientes_Permissions::getAcl();
			
			if($auth->hasIdentity()){
				$identity = $auth->getIdentity();
			}

			if(!$acl->isAllowed($identity['type'], $this->module . ':' . $this->controller, $this->action)){
				$this->controller = "login";
				$this->action = "index";
			}
			
			parent::run();
			
			
		}
	
	}