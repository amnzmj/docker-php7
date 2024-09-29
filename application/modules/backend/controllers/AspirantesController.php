<?php

	class AspirantesController extends Controller{

		public function indexAction(){
            $db = DB::getInstance();
			$identity = Zend_Auth::getInstance()->getIdentity();
			$admpos=$db->fetchRow("SELECT idAdministrador, posgradoAdm FROM `tbladministrador` WHERE `idAdministrador` = " . $identity['idAdministrador']);
            $pos=strtolower(str_replace(' ','_',$admpos['posgradoAdm']));
			$sql = "SELECT `idAspirante`, `clave`, `nombre`, `email`, `fechaNacimiento`, `licenciatura`, `posgrado`, `LGAC` FROM `viewaspirantes`";
			if(( isset($this->_req['nombre']) && $this->_req['nombre'] != "" ) || ( isset($this->_req['email']) && $this->_req['email'] != "" ) ){
				$sql .= " WHERE ";
				
				if( isset($this->_req['nombre']) && $this->_req['nombre'] != "" ){
						if($identity['idAdministrador']==12){
							$sql .= "(`posgrado`='doctorado_en_ciencias_de_los_alimentos' OR `posgrado`='maestría_en_ciencias_y_tecnología_de_los_alimentos' OR `posgrado`='especialidad_en_inocuidad_de_alimentos') AND (`nombre` LIKE '%". $this->_req['nombre'] ."%')";
						}elseif($identity['idAdministrador']==11)
							{
							$sql.="`posgrado`='doctorado_en_ciencias_químico_biológicas'".
							" OR `posgrado`='especialidad_en_inocuidad_de_alimentos'".
							" OR `posgrado`='maestría_en_ciencias_químico_biológicas'".
							" OR `posgrado`='maestría_en_ciencia_y_tecnología_de_los_alimentos'".
							" OR `posgrado`='doctorado_en_ciencias_de_los_alimentos'".
							" OR `posgrado`='especialidad_en_bioquímica_clínica' ".
							" AND `nombre` LIKE '%". $this->_req['nombre'] ."%'";
						}elseif($identity['idAdministrador']==14){
							$sql.="WHERE `posgrado`='maestría_en_ciencia_y_tecnología_ambiental'".
							" OR `posgrado`='maestría_en_ciencias_de_la_energía'".
							" OR `posgrado`='doctorado_en_ciencias_de_la_energía'".
							" OR `posgrado`='doctorado en ciencia y tecnología químico-ambiental'";							
							}
						elseif($identity['idAdministrador']==13){
							$sql .= "`posgrado`='maestría_en_ciencias_de_la_energía' OR `posgrado`='maestría_en_ciencia_y_tecnología_ambiental' OR `posgrado`='doctorado_en_ciencia_de_la_energía'  AND `nombre` LIKE '%". $this->_req['nombre'] ."%'";
						}else{
							$sql .= "posgrado='$pos' AND `nombre` LIKE '%". $this->_req['nombre'] ."%'";
							}
				}
				if( isset($this->_req['email']) && $this->_req['email'] != "" ){
					if(isset($this->_req['nombre']) && $this->_req['nombre'] != "" ){
						$sql .= " AND ";
					}
					if($identity['idAdministrador']==12){
							$sql .= "(`posgrado`='doctorado_en_ciencias_de_los_alimentos' OR `posgrado`='maestría_en_ciencias_y_tecnología_de_los_alimentos' OR `posgrado`='especialidad_en_inocuidad_de_alimentos') AND (`email` LIKE '%". $this->_req['email'] ."%')";
						}elseif($identity['idAdministrador']==11)
							{
							$sql.="`posgrado`='doctorado_en_ciencias_químico_biológicas'".
							" OR `posgrado`='especialidad_en_inocuidad_de_alimentos'".
							" OR `posgrado`='maestría_en_ciencias_químico_biológicas'".
							" OR `posgrado`='maestría_en_ciencia_y_tecnología_de_los_alimentos'".
							" OR `posgrado`='doctorado_en_ciencias_de_los_alimentos'".
							" OR `posgrado`='especialidad_en_bioquímica_clínica' ". 
							"AND `email` LIKE '%". $this->_req['email'] ."%'";
						}elseif($identity['idAdministrador']==14){
							$sql.="WHERE `posgrado`='maestría_en_ciencia_y_tecnología_ambiental'".
							" OR `posgrado`='maestría_en_ciencias_de_la_energía'".
							" OR `posgrado`='doctorado_en_ciencias_de_la_energía'".
							" OR `posgrado`='doctorado en ciencia y tecnología químico-ambiental'";							
						}elseif($identity['idAdministrador']==13){
							$sql .= "`posgrado`='maestría_en_ciencias_de_la_energía' OR `posgrado`='maestría_en_ciencia_y_tecnología_ambiental' OR `posgrado`='doctorado_en_ciencia_de_la_energía'  AND `email` LIKE '%". $this->_req['email'] ."%'";
						}ELSE{
					$sql .= "posgrado='$pos' AND `email` LIKE '%". $this->_req['email'] ."%'";
						}
				}
                        
                        }else{
                            if($identity['idAdministrador']==1){
                               $sql.=""; 
                            } 
							else{
							IF($identity['idAdministrador']==12){
							$sql.="WHERE `posgrado`='doctorado_en_ciencias_de_los_alimentos' OR `posgrado`='maestría_en_ciencias_y_tecnología_de_los_alimentos' OR `posgrado`='especialidad_en_inocuidad_de_alimentos' ";
							}elseif($identity['idAdministrador']==11){
							$sql.="WHERE `posgrado`='doctorado_en_ciencias_químico_biológicas'".
							" OR `posgrado`='especialidad_en_inocuidad_de_alimentos'".
							" OR `posgrado`='maestría_en_ciencias_químico_biológicas'".
							" OR `posgrado`='maestría_en_ciencia_y_tecnología_de_los_alimentos'".
							" OR `posgrado`='doctorado_en_ciencias_de_los_alimentos'".
							" OR `posgrado`='especialidad_en_bioquímica_clínica' ";
							}elseif($identity['idAdministrador']==13){
							$sql.="WHERE `posgrado`='maestría_en_ciencias_de_la_energía' OR `posgrado`='maestría_en_ciencia_y_tecnología_ambiental' OR `posgrado`='doctorado_en_ciencia_de_la_energía' ";	
							}elseif($identity['idAdministrador']==14){
							$sql.="WHERE `posgrado`='maestría_en_ciencia_y_tecnología_ambiental'".
							" OR `posgrado`='maestría_en_ciencias_de_la_energía'".
							" OR `posgrado`='doctorado_en_ciencias_de_la_energía'".
							" OR `posgrado`='doctorado en ciencia y tecnología químico-ambiental'";							
							}
							
							else{
                            $sql.="WHERE `v`.`posgrado`='$pos'";
                        }}
						}
                        
			$sql .= " ORDER BY `nombre` ASC";
			
			
			$paginaActual = (isset($this->_req['page']) && $this->_req['page'] > 1) ? $this->_req['page'] : 1;
			$paginator = new Pagination($sql, 30, $paginaActual);
			$this->view->aspirantes = $paginator->getResults();
			$this->view->paginator = $paginator;
		}
		
		
		
		
		public function detalleAction(){
			$db = DB::getInstance();
			if(!( isset($this->_req['idAspirante']) && is_numeric($this->_req['idAspirante'])  && $db->fetchOne("SELECT 1 FROM tblaspirante WHERE `idAspirante` = " . $this->_req['idAspirante'] ) ) ){
			
				header("Location: " . __BASEURL__ . "/backend/aspirantes/");
			}
			
			$this->view->aspirante = $db->fetchRow("SELECT * FROM `viewaspirantes` WHERE `idAspirante` = '". $this->_req['idAspirante'] ."'");
			$this->view->posgrados = $db->fetchArray("SELECT `posgrado`, `institucion`, CONCAT_WS(' ', DATE_FORMAT(`fechaIngreso`, '%M'), YEAR(`fechaIngreso`)) AS `fechaIngreso`,	CONCAT_WS(' ', DATE_FORMAT(`fechaEgreso`, '%M'), YEAR(`fechaEgreso`)) AS `fechaEgreso` FROM `tblaspiranteposgrado` WHERE `idAspirante` = '". $this->_req['idAspirante'] ."' AND `posgrado` <> ''");
		}
		
		
		
		public function eliminarAction(){
			$db = DB::getInstance();
			if(!( isset($this->_req['idAspirante']) && is_numeric($this->_req['idAspirante'])  && $db->fetchOne("SELECT 1 FROM tblaspirante WHERE `idAspirante` = " . $this->_req['idAspirante'] ) ) ){
				header("Location: " . __BASEURL__ . "/backend/aspirantes/");
			}
			
			$documentos = $db->fetchArray("SELECT `d`.`idDocumento`  FROM `tbldocumento` AS `d` JOIN `tblexpediente` AS `e` USING(`idExpediente`) JOIN `tblaspirante` AS `a` USING(`idAspirante`) WHERE `a`.`idAspirante` = " . $this->_req['idAspirante']);
			
			$path = Expedientes_Application::getConfig()->files->uploadPath;
			foreach($documentos as $d){
				if(file_exists($path . '/' . $d['idDocumento']))
					unlink( $path . '/' . $d['idDocumento']);
			}
			
			$db->delete("tblaspirante", "`idAspirante` = '{$this->_req['idAspirante']}' LIMIT 1");
			header("Location: " . __BASEURL__ . "/backend/aspirantes/?eliminado=1");
		}
		
		
		
	}