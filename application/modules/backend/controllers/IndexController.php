<?php

	class IndexController extends Controller{

		public function indexAction(){
			$this->disableView();
			$this->disableLayout();
			
			if(Zend_Auth::getInstance()->hasIdentity()){
				$identity = Zend_Auth::getInstance()->getIdentity();
				if($identity['type'] == 'admin'){
					header("Location: " . __BASEURL__ . "/backend/expedientes/"); exit;
				}
			}
			
			Zend_Auth::getInstance()->clearIdentity();
			header("Location: " .  __BASEURL__ . "/backend/login/"); exit;
			
		}
	}