<?php

	class UsuariosController extends Controller{
		
		public function indexAction(){
			$sql = "SELECT `idAdministrador`, `nombre`, `email`, `notificar`, `activo` FROM `tbladministrador` WHERE `idAdministrador` <> 1  ORDER BY `nombre` ASC";
			
			$paginaActual = ( isset($this->_req["page"]) && $this->_req['page'] > 1 ) ? $this->_req['page'] : 1;
			$paginator = new Pagination($sql, 15, $paginaActual);
			$this->view->administradores = $paginator->getResults();
			$this->view->paginator = $paginator;

		}
		
		
		
		public function nuevoAction(){
			$db = DB::getInstance();
			if(count($_POST)){
				$data = array(
					"nombre" => $this->_req['nombre'],
					"email" => $this->_req['email'],
					"posgradoAdm" => $this->_req['posgrado'],
					"password" => password_hash($this->_req['password'], PASSWORD_BCRYPT)
				);
				
				if(! $db->fetchOne("SELECT 1 FROM `tbladministrador` WHERE `email` =  '". $this->_req['email']. "'")){
					$insert = $db->insert($data, "tbladministrador", "`idAdministrador` = " . $this->_req['idAdministrador']);
					header("Location: ". __BASEURL__ ."/backend/usuarios/"); exit;
				}
				else{
					$this->view->emailRepetido = true;
				}
			}

		}
		
		
		public function editarAction(){
			$db = DB::getInstance();
			if(count($_POST)){
				$data = array(
					"nombre" => $this->_req['nombre'],
					"email" => $this->_req['email']
				);
				if(isset($this->_req['password'])!= "") $data['password'] = password_hash($this->_req['password'], PASSWORD_BCRYPT);
				$update = $db->update($data, "tbladministrador", "`idAdministrador` = " . $this->_req['idAdministrador']);
				if($update){
					header("Location: ". __BASEURL__ ."/backend/usuarios/"); exit;
				}
			}
			
			else{
				$this->view->idAdministrador = $this->_req['idAdministrador'];
				$this->view->administrador = $db->fetchRow("SELECT `nombre`, `email` FROM `tbladministrador` WHERE `idAdministrador` = ". $this->_req['idAdministrador'] ) ;
			}
		}
		
		
		public function desactivarAction(){
			if($this->_req['idAdministrador'] == 1) return false;
			$db = DB::getInstance();
			$db->query("UPDATE `tbladministrador` SET `activo` = IF(`activo`,0,1) WHERE `idAdministrador` = ". $this->_req['idAdministrador']);	
			header("Location: ". __BASEURL__ ."/backend/usuarios/"); exit;
		}
		
		public function notificarAction(){
			$db = DB::getInstance();
			$db->query("UPDATE `tbladministrador` SET `notificar` = IF(`notificar`,0,1) WHERE `idAdministrador` = ". $this->_req['idAdministrador']);
			header("Location: ". __BASEURL__ ."/backend/usuarios/"); exit;

		}
		
		public function eliminarAction(){
			$db = DB::getInstance();
			if($this->_req['idAdministrador'] == 1) return false;
			$db->delete("tbladministrador", "`idAdministrador` = " . $this->_req['idAdministrador']);
			header("Location: ". __BASEURL__ ."/backend/usuarios/"); exit;

		}
	}