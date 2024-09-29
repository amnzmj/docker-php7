<?php

	class RegistroController extends Controller{
		
		public function indexAction(){
			$this->layout->jqueryui = true;
		}
		
		
		public function checkEmailAction(){
			$this->disableLayout();
			$this->disableView();
			$this->addResponseHeader('Content-Type: application/json');
			$db = DB::getInstance();
			
			if( $db->fetchOne("SELECT 1 FROM `tblaspirante` WHERE `email` = '". $this->_req['email'] ."'") ){
				echo '"Este correo electrónico ya existe registrado en el sistema, puede intentar recuperar la contraseña en el siguiente link:<br /><a href=\"' . __BASEURL__ . '/frontend/recuperar/\">Recuperar mi contraseña</a>"';
			}
			else{
				echo 'true';
			}

		}
		
		public function getMunicipiosAction(){
			$this->disableLayout();
			$this->disableView();
			$this->addResponseHeader('Content-Type: application/json');
			$db = DB::getInstance();
			
			$query = $db->fetchArray("SELECT idMunicipio, municipio FROM tblmunicipio WHERE idEstado = '".$this->_req['idEstado']."'");
			
			$json = array();
			foreach($query as $municipio){
				$json[] = array($municipio['idMunicipio'], $municipio['municipio']);
			}
			echo json_encode($json);
		}
		
		
		
		public function submitAction(){

			$this->view->nombre = strtoupper( $this->_req['apellidoPaterno']  ." ". $this->_req['apellidoMaterno'] ." ". $this->_req['nombre'] );
			$this->view->email = $this->_req['email'];

			$db = DB::getInstance();
			$query = $db->fetchRow("SELECT 1 FROM `tblaspirante` WHERE `email` = '". $this->_req['email'] ."'");
			if($query){
				header("Location: ". __BASEURL__ ."/frontend/registro/"); exit;
			}
			
			$db->query("START TRANSACTION");
			
			try{

				$data = array(
					"clave" => 0,
					"password" => "",
					"apellidoPaterno" => array("UCASE('". $this->_req['apellidoPaterno'] ."')") ,
					"apellidoMaterno" => array("UCASE('". $this->_req['apellidoMaterno'] ."')") ,
					"nombre" => array("UCASE('". $this->_req['nombre'] ."')"),
					"posgrado" => $this->_req['posgrado'],
					"fechaNacimiento" => $this->_req['fechaNacimiento'],
					"estadoCivil" => $this->_req['estadoCivil'],
					"nacionalidad" => $this->_req['nacionalidad'],
					"telefonoFijo" => $this->_req['telefonoFijo'],
					"email" => $this->_req['email'],
					"direccionCalle" => $this->_req['direccionCalle'],
					"direccionNumero" => $this->_req['direccionNumero'],
					"direccionColonia" => $this->_req['direccionColonia'],
					"direccionEstado" => $this->_req['direccionEstado'],
					"direccionMunicipio" => $this->_req['direccionMunicipio'],
					"direccionCP" => $this->_req['direccionCP'],
					"universidad" => $this->_req['universidad'],
					"licenciatura" => $this->_req['licenciatura'],
					"universidadDireccion" => $this->_req['universidadDireccion'],
					"fechaIngreso" => $this->_req['licenciaturaFechaIngresoAno'] .'-'. $this->_req['licenciaturaFechaIngresoMes'] . '-01',
					"fechaEgreso" => $this->_req['licenciaturaFechaEgresoAno'] .'-'. $this->_req['licenciaturaFechaEgresoMes'] . '-01',
					"promedioGeneral" => $this->_req['promedioGeneral'],
					"tieneTitulo" => $this->_req['tieneTitulo'],
					"LGAC" => $this->_req['LGAC']
				);
				
				if(isset($this->_req['telefonoMovil']) && $this->_req['telefonoMovil'] != "") $data['telefonoMovil'] = $this->_req['telefonoMovil'];
				if($this->_req['estadoNacimiento'] == "otro"){
					$data['estadoNacimiento'] = array("NULL");
					$data['lugarNacimientoExtranjero'] = $this->_req['lugarNacimientoExtranjero'];
				}
				else{
					$data['lugarNacimientoExtranjero'] = array("NULL");
					$data['estadoNacimiento'] = $this->_req['estadoNacimiento'];
				}
				
				$idAspirante = $db->insert($data, "tblaspirante");
				$passwordGenerado = rand(0, 9) . rand(0, 9) . rand(0, 9) . rand(0, 9);
				$passwordHasheado = password_hash($passwordGenerado, PASSWORD_BCRYPT);

				$clave = 
					$db->fetchOne("SELECT `clavePrefijo` FROM `tblconvocatoria` WHERE `status` = 1 LIMIT 1") 
					. str_pad($idAspirante, 3, "0", STR_PAD_LEFT);
				
				$data = array(
					"clave" =>	$clave,
					"password" =>  $passwordHasheado
				);
				$db->update($data, "tblaspirante", "`idAspirante` = $idAspirante");
				
				foreach($this->_req['posgrados'] as $posgrado){
					
					if(!empty($posgrado['posgrado']) && !empty($posgrado['institucion'])){
						$data = array(
							"idAspirante" => $idAspirante,
							"posgrado" => array( "UCASE( ' " . $posgrado['posgrado'] . "' )" ),
							"institucion" =>  array( "UCASE( ' " . $posgrado['institucion'] . "' )" ),
							"fechaIngreso" => $posgrado['fechaIngresoAno'] . '-' . $posgrado['fechaIngresoMes'] . '-01',
							"fechaEgreso" => $posgrado['fechaEgresoAno'] . '-' . $posgrado['fechaEgresoMes'] . '-01'
						);
						$db->insert($data, "tblaspiranteposgrado");
					}
				}
				

				$idExpediente = $db->fetchRow("SELECT nuevoExpediente($idAspirante) AS idExpediente");

				$ini = Expedientes_Application::getConfig();
				$data = array(
					"email" => $this->_req['email'],
					"nombre" => strtoupper( $this->_req['apellidoPaterno']  ." ". $this->_req['apellidoMaterno'] ." ". $this->_req['nombre'] ),
					"clave" => $clave,
					"password" => $passwordGenerado,
				  	"pos" => $this->_req['posgrado']
					);
				
				$template = $ini->email->templatePath . "/nuevoRegistro.phtml";
				$enviarMail = new Notifier(array($this->_req['email']), $data, "Confirmación de nuevo Registro", $template);

				$db->query('COMMIT');
			}
			catch(Exception $e){
				$db->query('ROLLBACK');
				echo $e;
			}


		}
		
		

	}