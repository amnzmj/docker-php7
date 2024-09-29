<?php

	class PasswordController extends Controller{
		
		public function indexAction(){
			
		}
		
		public function submitAction(){
			
			$db = DB::getInstance();
			$auth = Zend_Auth::getInstance();
			$identity = $auth->getIdentity();
			
			$getPass = $db->fetchRow("SELECT password FROM `tblaspirante` WHERE `idAspirante` = " . $identity['idAspirante'] );
			
			$checkPass = password_verify($this->_req['actual'], $getPass['password']);
			
			if(!$checkPass){
				header("Location: ". __BASEURL__ ."/frontend/password/?incorrect=1");exit;
			}
			
			$passwordHasheado = password_hash($this->_req['nueva'], PASSWORD_BCRYPT);
			$data = array("password" => $passwordHasheado);
			$update = $db->update($data, "tblaspirante", "`idAspirante` = " . $identity['idAspirante']);
			$auth->clearIdentity();
	
		}
	}