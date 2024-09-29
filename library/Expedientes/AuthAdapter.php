<?php

	class Expedientes_AuthAdapter implements Zend_Auth_Adapter_Interface{
		protected $username;
		protected $password;
		protected $module;
		/**
		 * Sets username and password for authentication
		 *
		 * @return void
		 */
		public function __construct($username, $password, $module){			
			$this->username = $username;
			$this->password = $password;
			$this->module = $module;
		}
		
	 
		/**
		 * Performs an authentication attempt
		 *
		 * @throws Zend_Auth_Adapter_Exception If authentication cannot be performed
		 * @return Zend_Auth_Result
		 */
		public function authenticate(){
			$identity = array();
	
			
			if($this->module == "backend"){
				$identity = $this->authBackend();
			}
			else{
				$identity = $this->authFrontend();
			}
			
			if($identity){
				return new Zend_Auth_Result( Zend_Auth_Result::SUCCESS, $identity );
			}
			
			else{
				return new Zend_Auth_Result( Zend_Auth_Result::FAILURE , null, 
					array("usuario o contraseña son incorrectos") );
			}
		}
		
		
		protected function authBackend(){
			$db = DB::getInstance();
			
			$identity = $db->fetchRow( "SELECT `idAdministrador`, `nombre`, `email`, `password`, `notificar`, `activo` FROM `tbladministrador` WHERE `email` = '{$this->username}' AND `activo` = 1 LIMIT 1" );
			$validIdentity = password_verify($this->password, $identity['password']);

			if($validIdentity){
				$identity['type'] = 'admin';
				$db->insert(array(
					"idAdministrador" => $identity['idAdministrador'],
					"fecha" => array("NOW()"),
					"ip" => $_SERVER['REMOTE_ADDR']
				), "tblloginadministrador");
				
				return $identity;
			}
			
			return false;
		}

		
		
		
		protected function authFrontend(){
			$db = DB::getInstance();
			
			$identity = $db->fetchRow( "SELECT `idAspirante`, `password`, CONCAT_WS(' ', `nombre`, `apellidoPaterno`, `apellidoMaterno`) AS `nombre`, `email` FROM `tblaspirante` WHERE `email` = '{$this->username}' LIMIT 1" );

			$validIdentity = password_verify($this->password, $identity['password']);

			if($validIdentity){
				$identity['type'] = 'user';
				$db->insert(array(
					"idAspirante" => $identity['idAspirante'],
					"fecha" => array("NOW()"),
					"ip" => $_SERVER['REMOTE_ADDR']
				), "tblloginaspirante");
				
				return $identity;
			}
			
			return false;
		}

	}