<?php 

class ConvocatoriaController extends Controller{
	protected $idConvocatoria;
	
	public function init(){
		$db = DB::getInstance();
		$this->idConvocatoria = $db->fetchOne("SELECT `idConvocatoria` FROM `tblconvocatoria` WHERE `status` = 1 LIMIT 1");
	}
	
	public function indexAction(){
		$db = DB::getInstance();
		$this->view->convocatoria = $db->fetchRow("SELECT * FROM `tblconvocatoria` WHERE `idConvocatoria` = " . $this->idConvocatoria);
	}
	
	
	public function editarAction(){
		$db = DB::getInstance();
		$this->layout->tinymce = true;
		$this->view->convocatoria = $db->fetchRow("SELECT * FROM `tblconvocatoria` WHERE `idConvocatoria` = " . $this->idConvocatoria);
	}
	
	public function submiteditarAction(){
		$db = DB::getInstance();
		$data = array(
			"nombre" => $this->_req['nombre'],
			"clavePrefijo" => $this->_req['clavePrefijo'],
			"infoPrincipal" => $this->_req['infoPrincipal'],
			"infoExpediente" => $this->_req['infoExpediente'],
			"infoFinalizado" => $this->_req['infoFinalizado']
		);
		$db->update( $data, "tblconvocatoria", "`idConvocatoria` = " . $this->idConvocatoria );
		header("Location: " . __BASEURL__ . "/backend/convocatoria/");exit;
	}
	
	public function documentosAction(){
		$db = DB::getInstance();
		$this->view->documentos = $db->fetchArray(
			"SELECT `d`.`idTipoDocumento`, `d`.`documento`, `d`.`pesoMax`, GROUP_CONCAT(DISTINCT `f`.`formato` SEPARATOR ', ') AS `formatos` FROM `tbltipodocumento` AS `d` JOIN `tbltipodocumentoformato` USING (`idTipoDocumento`) JOIN `tblformato` AS `f` USING ( `idFormato` ) GROUP BY `d`.`idTipoDocumento` ORDER BY `d`.`documento` ASC"
		);
	}
	
	public function editardocumentoAction(){
		$this->layout->tinymce = true;
		$db = DB::getInstance();
		if( !(
			isset($this->_req['idTipoDocumento']) && is_numeric( $this->_req['idTipoDocumento'] ) && $db->fetchRow('SELECT 1 FROM `tbltipodocumento` WHERE `idTipoDocumento` = ' . $this->_req['idTipoDocumento'])
		)){
			$this->disableView();
			$this->disableLayout();
			return;
		}
		$this->view->doc = $db->fetchRow("SELECT * FROM `tbltipodocumento` WHERE `idTipoDocumento` = " . $this->_req['idTipoDocumento']);
	}
	
	public function submiteditardocumentoAction(){
		$this->disableView();
		$this->disableLayout();
		$db = DB::getInstance();
		
		if( !(
			isset($this->_req['idTipoDocumento']) && is_numeric( $this->_req['idTipoDocumento'] ) && $db->fetchRow('SELECT 1 FROM `tbltipodocumento` WHERE `idTipoDocumento` = ' . $this->_req['idTipoDocumento'])
			&& isset($this->_req['documento']) && $this->_req['documento'] != "" 
			&& isset($this->_req['pesoMax']) && is_numeric( $this->_req['pesoMax'] ) && $this->_req['pesoMax'] > 0
			&& isset($this->_req['formatos']) && is_array($this->_req['formatos']) && count($this->_req['formatos'] ) > 0
			&& isset($this->_req['requisitos'])
		) ){
			return;
		
		}
		
		
		$db->query("START TRANSACTION");
		try{
			$data = array(
				"documento" => $this->_req['documento'],
				"pesoMax" => $this->_req['pesoMax'],
				"info" => ( $this->_req['requisitos'] == "" ) ? null : $this->_req['requisitos']
			);
			$db->update($data, "tbltipodocumento", "`idTipoDocumento` = '{$this->_req['idTipoDocumento']}'");
			$db->delete('tbltipodocumentoformato', "`idTipoDocumento` = '{$this->_req['idTipoDocumento']}'");
			
			foreach($this->_req['formatos'] as $f){
				$db->insert( array(
					"idTipoDocumento" => $this->_req['idTipoDocumento'],
					"idFormato" => $f
				), "tbltipodocumentoformato");
			}
			
			$db->query("COMMIT");
		}
		catch(Exception $e){
			$db->query("ROLLBACK");
		}
		
		header("Location: " . __BASEURL__ . "/backend/convocatoria/documentos/?editar=1"); exit;
		
	}
	
	public function nuevodocumentoAction(){
		$this->layout->tinymce = true;
	
	}
	
	public function submitnuevoAction(){
		$this->disableView();
		$this->disableLayout();
		if( !(
			isset($this->_req['documento']) && $this->_req['documento'] != "" 
			&& isset($this->_req['pesoMax']) && is_numeric( $this->_req['pesoMax'] ) && $this->_req['pesoMax'] > 0
			&& isset($this->_req['formatos']) && is_array($this->_req['formatos']) && count($this->_req['formatos'] ) > 0
			&& isset($this->_req['requisitos']) 
		) ){
			return;
		
		}
		
		$db = DB::getInstance();
		$db->query("START TRANSACTION");
		try{
			$data = array(
				"documento" => $this->_req['documento'],
				"pesoMax" => $this->_req['pesoMax'],
				"info" =>  ( $this->_req['requisitos'] == "" ) ? null : $this->_req['requisitos']
			);
			$idTipoDocumento = $db->insert($data, "tbltipodocumento");
			
			foreach($this->_req['formatos'] as $f){
				$db->insert( array(
					"idTipoDocumento" => $idTipoDocumento,
					"idFormato" => $f
				), "tbltipodocumentoformato");
			}
			
			$db->query("COMMIT");
		}
		catch(Exception $e){
			$db->query("ROLLBACK");
		}
		
		header("Location: " . __BASEURL__ . "/backend/convocatoria/documentos/?nuevo=1"); exit;
		
	
	}
	
	public function eliminardocumentoAction(){
		$this->disableView();
		$this->disableLayout();
		$db = DB::getInstance();
		if( !(
			isset($this->_req['idTipoDocumento']) && is_numeric( $this->_req['idTipoDocumento'] ) && $db->fetchRow('SELECT 1 FROM `tbltipodocumento` WHERE `idTipoDocumento` = ' . $this->_req['idTipoDocumento'])
		)){
			return;
		}
		
		$docs = $db->fetchArray("SELECT `idDocumento` FROM `tbldocumento` WHERE `idTipoDocumento` = {$this->_req['idTipoDocumento']}");
		$ini = Expedientes_Application::getConfig();
		foreach($docs as $d){
			if(file_exists($ini->files->uploadPath . "/" . $d['idDocumento'])){
				unlink($ini->files->uploadPath . "/" . $d['idDocumento']);
			}
		}
		
		$db->delete('tbltipodocumento', "`idTipoDocumento` = {$this->_req['idTipoDocumento']} LIMIT 1" );
		header("Location: " . __BASEURL__ . "/backend/convocatoria/documentos/?eliminar=1"); exit;
		
	
	}
	
	
	
	
	public function limpiardatosAction(){
		if(isset($this->_req['confirmar'])){
			
			$db = DB::getInstance();
			$docs = $db->fetchArray('SELECT `idDocumento` FROM `tbldocumento`');
			$ini = Expedientes_Application::getConfig();
			foreach($docs as $d){
				if(file_exists($ini->files->uploadPath . "/" . $d['idDocumento'])){
					unlink($ini->files->uploadPath . "/" . $d['idDocumento']);
				}
			}
			
			$db->query("DELETE FROM `tblaspirante`");
			header("Location: " . __BASEURL__ . "/backend/convocatoria/?limpiardatos=1"); exit;
		}
		else{
			
		}
	}
}