<?php

	class DocumentosController extends Controller{

		public function indexAction(){
			$db = DB::getInstance();
			
			if(!isset($this->_req['idExpediente']) || !is_numeric($this->_req['idExpediente']) && $db->fetchOne("SELECT 1 FROM `tblexpediente` WHERE `idExpediente` = " . $this->_req['idExpediente'] )){
				header("Location: ". __BASEURL__ ."/backend/expedientes/"); exit;
			}


			$sql = "SELECT * FROM `viewdocumentos` WHERE `idExpediente` = '". $this->_req['idExpediente'] ."' ORDER BY documento ASC";
			$this->view->nombre = $db->fetchOne("SELECT `nombre` FROM `viewexpedientes` WHERE `idExpediente` = " . $this->_req['idExpediente'] );
				
			$paginaActual = (isset($this->_req['page']) && $this->_req['page'] > 1) ? $this->_req['page'] : 1;
			$paginator = new Pagination($sql, 15, $paginaActual);
			$this->view->documentos = $paginator->getResults();
			$this->view->paginator = $paginator;
		}
		
		
		
		//////////////////////////////////////
		//////////////////////////////////////
		
		public function aprobarAction(){


			if(!isset($this->_req['idDocumento']) || !is_numeric($this->_req['idDocumento'])){
				header("Location: ". __BASEURL__ ."/backend/expedientes/");exit;
			}
			
			$db = DB::getInstance();
			$doc = $db->fetchRow("SELECT `idDocumento`, `idExpediente`, `status` FROM `viewdocumentos` WHERE `idDocumento` = '". $this->_req['idDocumento'] ."'");
			
			if(!$doc){
				header("Location: ". __BASEURL__ ."/backend/expedientes/");exit;
			}
			if($doc['status'] == "SIN_DATOS" || $doc['status'] == "OK"){
				header("Location: ". __BASEURL__ ."/backend/documentos/?idExpediente=" . $doc['idExpediente']);exit;
			}
			
			$cambioStatus = $db->query("INSERT INTO `tblstatusdocumento`(`idDocumento`, `idStatus`, `fecha`) VALUES('" . $doc['idDocumento'] ."', (SELECT `idStatus` FROM `tblstatus` WHERE `status` = 'OK'), NOW())");
			if($cambioStatus){
				$checarCompletado = $db->fetchRow("SELECT 1 FROM `viewexpedientes` WHERE `idExpediente` = ".$doc['idExpediente']." AND `cuentaAprobados` = `cuentaTotalDocumentos`");
				if($checarCompletado){
					$db->query("UPDATE `tblexpediente` SET `idStatus` = (SELECT `idStatus` FROM `tblstatus` WHERE `status` = 'OK'), `fechaAprobado` = NOW() WHERE `idExpediente` = " . $doc['idExpediente']);
					
					$ini = new Zend_Config_Ini(APPLICATION_PATH . "/application.ini", APPLICATION_ENV);
					$aspirante = $db->fetchRow("SELECT UCASE( CONCAT_WS(' ', `a`.`apellidoPaterno`,`a`.`apellidoMaterno`, `a`.`nombre`) ) AS `nombre`, `a`.`email`, formatoFechaHora(NOW()) AS `fecha` FROM `tblaspirante` AS `a`, `tblexpediente` AS `e` WHERE `e`.`idExpediente` = ". $doc['idExpediente'] ." AND `e`.`idAspirante` = `a`.`idAspirante` LIMIT 1");
					$email = array( $aspirante['email'] );
					$template = $ini->email->templatePath . "/expedienteAprobado.phtml";
					$data = array(
						"nombre" => $aspirante['nombre'],
						"fecha" => $aspirante['fecha']
					);
					$notif = new Notifier($email, $data, "Expediente aprobado", $template);
				}
				
				header("Location: ". __BASEURL__ ."/backend/documentos/?idExpediente=" . $doc['idExpediente']);exit;
			}
		
		}
		
		
		
		
		
		////////////////////////////////
		////////////////////////////////
		
		public function rechazarAction(){
		
			if(!isset($this->_req['idDocumento']) || !is_numeric($this->_req['idDocumento'])){
				header("Location: ". __BASEURL__ ."/backend/expedientes/");
			}
	
			$db = DB::getInstance();
			$doc = $db->fetchRow("SELECT `idDocumento`, `idExpediente`, `status`, `documento` FROM `viewdocumentos` WHERE `idDocumento` = '". $this->_req['idDocumento'] ."'");
			if(!$doc){
				header("Location: ". __BASEURL__ ."/backend/expedientes/");exit;
			}
			if($doc['status'] == "SIN_DATOS" || $doc['status'] == "RECHAZADO"){
				header("Location: ". __BASEURL__ ."/backend/documentos/?idExpediente=" . $doc['idExpediente']);exit;
			}
			
			$this->view->doc = $doc;
			
			if(isset($this->_req['comentario']) && $this->_req['comentario'] != ''){
				$cambioStatus = $db->query("INSERT INTO `tblstatusdocumento`(`idDocumento`, `idStatus`, `fecha`, `comentario`) VALUES('" . $doc['idDocumento'] ."', (SELECT `idStatus` FROM `tblstatus` WHERE `status` = 'RECHAZADO'), NOW(), '". $this->_req['comentario'] ."')");
				if($cambioStatus){
					$db->query("UPDATE `tblexpediente` SET `idStatus` = (SELECT `idStatus` FROM `tblstatus` WHERE `status` = 'RECHAZADO'), `fechaAprobado` = NULL WHERE `idExpediente` = " . $doc['idExpediente']);
					
					$ini = Expedientes_Application::getConfig();
					
					$aspirante = $db->fetchRow("SELECT UCASE( CONCAT_WS(' ', `a`.`apellidoPaterno`,`a`.`apellidoMaterno`, `a`.`nombre`) ) AS `nombre`, `a`.`email`, formatoFechaHora(NOW()) AS `fecha` FROM `tblaspirante` AS `a`, `tblexpediente` AS `e` WHERE `e`.`idExpediente` = ". $doc['idExpediente'] ." AND `e`.`idAspirante` = `a`.`idAspirante` LIMIT 1");
					$email = array( $aspirante['email'] );
					
					$template = $ini->email->templatePath . "/expedienteRechazado.phtml";
					$data = array(
						"nombre" => $aspirante['nombre'],
						"fecha" => $aspirante['fecha'],
						"documento" => $doc['documento'],
						"comentario" => $this->_req['comentario']
					);
					$notif = new Notifier($email, $data, "Documento rechazado", $template);
				}
				header("Location: ". __BASEURL__ ."/backend/documentos/?idExpediente=" . $doc['idExpediente']); exit;
			}

		}
		
		
		
		////////////////////////////////
		////////////////////////////////
		
		public function comentarioAction(){
			$db = DB::getInstance();
			$this->disableLayout();
			if(!isset($this->_req['idDocumento']) || !is_numeric($this->_req['idDocumento'])){
				header("Location: ". __BASEURL__ ."/backend/expedientes/");
			}
			
			$query = $db->fetchOne( "SELECT `status` FROM `viewdocumentos` WHERE `idDocumento` = '". $this->_req['idDocumento' ] ."'" );
			if($query == "RECHAZADO"){
				$this->view->query = $db->fetchRow( "SELECT `comentario`, formatoFechaHora(`fecha`) AS `fecha` FROM `tblstatusdocumento` WHERE `idDocumento` = '". $this->_req['idDocumento' ] ."' AND `idStatus` = 5 ORDER BY `tblstatusdocumento`.`fecha` DESC LIMIT 1");
			}
		}
		
		
		////////////////////////////////
		////////////////////////////////
		
		
		public function descargarAction(){
		
			if( !( 
				isset($this->_req['idDocumento']) 
				&& is_numeric( $this->_req['idDocumento'] )
				&& $doc = $this->getDoc($this->_req['idDocumento'])
			)){
				header("Location: ". __BASEURL__ ."/backend/expedientes/"); exit;
			}
			
			$this->disableLayout();
			$this->disableView();
			

			$this->addResponseHeader(array(
				"Content-Type: " . $doc['mime'],
				"Content-Disposition: attachment; filename=\"{$doc['nombre']}\"",
				"Pragma: public",
				"Cache-Control: must-revalidate, post-check=0, pre-check=0",
				"Content-Length: " . filesize( $doc['path'] )
			) );
			
			
			readfile( $doc['path'] );
	
		
		}
		
		
		
		public function previewAction(){
		
			if( !( 
				isset($this->_req['idDocumento']) 
				&& is_numeric( $this->_req['idDocumento'] )
				&& $doc = $this->getDoc($this->_req['idDocumento'])
			)){
				header("Location: ". __BASEURL__ ."/backend/expedientes/"); exit;
			}			
			
			$this->disableLayout();
			$this->disableView();
			

			$this->addResponseHeader(array(
				"Content-Type: " . $doc['mime'],
				"Content-Disposition: inline; filename=\"{$doc['nombre']}\"",
				"Pragma: public",
				"Cache-Control: must-revalidate, post-check=0, pre-check=0",
				"Content-Length: " . filesize( $doc['path'] )
			) );
			
			
			readfile( $doc['path'] );
	
		
		}
		
		
		
		private function getDoc($idDocumento){
		
			$db = DB::getInstance();
			$identity = Zend_Auth::getInstance()->getIdentity();
			
			$documento = $db->fetchRow( "SELECT `d`.`idExpediente`, `d`.`idDocumento`, `d`.`idTipoDocumento`, LCASE(`d`.`documento`) AS `documento`, `e`.`extension`, `e`.`mime`, `d`.`status`, LCASE(`ve`.`nombre`) AS `nombre` FROM `viewdocumentos` AS `d`, `tblextensiones` AS `e`, `tbldocumento` AS `dd`, `viewexpedientes` AS `ve` WHERE `d`.`idDocumento` = '".$idDocumento."' AND `dd`.`idDocumento` = `d`.`idDocumento` AND `e`.`idExtension` = `dd`.`idExtension` AND `d`.`idExpediente` = `ve`.`idExpediente`"  );
			
			if(!$documento){
				return false;
			}
			

			if($documento['status'] == "SIN_DATOS"){
				return false;
			}
			
			$acentos = array(" ", "á","é",'í',"ó","ú", "ñ", "(", ")", ";", ",");
			$reempAcentos =  array("_", "a","e",'i',"o","u", "n", "", "", "", "");
			
			$nombre = $documento['idExpediente'] ."_".  strtolower( str_replace($acentos, $reempAcentos, substr($documento['nombre'], 0, 20)) )  ."_". strtolower( str_replace($acentos, $reempAcentos, substr($documento['documento'], 0, 50)) ) . "." .$documento['extension'];

			$uploadPath = realpath(Expedientes_Application::getConfig()->files->uploadPath);
			
			return array(
				'nombre' => $nombre,
				'mime' => $documento['mime'],
				'path' => $uploadPath . "/" . $documento['idDocumento']
			);

		}

	}
