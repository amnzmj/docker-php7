<?php

	class RecuperarController extends Controller{

		public function indexAction(){
			
		}
		
		
		////////////////////////////
		////////////////////////////
		
		public function submitAction(){	
			if(!isset($this->_req['email']) || $this->_req['email'] == ""){
				header("Location: ". __BASEURL__ ."/frontend/recuperar/");
			}
			
			$db = DB::getInstance();
			
			if( !$db->fetchOne("SELECT 1 FROM `tblaspirante` WHERE `email` = '". $this->_req['email'] ."'") ){
				$this->setView('index');
				$this->view->inexistent = true;
				return false;
			}
			
			$aspirante = $db->fetchRow("SELECT `idAspirante`, CONCAT_WS(' ', `apellidoPaterno`, `apellidoMaterno`, `nombre`) AS `nombre`, `email` FROM `tblaspirante` WHERE `email` = '". $this->_req['email'] ."'");
				
			
			$nuevoPass = rand(0, 9) . rand(0, 9) . rand(0, 9) . rand(0, 9);
			$passwordHasheado = password_hash($nuevoPass, PASSWORD_BCRYPT);
			$query = $db->query("UPDATE `tblaspirante` SET `password` = '{$passwordHasheado}' WHERE `idAspirante` = {$aspirante['idAspirante']} LIMIT 1");
			
			$data = array(
				"email" => $aspirante['email'], 
				"nombre" => $aspirante['nombre'],
				"password" => $nuevoPass
			);
			
			$ini = Expedientes_Application::getConfig();	
			$template = $ini->email->templatePath . "/recuperarPassword.phtml";
			$enviarMail = new Notifier(array($aspirante['email']), $data, "Recuperación de contraseña", $template);
			
			$this->view->aspirante = $aspirante;
		}
	}