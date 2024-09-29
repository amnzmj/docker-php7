<?php

	class IndexController extends Controller{

		public function indexAction(){
			
			
			$this->disableView();
			$this->disableLayout();
			
			if(Zend_Auth::getInstance()->hasIdentity()){
				$identity = Zend_Auth::getInstance()->getIdentity();
				if($identity['type'] == 'user'){
					header("Location: " . __BASEURL__ . "/frontend/documentos/"); exit;
				}
			}
			
			Zend_Auth::getInstance()->clearIdentity();
			header("Location: " .  __BASEURL__ . "/frontend/login/"); exit;
			
		}
	}