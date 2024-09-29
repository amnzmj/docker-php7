<?php

	class LoginController extends Controller{
		
		public function indexAction(){

		}
		
		public function loginAction(){
			$this->setView('index');
			
			$auth = Zend_Auth::getInstance();
			$auth->clearIdentity();
			
			$authAdapter = new Expedientes_AuthAdapter($this->_req['email'], $this->_req['password'], 'frontend');

			
			// Attempt authentication, saving the result
			$result = $auth->authenticate($authAdapter);
			if ( !$result->isValid() ) {
				$this->view->incorrect = true;				
			} else {
				header("Location: " . __BASEURL__ . "/frontend/index/"); exit;
			}
		}
		
		public function logoutAction(){
			Zend_Auth::getInstance()->clearIdentity();
			header("Location: " . __BASEURL__ . "/frontend/login/"); exit;
		}
		
		
	}